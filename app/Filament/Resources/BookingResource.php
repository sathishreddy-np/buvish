<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\BookingTiming;
use App\Models\Branch;
use Carbon\Carbon;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                Forms\Components\TextInput::make('phone')
                    ->prefix('+91')
                    ->tel()
                    ->telRegex('/^[6789]\d{9}$/')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('branch_id', null);
                    }),

                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::where('company_id', auth()->user()->company_id)->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('activity_id', null);
                    })
                    ->hidden(fn (Get $get): bool => !$get('phone'))
                    ->searchable(),
                Forms\Components\Select::make('activity_id')
                    ->label('Activity')
                    ->options(
                        function (callable $get) {
                            $branch_id = $get('branch_id');
                            $branch = Branch::where('id', $branch_id)->first();
                            if ($branch) {
                                return $branch->activities()->pluck('activities.name', 'activities.id')->map(function ($name) {
                                    return ucfirst($name);
                                });
                            }
                        }

                    )
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('booking_date', null);
                    })
                    ->hidden(fn (Get $get): bool => !$get('branch_id'))
                    ->searchable(),
                DatePicker::make('booking_date')
                    ->native(false)
                    ->minDate(today())
                    ->required()
                    ->closeOnDateSelection()
                    ->displayFormat('d-M-Y')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('slot', null);
                    })
                    ->hidden(fn (Get $get): bool => !$get('activity_id')),
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
                                if ($timing['type'] == 'Opening Timings') {
                                    $days = $timing['data']['day'];
                                    $exists = in_array($day, $days);
                                    if ($exists) {
                                        // $dayZone
                                        $start_time = date('h:i A', strtotime($timing['data']['start_time']));
                                        $end_time = date('h:i A', strtotime($timing['data']['end_time']));
                                        array_push($array, $start_time . ' - ' . $end_time);
                                    }
                                }
                            }

                            $combined_array = array_combine($array, $array);

                            return $combined_array;
                        }
                    })
                    ->descriptions(function (callable $get)
                    {
                        $day = strtolower(Carbon::parse($get('booking_date'))->dayName);
                        $branch_id = $get('branch_id');
                        $activity_id = $get('activity_id');

                        $booking_timing = BookingTiming::where('branch_id', $branch_id)
                            ->where('activity_id', $activity_id)
                            ->first();

                        if (!$booking_timing) {
                            return null;
                        }

                        $timings = $booking_timing->timings;
                        $combined_array = [];

                        foreach ($timings as $timing) {
                            if ($timing['type'] == 'Opening Timings' && in_array($day, $timing['data']['day'])) {
                                $start_time = date('h:i A', strtotime($timing['data']['start_time']));
                                $end_time = date('h:i A', strtotime($timing['data']['end_time']));

                                $genders = array_filter(array_column($timing['data']['allowed_genders'], 'gender'));

                                $no_of_slots = $timing['data']['no_of_slots']." slots left";

                                $combined_array["$start_time - $end_time"] = implode(', ', $genders) . ' - ' . $no_of_slots;
                            }
                        }

                        return $combined_array;
                    }
                    )
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('gender', null);
                    })
                    ->hidden(fn (Get $get): bool => !($get('branch_id') && $get('activity_id') && $get('booking_date')))
                    ->required()
                    ->columnSpanFull()
                    ->columns(3),

                Repeater::make('members')
                    ->schema([
                        TextInput::make('Name'),
                        Select::make('gender')
                            ->options(
                                function (callable $get) {
                                    $timeRange = $get('../../slot');
                                    [$startTime, $endTime] = explode(' - ', $timeRange);
                                    $startTimeObj = DateTime::createFromFormat('h:i A', $startTime);
                                    $endTimeObj = DateTime::createFromFormat('h:i A', $endTime);
                                    $startTime24 = $startTimeObj->format('H:i');
                                    $endTime24 = $endTimeObj->format('H:i');

                                    $day = Carbon::parse($get('booking_date'))->dayName;
                                    $day = strtolower($day);
                                    $branch_id = $get('../../branch_id');
                                    $activity_id = $get('../../activity_id');
                                    $booking_timing = BookingTiming::where('branch_id', $branch_id)
                                        ->where('activity_id', $activity_id)
                                        ->first();
                                    if ($booking_timing) {
                                        $timings = $booking_timing->timings;
                                        $array = [];
                                        foreach ($timings as $timing) {
                                            if ($timing['type'] == 'Opening Timings') {
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

                                        $array_combine = array_combine($array, array_map(fn ($value) => ucfirst($value), $array));

                                        return $array_combine;
                                    }
                                }
                            )
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                return $set('age', null);
                            })
                            ->required(),

                        TextInput::make('age')
                            ->numeric()
                            ->minValue(
                                function (callable $get) {
                                    $timeRange = $get('../../slot');
                                    [$startTime, $endTime] = explode(' - ', $timeRange);
                                    $startTimeObj = DateTime::createFromFormat('h:i A', $startTime);
                                    $endTimeObj = DateTime::createFromFormat('h:i A', $endTime);
                                    $startTime24 = $startTimeObj->format('H:i');
                                    $endTime24 = $endTimeObj->format('H:i');

                                    $day = Carbon::parse($get('booking_date'))->dayName;
                                    $day = strtolower($day);
                                    $branch_id = $get('../../branch_id');
                                    $activity_id = $get('../../activity_id');
                                    $booking_timing = BookingTiming::where('branch_id', $branch_id)
                                        ->where('activity_id', $activity_id)
                                        ->first();

                                    if ($booking_timing) {
                                        $timings = $booking_timing->timings;
                                        foreach ($timings as $timing) {
                                            if ($timing['type'] == 'Opening Timings') {
                                                $start_time = $timing['data']['start_time'];
                                                $end_time = $timing['data']['end_time'];

                                                if ($start_time == $startTime24 && $end_time == $endTime24) {
                                                    $genders = $timing['data']['allowed_genders'];
                                                    foreach ($genders as $gender) {
                                                        $gen = $gender['gender'];
                                                        $input_gen = $get('gender');
                                                        // dd($gen);
                                                        // dump($input_gen);
                                                        if ($gen == $input_gen) {
                                                            $min_value = $gender['age_from'];
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        return $min_value;
                                    }
                                }
                            )
                            ->maxValue(
                                function (callable $get) {
                                    $timeRange = $get('../../slot');
                                    [$startTime, $endTime] = explode(' - ', $timeRange);
                                    $startTimeObj = DateTime::createFromFormat('h:i A', $startTime);
                                    $endTimeObj = DateTime::createFromFormat('h:i A', $endTime);
                                    $startTime24 = $startTimeObj->format('H:i');
                                    $endTime24 = $endTimeObj->format('H:i');

                                    $day = Carbon::parse($get('booking_date'))->dayName;
                                    $day = strtolower($day);
                                    $branch_id = $get('../../branch_id');
                                    $activity_id = $get('../../activity_id');
                                    $booking_timing = BookingTiming::where('branch_id', $branch_id)
                                        ->where('activity_id', $activity_id)
                                        ->first();

                                    if ($booking_timing) {
                                        $timings = $booking_timing->timings;
                                        foreach ($timings as $timing) {
                                            if ($timing['type'] == 'Opening Timings') {
                                                $start_time = $timing['data']['start_time'];
                                                $end_time = $timing['data']['end_time'];

                                                if ($start_time == $startTime24 && $end_time == $endTime24) {
                                                    $genders = $timing['data']['allowed_genders'];
                                                    foreach ($genders as $gender) {
                                                        $gen = $gender['gender'];
                                                        $input_gen = $get('gender');
                                                        // dump($gen);
                                                        // dd($input_gen);
                                                        if ($gen == $input_gen) {
                                                            $max_value = $gender['age_to'];
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        return $max_value;
                                    }
                                }
                            )
                            ->reactive()
                            ->afterStateUpdated(
                                function (callable $set, callable $get) {
                                    $timeRange = $get('../../slot');
                                    [$startTime, $endTime] = explode(' - ', $timeRange);
                                    $startTimeObj = DateTime::createFromFormat('h:i A', $startTime);
                                    $endTimeObj = DateTime::createFromFormat('h:i A', $endTime);
                                    $startTime24 = $startTimeObj->format('H:i');
                                    $endTime24 = $endTimeObj->format('H:i');

                                    $day = Carbon::parse($get('booking_date'))->dayName;
                                    $day = strtolower($day);
                                    $branch_id = $get('../../branch_id');
                                    $activity_id = $get('../../activity_id');
                                    $booking_timing = BookingTiming::where('branch_id', $branch_id)
                                        ->where('activity_id', $activity_id)
                                        ->first();

                                    if ($booking_timing) {
                                        $timings = $booking_timing->timings;
                                        foreach ($timings as $timing) {
                                            if ($timing['type'] == 'Opening Timings') {
                                                $start_time = $timing['data']['start_time'];
                                                $end_time = $timing['data']['end_time'];

                                                if ($start_time == $startTime24 && $end_time == $endTime24) {
                                                    $genders = $timing['data']['allowed_genders'];
                                                    foreach ($genders as $gender) {
                                                        $gen = $gender['gender'];
                                                        $input_gen = $get('gender');
                                                        if ($gen == $input_gen) {
                                                            $amount = $gender['amount'];
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        return $set('amount', $amount);
                                    }
                                }
                            )
                            ->hidden(fn (Get $get): bool => !($get('gender')))
                            ->required(),
                        TextInput::make('amount')
                        ->default(                                function (callable $set, callable $get) {
                            $timeRange = $get('../../slot');
                            [$startTime, $endTime] = explode(' - ', $timeRange);
                            $startTimeObj = DateTime::createFromFormat('h:i A', $startTime);
                            $endTimeObj = DateTime::createFromFormat('h:i A', $endTime);
                            $startTime24 = $startTimeObj->format('H:i');
                            $endTime24 = $endTimeObj->format('H:i');

                            $day = Carbon::parse($get('booking_date'))->dayName;
                            $day = strtolower($day);
                            $branch_id = $get('../../branch_id');
                            $activity_id = $get('../../activity_id');
                            $booking_timing = BookingTiming::where('branch_id', $branch_id)
                                ->where('activity_id', $activity_id)
                                ->first();

                            if ($booking_timing) {
                                $timings = $booking_timing->timings;
                                foreach ($timings as $timing) {
                                    if ($timing['type'] == 'Opening Timings') {
                                        $start_time = $timing['data']['start_time'];
                                        $end_time = $timing['data']['end_time'];

                                        if ($start_time == $startTime24 && $end_time == $endTime24) {
                                            $genders = $timing['data']['allowed_genders'];
                                            foreach ($genders as $gender) {
                                                $gen = $gender['gender'];
                                                $input_gen = $get('gender');
                                                if ($gen == $input_gen) {
                                                    $amount = $gender['amount'];
                                                }
                                            }
                                        }
                                    }
                                }

                                return $amount;
                            }
                        }
)
                            ->minValue(
                                function (callable $get) {
                                    $timeRange = $get('../../slot');
                                    [$startTime, $endTime] = explode(' - ', $timeRange);
                                    $startTimeObj = DateTime::createFromFormat('h:i A', $startTime);
                                    $endTimeObj = DateTime::createFromFormat('h:i A', $endTime);
                                    $startTime24 = $startTimeObj->format('H:i');
                                    $endTime24 = $endTimeObj->format('H:i');

                                    $day = Carbon::parse($get('booking_date'))->dayName;
                                    $day = strtolower($day);
                                    $branch_id = $get('../../branch_id');
                                    $activity_id = $get('../../activity_id');
                                    $booking_timing = BookingTiming::where('branch_id', $branch_id)
                                        ->where('activity_id', $activity_id)
                                        ->first();

                                    if ($booking_timing) {
                                        $timings = $booking_timing->timings;
                                        foreach ($timings as $timing) {
                                            if ($timing['type'] == 'Opening Timings') {
                                                $start_time = $timing['data']['start_time'];
                                                $end_time = $timing['data']['end_time'];

                                                if ($start_time == $startTime24 && $end_time == $endTime24) {
                                                    $genders = $timing['data']['allowed_genders'];
                                                    foreach ($genders as $gender) {
                                                        $gen = $gender['gender'];
                                                        $input_gen = $get('gender');
                                                        if ($gen == $input_gen) {
                                                            $amount = $gender['amount'];
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        return $amount;
                                    }
                                }
                            )
                            ->numeric()
                            ->required()
                            ->hidden(fn (Get $get): bool => !($get('age'))),

                    ])
                    ->hidden(fn (Get $get): bool => !($get('branch_id') && $get('activity_id') && $get('booking_date') && $get('slot')))
                    ->defaultItems(0)
                    ->minItems(1)
                    ->columnSpanFull()
                    ->columns(4)
                    ->collapsible()
                    ->cloneable(),


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
