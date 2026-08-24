<?php

namespace App\Filament\Resources\ToothDefinitions\Pages;

use App\Filament\Resources\ToothDefinitions\ToothDefinitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListToothDefinitions extends ListRecords
{
    protected static string $resource = ToothDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::SquaresPlus),
        ];
    }
}
