<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                TextColumn::make('starts_at')->label('Когда')
                    ->dateTime('d.m H:i')->sortable(),
                TextColumn::make('client_name')->label('Клиент')
                    ->description(fn (Appointment $r) => $r->client_phone)
                    ->searchable(['client_name', 'client_phone']),
                TextColumn::make('service.name')->label('Услуга'),
                TextColumn::make('master.name')->label('Мастер'),
                TextColumn::make('status')->label('Статус')->badge()
                    ->formatStateUsing(fn (string $state) => Appointment::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'confirmed' => 'info',
                        'done' => 'success',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Создана')
                    ->dateTime('d.m.Y H:i')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(Appointment::STATUSES),
                SelectFilter::make('master_id')->label('Мастер')->relationship('master', 'name'),
                Filter::make('date')->label('Дата')
                    ->schema([DatePicker::make('day')->label('Дата')])
                    ->query(fn (Builder $q, array $data) => $q->when($data['day'] ?? null, fn ($q, $day) => $q->whereDate('starts_at', $day))),
            ])
            ->recordActions([
                Action::make('confirm')->label('Подтвердить')->icon('heroicon-o-check')
                    ->visible(fn (Appointment $r) => $r->status === 'new')
                    ->action(fn (Appointment $r) => $r->update(['status' => 'confirmed'])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
