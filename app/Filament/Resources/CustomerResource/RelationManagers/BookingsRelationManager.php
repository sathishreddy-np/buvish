<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Activity;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\NotificationType;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::where('company_id', auth()->user()->company_id)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('activity_id')
                    ->label('Activity')
                    ->options(Activity::where('company_id', auth()->user()->company_id)->pluck('name', 'id')->map(function ($name) {
                        return ucfirst($name);
                    }))
                    ->required()
                    ->searchable(),
                DateTimePicker::make('booking_starts_at')
                    ->seconds(false),
                DateTimePicker::make('booking_ends_at')
                    ->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
