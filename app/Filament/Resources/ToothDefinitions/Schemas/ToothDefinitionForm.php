<?php

namespace App\Filament\Resources\ToothDefinitions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ToothDefinitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fdi_code')
                    ->required()
                    ->numeric(),
                Select::make('dentition')
                    ->options(['adult' => 'Adult', 'child' => 'Child'])
                    ->required(),
                Select::make('arch')
                    ->options(['upper' => 'Upper', 'lower' => 'Lower'])
                    ->required(),
                TextInput::make('quadrant')
                    ->required()
                    ->numeric(),
                TextInput::make('position')
                    ->required()
                    ->numeric(),
                TextInput::make('tooth_type')
                    ->required(),
                TextInput::make('root_count')
                    ->required()
                    ->numeric(),
                TextInput::make('universal_number')
                    ->numeric()
                    ->default(null),
                TextInput::make('universal_letter')
                    ->default(null),
                TextInput::make('display_order')
                    ->required()
                    ->numeric(),
            ]);
    }
}
