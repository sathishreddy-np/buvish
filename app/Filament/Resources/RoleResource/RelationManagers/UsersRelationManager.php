<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('email')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('roles.name')
                ->badge()
                ->sortable()
                ->searchable()
                ->getStateUsing(function (Model $record) {
                    $roles = $record->roles->toArray();
                    $role_names = array_map(function ($role) {
                        return $role['name'];
                    }, $roles);
                    return $role_names;
                }),
            Tables\Columns\IconColumn::make('is_verified')
                ->sortable()
                ->icon(fn (string $state): string => match ($state) {
                    '0' => 'heroicon-o-x-circle',
                    '1' => 'heroicon-o-check-badge',
                    default => 'heroicon-o-check-circle',
                })
                ->color(fn (string $state): string => match ($state) {
                    '0' => 'danger',
                    '1' => 'success',
                    default => 'gray',
                }),
            Tables\Columns\ToggleColumn::make('is_active')
                ->sortable(),
            Tables\Columns\TextColumn::make('email_verified_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('deleted_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),


            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                TernaryFilter::make('is_verified')
                    ->label('User verified')
                    ->placeholder('Select status')
                    ->nullable(),
                TernaryFilter::make('is_active')
                    ->label('Is active')
                    ->placeholder('Select status')
                    ->nullable(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                Tables\Actions\AttachAction::make()
                ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('company_id',auth()->user()->company_id))
                ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
