<?php

namespace App\Filament\Pages;

use App\Enum\PatientSex;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

class OdontogramBoard extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.odontogram-board';

    public ?int $patientId = null;

    public ?string $document_number = null;

    public ?string $birth_date = null;

    public ?string $sex = null;

    public ?string $phone = null;

    public ?string $email = null;

    public function mount(): void
    {
        $this->form->fill();
        $this->fillFormWithPatientData();
    }

    public function updatedPatientId(): void
    {
        $this->fillFormWithPatientData();
    }

    private function fillFormWithPatientData(): void
    {
        $patient = $this->getSelectedPatient();

        $this->form->fill([
            'patientId' => $patient?->id,
            'document_number' => $patient?->document_number,
            'birth_date' => $patient?->birth_date?->format('Y-m-d'),
            'sex' => $patient?->sex?->value,
            'phone' => $patient?->phone,
            'email' => $patient?->email,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos personales')
                    ->description('datos del paciente y odontograma')
                    ->columns(3)
                    ->schema([
                        Select::make('patientId')
                            ->label('Paciente')
                            ->placeholder('Seleccione un paciente')
                            ->default(fn(): ?int => Patient::query()
                                ->orderBy('first_name')
                                ->value('id'))
                            ->options(fn(): array => Patient::query()
                                ->orderBy('first_name')
                                ->get()
                                ->mapWithKeys(fn(Patient $patient): array => [
                                    $patient->id => "{$patient->fullName()} ",
                                ])
                                ->all())
                            ->live()
                            ->searchable(),
                        TextInput::make('document_number')
                            ->disabled(),
                        DatePicker::make('birth_date')
                            ->native(false)
                            ->disabled(),
                        Select::make('sex')
                            ->options(PatientSex::class)
                            ->native(false)
                            ->disabled(),
                        TextInput::make('phone')
                            ->disabled(),
                        TextInput::make('email')
                            ->email()
                            ->disabled(),
                    ])
            ]);
    }

    public function getSelectedPatient(): ?Patient
    {
        return $this->patientId ? Patient::find($this->patientId) : null;
    }
}
