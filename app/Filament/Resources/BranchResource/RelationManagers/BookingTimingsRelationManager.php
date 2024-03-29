<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use App\Models\Activity;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Components\Builder as ComponentsBuilder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BookingTimingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookingTimings';

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
                ComponentsBuilder::make('timings')
                    ->blocks([
                        Block::make('Opening Timings')
                            ->schema([
                                Forms\Components\Select::make('day')
                                    ->options([
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday',
                                    ])
                                    ->required()
                                    ->multiple()
                                    ->searchable(),
                                Forms\Components\TimePicker::make('start_time')
                                    ->required(),
                                Forms\Components\TimePicker::make('end_time')
                                    ->required(),
                                Forms\Components\TextInput::make('no_of_slots')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                Repeater::make('allowed_genders')
                                    ->schema([
                                        Forms\Components\Select::make('gender')
                                            ->options([
                                                'male' => 'Male',
                                                'female' => 'Female',
                                                'kid' => 'Kid',
                                            ])
                                            ->required()
                                            ->searchable(),
                                        Forms\Components\TextInput::make('age_from')
                                            ->required()
                                            ->numeric(),
                                        Forms\Components\TextInput::make('age_to')
                                            ->required()
                                            ->numeric(),
                                        Forms\Components\TextInput::make('amount')
                                            ->required()
                                            ->numeric(),

                                    ])
                                    ->deletable(true)
                                    ->addable(true)
                                    ->cloneable()
                                    ->columnSpanFull()
                                    ->columns(4)
                                    ->collapsible(),
                            ]),
                        Block::make('Closing Timings')
                            ->schema([
                                Forms\Components\Select::make('day')
                                    ->options([
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday',
                                    ])
                                    ->required()
                                    ->multiple()
                                    ->searchable(),
                                Forms\Components\TimePicker::make('start_time')
                                    ->required(),
                                Forms\Components\TimePicker::make('end_time')
                                    ->required(),
                            ]),
                    ])->columnSpanFull()
                    ->collapsible()
                    ->cloneable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity.name')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
