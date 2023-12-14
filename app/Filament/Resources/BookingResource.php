<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\BookingTiming;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\NotificationType;
use Carbon\Carbon;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder as ComponentsBuilder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
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
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('slot', null);
                    }),
                Radio::make('slot')
                    ->options(function (callable $get) {
                        $day = Carbon::parse($get('booking_date'))->dayName;
                        $day = strtolower($day);
                        $branch_id = $get('branch_id');
                        $activity_id = $get('activity_id');

                        $booking_timing = BookingTiming::where('branch_id', $branch_id)
                            ->where('activity_id', $activity_id)
                            ->first();

                        if ($booking_timing) {
                            // $timings = json_decode($booking_timing->timings, true);
                            $timings = $booking_timing->timings;
                            $array = [];
                            foreach ($timings as $timing) {
                                if ($timing['type'] == "Opening Timings") {
                                    $days = $timing['data']['day'];
                                    $exists = in_array($day, $days);
                                    if ($exists) {
                                        // $dayZone
                                        $start_time = date("h:i:s A", strtotime($timing['data']['start_time']));
                                        $end_time = date("h:i:s A", strtotime($timing['data']['end_time']));
                                        array_push($array, $start_time . " - " . $end_time);
                                    }
                                }
                            }
                            $combined_array = array_combine($array, $array);
                            return $combined_array;
                        }
                    })
                    ->reactive()
                    // ->afterStateUpdated(function (callable $set) {
                    //     return $set('members', null);
                    // })
                    ->columnSpanFull()
                    ->columns(3),

                ComponentsBuilder::make('members')
                    ->blocks(
                        [
                            Block::make('male')
                                ->schema([
                                    Forms\Components\Select::make('gender')
                                        ->options(                        function (callable $get) {
                                            $timeRange = $get('slot');
                                            // Split the time range into start and end times
                                            list($startTime, $endTime) = explode(' - ', $timeRange);

                                            // Create DateTime objects to parse and format the times
                                            $startTimeObj = DateTime::createFromFormat('h:i:s A', $startTime);
                                            $endTimeObj = DateTime::createFromFormat('h:i:s A', $endTime);

                                            // Format the times in 24-hour format and store in separate variables
                                            $startTime24 = $startTimeObj->format('H:i:s');
                                            $endTime24 = $endTimeObj->format('H:i:s');

                                            $day = Carbon::parse($get('booking_date'))->dayName;
                                            $day = strtolower($day);
                                            $branch_id = $get('branch_id');
                                            $activity_id = $get('activity_id');

                                            $booking_timing = BookingTiming::where('branch_id', $branch_id)
                                                ->where('activity_id', $activity_id)
                                                ->first();

                                            if ($booking_timing) {
                                                // $timings = json_decode($booking_timing->timings, true);
                                                $timings = $booking_timing->timings;
                                                $array = [];
                                                foreach ($timings as $timing) {
                                                    if ($timing['type'] == "Opening Timings") {
                                                        $start_time = $timing['data']['start_time'];
                                                        $end_time = $timing['data']['end_time'];

                                                        if ($start_time == $startTime24 && $end_time == $endTime24) {
                                                            $genders = $timing['data']['allowed_genders'];
                                                            foreach ($genders as $gender) {
                                                                $gen = $gender['gender'];
                                                                if ($gen) {
                                                                    array_push($array, $gen);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                return $array;
                                            }
                                        }
                )
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
                        ]


                    )->columnSpanFull()
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
