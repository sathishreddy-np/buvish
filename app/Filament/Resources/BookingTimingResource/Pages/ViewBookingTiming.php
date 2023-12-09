<?php

namespace App\Filament\Resources\BookingTimingResource\Pages;

use App\Filament\Resources\BookingTimingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookingTiming extends ViewRecord
{
    protected static string $resource = BookingTimingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
