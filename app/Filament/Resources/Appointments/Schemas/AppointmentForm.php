<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        // Время окончания считается из начала и длительности услуги, вручную его не вводят.
        $recalcEnd = function (Get $get, Set $set): void {
            $service = Service::find($get('service_id'));
            $start = $get('starts_at');
            $set('ends_at', $service && $start
                ? Carbon::parse($start)->addMinutes($service->duration_minutes)->format('Y-m-d H:i:s')
                : null);
        };

        return $schema
            ->components([
                Select::make('service_id')->label('Услуга')
                    ->relationship('service', 'name')
                    ->required()->live()->afterStateUpdated($recalcEnd),
                Select::make('master_id')->label('Мастер')
                    ->relationship('master', 'name')
                    ->required(),
                TextInput::make('client_name')->label('Клиент')
                    ->required()->maxLength(80),
                TextInput::make('client_phone')->label('Телефон')
                    ->tel()->required(),
                DateTimePicker::make('starts_at')->label('Начало')
                    ->seconds(false)->minutesStep(30)
                    ->required()->live()->afterStateUpdated($recalcEnd),
                DateTimePicker::make('ends_at')->label('Окончание')
                    ->seconds(false)->disabled()->dehydrated()
                    ->helperText('Считается по длительности услуги'),
                Select::make('status')->label('Статус')
                    ->options(Appointment::STATUSES)
                    ->default('new')->required(),
                Textarea::make('comment')->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
