<?php

namespace App\Filament\Resources\BookingTimingResource\Pages;

use App\Filament\Resources\BookingTimingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookingTiming extends EditRecord
{
    protected static string $resource = BookingTimingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
