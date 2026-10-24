<?php

namespace App\Filament\Resources\Masters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MasterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Имя')
                    ->required(),
                TextInput::make('specialty')->label('Специализация')
                    ->required(),
                \Filament\Forms\Components\CheckboxList::make('services')->label('Услуги')
                    ->relationship('services', 'name')->columns(2)->columnSpanFull(),
                TimePicker::make('work_from')->label('Начало дня')->seconds(false)
                    ->required(),
                TimePicker::make('work_to')->label('Конец дня')->seconds(false)
                    ->required()->after('work_from'),
                Toggle::make('is_active')->label('Принимает записи')->default(true),
            ]);
    }
}
