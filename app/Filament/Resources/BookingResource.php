<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\BookingTiming;
use App\Models\Branch;
use Carbon\Carbon;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-m-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->user()->id)
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->prefix('+91')
                    ->tel()
                    ->telRegex('/^[6789]\d{9}$/'),
                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::where('company_id', auth()->user()->company_id)->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('activity_id', null);
                    })
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
                                $start_time = date('h:i A', strtotime($timing['data']['start_time']));
                                $end_time = date('h:i A', strtotime($timing['data']['end_time']));
                                if (isset($timing['data']['no_of_slots'])) {
                                    $no_of_slots = $timing['data']['no_of_slots'] . ' slots left';
                                    $total_slots = $timing['data']['no_of_slots'];
                                }
                                $booking_date = $get('booking_date');
                                $booking_count = Booking::where('booking_date', $booking_date)
                                    ->where('slot', "$start_time - $end_time")
                                    ->selectRaw('SUM(JSON_LENGTH(members)) as total_members_count')
                                    ->value('total_members_count');
                                if ($booking_count) {
                                    $slots = (intval($total_slots) - $booking_count) . ' slots left';
                                    if ($slots == '0 slots left') {
                                        // continue;
                                    }
                                }

                                if ($timing['type'] == 'Opening Timings') {
                                    $days = $timing['data']['day'];
                                    $exists = in_array($day, $days);
                                    if ($exists) {
                                        // $dayZone
                                        array_push($array, $start_time . ' - ' . $end_time);
                                    }
                                }
                            }

                            $combined_array = array_combine($array, $array);

                            return $combined_array;
                        }
                    })
                    ->descriptions(
                        function (callable $get) {
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
                                    if (isset($timing['data']['no_of_slots'])) {
                                        $no_of_slots = $timing['data']['no_of_slots'] . ' slots left';
                                        $total_slots = $timing['data']['no_of_slots'];
                                    }
                                    $booking_date = $get('booking_date');
                                    $booking_count = Booking::where('booking_date', $booking_date)
                                        ->where('slot', "$start_time - $end_time")
                                        ->selectRaw('SUM(JSON_LENGTH(members)) as total_members_count')
                                        ->value('total_members_count');
                                    if ($booking_count) {
                                        $slots = intval($total_slots) - $booking_count . ' slots left';
                                        if ($slots == '0 slots left') {
                                            // continue;
                                        }
                                        $no_of_slots = $slots;
                                    }

                                    $genders = array_filter(array_column($timing['data']['allowed_genders'], 'gender'));

                                    $combined_array["$start_time - $end_time"] = implode(', ', array_map(function ($gender) {
                                        return ucfirst($gender);
                                    }, $genders)) . ' - ' . $no_of_slots;
                                }
                            }

                            return $combined_array;
                        }
                    )
                    ->disableOptionWhen(function (callable $get, string $value) {
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
                            foreach ($timings as $timing) {
                                $start_time = date('h:i A', strtotime($timing['data']['start_time']));
                                $end_time = date('h:i A', strtotime($timing['data']['end_time']));
                                if (isset($timing['data']['no_of_slots'])) {
                                    $no_of_slots = $timing['data']['no_of_slots'] . ' slots left';
                                    $total_slots = $timing['data']['no_of_slots'];
                                }
                                $booking_date = $get('booking_date');
                                $booking_count = Booking::where('booking_date', $booking_date)
                                    ->where('slot', "$start_time - $end_time")
                                    ->selectRaw('SUM(JSON_LENGTH(members)) as total_members_count')
                                    ->value('total_members_count');
                                if ($booking_count) {
                                    $slots = (intval($total_slots) - $booking_count) . ' slots left';
                                    if ($slots == '0 slots left') {
                                        return $value === "$start_time - $end_time";
                                    }
                                }
                            }
                        }
                    })
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('members', null);
                    })
                    ->hidden(fn (Get $get): bool => !($get('branch_id') && $get('activity_id') && $get('booking_date')))
                    ->required()
                    ->columnSpanFull()
                    ->columns(3),

                Repeater::make('members')
                    ->schema([
                        TextInput::make('name'),
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
                            ->required()
                            ->live(debounce: 500),
                        TextInput::make('amount')
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
                            ->readOnly()
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
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('mode_of_payment', null);
                    })
                    ->cloneable(),
                Select::make('mode_of_payment')
                    ->options([
                        "cash" => "Cash",
                        "online" => "Online"
                    ])
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        return $set('request_payment', null);
                    })

                    ->hidden(fn (Get $get): bool => !($get('members')))
                    ->required(),
                Actions::make([
                    Action::make('request_payment')
                        ->icon('heroicon-m-currency-rupee')
                        ->color('success')
                        ->action(function (callable $get) {
                            $amount = 0;
                            foreach ($get('members') as $member) {
                                $amount += $member['amount'];
                            }
                            if ($amount != 0) {
                                Notification::make()
                                    ->title("Payment requested Rs. $amount successfully.")
                                    ->success()
                                    ->send();
                            }

                            if ($amount == 0) {
                                Notification::make()
                                    ->title("Add atleast 1 member.")
                                    ->danger()
                                    ->send();
                            }
                        })->hidden(fn (Get $get): bool => !($get('mode_of_payment') == "online"))

                ])



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_date')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot')
                    ->searchable()
                    ->sortable(),
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
                Filter::make('booking_date')
                    ->form([
                        DatePicker::make('booking_from'),
                        DatePicker::make('booking_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['booking_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '>=', $date),
                            )
                            ->when(
                                $data['booking_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '<=', $date),
                            );
                    }),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('updated_at')
                    ->form([
                        DatePicker::make('updated_from'),
                        DatePicker::make('updated_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['updated_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                            )
                            ->when(
                                $data['updated_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date),
                            );
                    }),

            ], layout: FiltersLayout::AboveContentCollapsible)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Actions'),
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
