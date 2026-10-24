<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Считает свободные окна мастера на дату под длительность услуги.
 * Шаг сетки 30 минут, прошедшее время сегодня не предлагается.
 */
class SlotService
{
    public const STEP_MINUTES = 30;

    /** @return Collection<int, CarbonImmutable> */
    public function freeSlots(Master $master, Service $service, CarbonImmutable $date): Collection
    {
        $dayStart = $date->setTimeFromTimeString($master->work_from);
        $dayEnd = $date->setTimeFromTimeString($master->work_to);
        $now = CarbonImmutable::now();

        $busy = Appointment::query()
            ->blocking()
            ->where('master_id', $master->id)
            ->where('starts_at', '<', $dayEnd)
            ->where('ends_at', '>', $dayStart)
            ->get(['starts_at', 'ends_at']);

        $slots = collect();
        for ($start = $dayStart; $start->addMinutes($service->duration_minutes) <= $dayEnd; $start = $start->addMinutes(self::STEP_MINUTES)) {
            $end = $start->addMinutes($service->duration_minutes);
            if ($start <= $now) {
                continue;
            }
            $overlaps = $busy->contains(fn ($a) => $a->starts_at < $end && $a->ends_at > $start);
            if (! $overlaps) {
                $slots->push($start);
            }
        }

        return $slots;
    }

    public function isFree(Master $master, Service $service, CarbonImmutable $start): bool
    {
        return $this->freeSlots($master, $service, $start->startOfDay())
            ->contains(fn (CarbonImmutable $slot) => $slot->equalTo($start));
    }
}
