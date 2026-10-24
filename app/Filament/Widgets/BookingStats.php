<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = Appointment::blocking()->whereDate('starts_at', today())->count();
        $new = Appointment::where('status', 'new')->count();
        $revenue = Appointment::query()
            ->where('status', 'done')
            ->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->join('services', 'services.id', '=', 'appointments.service_id')
            ->sum('services.price');

        return [
            Stat::make('Записей сегодня', $today),
            Stat::make('Ждут подтверждения', $new)->color($new > 0 ? 'warning' : 'success'),
            Stat::make('Выручка за месяц', number_format($revenue, 0, ',', ' ').' руб.')
                ->description('по выполненным записям'),
        ];
    }
}
