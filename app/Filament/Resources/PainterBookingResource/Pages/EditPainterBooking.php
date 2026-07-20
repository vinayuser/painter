<?php

namespace App\Filament\Resources\PainterBookingResource\Pages;

use App\Filament\Resources\PainterBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPainterBooking extends EditRecord
{
    protected static string $resource = PainterBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
