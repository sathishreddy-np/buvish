<?php

namespace App\Filament\Resources\NotificationTypeResource\Pages;

use App\Filament\Resources\NotificationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNotificationType extends ViewRecord
{
    protected static string $resource = NotificationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
