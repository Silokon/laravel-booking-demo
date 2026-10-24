<?php

namespace App\Filament\Resources\Masters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MastersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Мастер')
                    ->searchable(),
                TextColumn::make('specialty')->label('Специализация')
                    ->searchable(),
                TextColumn::make('services.name')->label('Услуги')->badge(),
                TextColumn::make('work_from')->label('С')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('work_to')->label('До')
                    ->time('H:i')
                    ->sortable(),
                IconColumn::make('is_active')->label('Активен')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
