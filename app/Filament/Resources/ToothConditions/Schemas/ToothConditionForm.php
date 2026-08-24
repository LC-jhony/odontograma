<?php

namespace App\Filament\Resources\ToothConditions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ToothConditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('color')
                    ->required(),
                Select::make('target')
                    ->options(['face' => 'Face', 'tooth' => 'Tooth', 'both' => 'Both'])
                    ->required(),
                Select::make('category')
                    ->options([
                        'sano' => 'Sano',
                        'patologia' => 'Patologia',
                        'restauracion' => 'Restauracion',
                        'protesis' => 'Protesis',
                        'quirurgico' => 'Quirurgico',
                    ])
                    ->default('patologia')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
