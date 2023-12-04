<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers\NotificationTypesRelationManager;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\NotificationType;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Name' => $record->name,
            'Email' => $record->email,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        true => "Yes",
                        false => "No",
                    ])
                    ->default(true)
                    ->required(),
                Forms\Components\Select::make('notifications')
                    ->multiple()
                    ->relationship('notificationTypes', 'name')
                    ->required()
                    ->preload()
                    ->hiddenOn('view'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notificationTypes.name')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function (Model $record) {
                        $notificationTypes = $record->notificationTypes->toArray();
                        $notificationTypes = array_map(function ($notificationType) {
                            return $notificationType['name'];
                        }, $notificationTypes);

                        return $notificationTypes;
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable()
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
                    Action::make('Send Email')
                        ->icon('heroicon-m-document-text')
                        ->mountUsing(fn (Forms\ComponentContainer $form, Customer $record) => $form->fill([
                            'email' => $record->email,
                            'reply_to' => [auth()->user()->email]
                        ]))
                        ->action(function (Customer $record, array $data): void {
                            Mail::html($data['message'], function ($message) use ($record, $data) {
                                $message->to($record->email);
                                $message->subject($data['subject']);
                                $message->cc($data['cc']);
                                $message->bcc($data['bcc']);
                                $message->replyTo($data['reply_to']);
                            });

                            Notification::make()
                                ->title("Email sent successfully to $record->email.")
                                ->success()
                                ->send();
                        })
                        ->form([
                            Forms\Components\TextInput::make('email')
                                ->label('Email To')
                                ->disabled()
                                ->required(),
                            Section::make('Additional Configuration')
                                ->description('Press tab or enter to add more emails in this section input form fields.')
                                ->schema([
                                    Forms\Components\TagsInput::make('reply_to')
                                        ->label('Reply To')
                                        ->placeholder("Add Reply to emails")
                                        ->required(),
                                    Forms\Components\TagsInput::make('cc')
                                        ->label('CC')
                                        ->placeholder("Add CC emails"),
                                    Forms\Components\TagsInput::make('bcc')
                                        ->label('BCC')
                                        ->placeholder("Add BCC emails"),

                                ])
                                ->columns(3)
                                ->collapsed()
                                ->compact(),
                            Forms\Components\TextInput::make('subject')
                                ->label('Subject')
                                ->required(),

                            Forms\Components\RichEditor::make('message')
                                ->label('Message')
                                ->toolbarButtons([
                                    'attachFiles',
                                    'blockquote',
                                    'bold',
                                    'bulletList',
                                    'codeBlock',
                                    'h2',
                                    'h3',
                                    'italic',
                                    'link',
                                    'orderedList',
                                    'redo',
                                    'strike',
                                    'underline',
                                    'undo',
                                ])
                            // ->disableToolbarButtons([])
                            // ->fileAttachmentsDisk('s3')
                            // ->fileAttachmentsDirectory('attachments')
                            // ->fileAttachmentsVisibility('private')
                        ]),
                ])
                    ->icon('heroicon-m-ellipsis-horizontal')
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
            NotificationTypesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('user_id', auth()->user()->id);
    }
}
