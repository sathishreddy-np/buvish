<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\NotificationType;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder as ComponentsBuilder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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
                Forms\Components\TextInput::make('phone')
                    ->prefix('+91')
                    ->tel()
                    ->telRegex('/^[6789]\d{9}$/')
                    ->required(),
                DatePicker::make('booking_date')
                    ->required(),

                ComponentsBuilder::make('members')
                    ->blocks([
                        Block::make('male')
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->options([
                                        'male' => 'Male',
                                        'female' => 'Female',
                                        'kid' => 'Kid',
                                    ])
                                    ->default('male')
                                    ->disabled()
                                    ->required(),
                                TextInput::make('age')
                                    ->label('Age')
                                    ->default(25)
                                    ->required(),
                                TextInput::make('no_of_slots')
                                    ->required(),
                            ]),
                        Block::make('female')
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->options([
                                        'male' => 'Male',
                                        'female' => 'Female',
                                        'kid' => 'Kid',
                                    ])
                                    ->default('female')
                                    ->disabled()
                                    ->required(),
                                TextInput::make('age')
                                    ->label('Age')
                                    ->default(25)
                                    ->required(),
                                TextInput::make('no_of_slots')
                                    ->required(),

                            ]),
                        Block::make('kid')
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->options([
                                        'male' => 'Male',
                                        'female' => 'Female',
                                        'kid' => 'Kid',
                                    ])
                                    ->default('kid')
                                    ->disabled()
                                    ->required(),
                                TextInput::make('age')
                                    ->label('Age')
                                    ->default(8)
                                    ->required(),
                                TextInput::make('no_of_slots')
                                    ->required(),

                            ]),
                    ])->columnSpanFull()
                    ->collapsible()
                    ->blockNumbers(false),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
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
