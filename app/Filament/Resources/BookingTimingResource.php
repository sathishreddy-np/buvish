<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingTimingResource\Pages;
use App\Filament\Resources\BookingTimingResource\RelationManagers;
use App\Models\BookingTiming;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingTimingResource extends Resource
{
    protected static ?string $model = BookingTiming::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('activity_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('day')
                    ->required()
                    ->maxLength(55),
                Forms\Components\TextInput::make('start_time')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('end_time')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('no_of_slots')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('allowed_categories')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_of_slots')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
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
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookingTimings::route('/'),
            'create' => Pages\CreateBookingTiming::route('/create'),
            'view' => Pages\ViewBookingTiming::route('/{record}'),
            'edit' => Pages\EditBookingTiming::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
