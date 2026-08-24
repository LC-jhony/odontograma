<?php

namespace App\Filament\Resources\ToothConditions\Pages;

use App\Filament\Resources\ToothConditions\ToothConditionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditToothCondition extends EditRecord
{
    protected static string $resource = ToothConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
