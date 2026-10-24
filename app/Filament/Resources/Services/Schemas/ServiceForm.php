<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Название')
                    ->required(),
                Textarea::make('description')->label('Описание')
                    ->columnSpanFull(),
                TextInput::make('duration_minutes')->label('Длительность, мин')
                    ->required()
                    ->numeric()->minValue(15)->step(15),
                TextInput::make('price')
                    ->required()
                    ->numeric()->label('Цена')
                    ->suffix('руб.'),
                Toggle::make('is_active')->label('Показывать на сайте')->default(true),
            ]);
    }
}
