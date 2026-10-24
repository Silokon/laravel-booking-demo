<?php

namespace App\Actions;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Service;
use App\Services\SlotService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateAppointment
{
    public function __construct(private SlotService $slots) {}

    public function handle(array $data): Appointment
    {
        $master = Master::where('is_active', true)->findOrFail($data['master_id']);
        $service = Service::where('is_active', true)->findOrFail($data['service_id']);

        if (! $master->services()->whereKey($service->id)->exists()) {
            throw ValidationException::withMessages(['master_id' => 'Этот мастер не оказывает выбранную услугу.']);
        }

        $start = CarbonImmutable::createFromFormat('Y-m-d H:i', $data['starts_at']);

        // Повторная проверка внутри транзакции: два клиента не займут одно окно.
        return DB::transaction(function () use ($master, $service, $start, $data) {
            if (! $this->slots->isFree($master, $service, $start)) {
                throw ValidationException::withMessages(['starts_at' => 'Это время только что заняли, выберите другое.']);
            }

            return Appointment::create([
                'master_id' => $master->id,
                'service_id' => $service->id,
                'client_name' => $data['client_name'],
                'client_phone' => $data['client_phone'],
                'comment' => $data['comment'] ?? null,
                'starts_at' => $start,
                'ends_at' => $start->addMinutes($service->duration_minutes),
                'status' => 'new',
            ]);
        });
    }
}
