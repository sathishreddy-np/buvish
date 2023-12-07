<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Send Email')
                ->icon('heroicon-m-document-text')
                ->color('success')
                ->mountUsing(fn (ComponentContainer $form, Customer $record) => $form->fill([
                    'from_email' => auth()->user()->email,
                    'to_email' => $record->email,
                    'reply_to' => [auth()->user()->email],
                    'cc' => array_filter([auth()->user()->superAdminEmail(), auth()->user()->adminEmail()]),
                ]))
                ->action(function (Customer $record, array $data): void {
                    Config::set('mail.from.address', auth()->user()->email);
                    Mail::html($data['message'], function ($message) use ($record, $data) {
                        $message->to($record->email);
                        $message->subject($data['subject']);
                        $message->cc($data['cc']);
                        $message->bcc($data['bcc']);
                        $message->replyTo($data['reply_to']);
                    });
                    Config::set('mail.from.address', env('MAIL_FROM_ADDRESS'));
                    Notification::make()
                        ->title("Email sent successfully to $record->email.")
                        ->success()
                        ->send();
                })
                ->form([
                    Section::make('Basic Configuration')
                        ->schema([
                            TextInput::make('from_email')
                                ->label('Email From')
                                ->disabled()
                                ->required(),
                            TextInput::make('to_email')
                                ->label('Email To')
                                ->disabled()
                                ->required(),
                        ])
                        ->columns(2)
                        ->collapsed()
                        ->compact(),
                    Section::make('Additional Configuration')
                        ->description('Press tab or enter to add more emails in this section input form fields.')
                        ->schema([
                            TagsInput::make('reply_to')
                                ->label('Reply To')
                                ->placeholder('Add Reply to emails')
                                ->required(),
                            TagsInput::make('cc')
                                ->label('CC')
                                ->placeholder('Add CC emails'),
                            TagsInput::make('bcc')
                                ->label('BCC')
                                ->placeholder('Add BCC emails'),

                        ])
                        ->columns(3)
                        ->collapsed()
                        ->compact(),
                    TextInput::make('subject')
                        ->label('Subject')
                        ->required(),
                    RichEditor::make('message')
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
                            'preview',
                        ])
                        ->required()
                        ->disableToolbarButtons([])
                        ->fileAttachmentsDisk('s3')
                        ->fileAttachmentsDirectory('attachments')
                        ->fileAttachmentsVisibility('private'),
                    FileUpload::make('attachments')
                        ->disk('s3')
                        ->directory('attachments')
                        ->visibility('private'),

                ])
                ->visible(function (Customer $record) {
                    return $record->notificationTypes()->where('name', 'email')->exists();
                }),
            Actions\EditAction::make(),
        ];
    }
}
