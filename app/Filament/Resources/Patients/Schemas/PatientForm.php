<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enum\PatientSex;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('document_number'),
                DatePicker::make('birth_date')
                    ->native(false),
                Select::make('sex')
                    ->options(PatientSex::class)
                    ->native(false),
                TextInput::make('phone'),
                TextInput::make('email')
                    ->email(),
                ViewField::make('view')
                    ->view('filament.patients.odontogram-board')
                    ->visible(fn (?Patient $record) => (bool) ($record?->exists ?? false)),
            ]);
    }
}
