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
use Filament\Forms\Components\DateTimePicker;
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
                Forms\Components\Select::make('customers')
                    ->multiple()
                    ->label('Customer')
                    ->relationship('customers', 'name')
                    ->preload()
                    ->required()
                    ->searchable()
                    ->suffixAction(
                        Action::make('Create Customer')
                            ->icon('heroicon-m-user')
                            ->form([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('branch_id')
                                    ->label('Branch')
                                    ->options(Branch::where('company_id', auth()->user()->company_id)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Forms\Components\Select::make('is_active')
                                    ->label('Active')
                                    ->options([
                                        true => 'Yes',
                                        false => 'No',
                                    ])
                                    ->default(true)
                                    ->required(),
                                Forms\Components\Select::make('notifications')
                                ->options(NotificationType::pluck('name', 'id')->map(function ($name) {
                                    return ucfirst($name);
                                }))

                                    ->multiple(),
                            ])
                            ->action(function (array $data): void {
                                $customer = Customer::firstOrCreate([
                                    'name' => $data['name'],
                                    'email' => $data['email'],
                                    'branch_id' => $data['branch_id'],
                                    'is_active' => $data['is_active'],
                                ]);
                                if($customer){
                                    $customer->notificationTypes()->sync($data['notifications']);
                                }
                            })
                    ),
                DateTimePicker::make('booking_starts_at')
                    ->seconds(false),
                DateTimePicker::make('booking_ends_at')
                    ->seconds(false),

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
