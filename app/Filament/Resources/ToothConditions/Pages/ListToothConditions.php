<?php

namespace App\Filament\Resources\ToothConditions\Pages;

use App\Filament\Resources\ToothConditions\ToothConditionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListToothConditions extends ListRecords
{
    protected static string $resource = ToothConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::SquaresPlus),
        ];
    }
}
