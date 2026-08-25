<?php

use App\Enum\PatientSex;
use App\Filament\Pages\OdontogramBoard;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the selected patient data in the form', function (): void {
    $patient = Patient::create([
        'first_name' => 'JHONY ANTONIO',
        'last_name' => 'Pilco Mamani',
        'document_number' => '12345678',
        'birth_date' => '1990-05-20',
        'sex' => PatientSex::Masculino,
        'phone' => '951034056',
        'email' => 'patient@example.com',
    ]);

    Livewire::test(OdontogramBoard::class)
        ->assertFormSet([
            'patientId' => $patient->id,
            'document_number' => '12345678',
            'birth_date' => '1990-05-20',
            'sex' => PatientSex::Masculino,
            'phone' => '951034056',
            'email' => 'patient@example.com',
        ]);
});

it('refreshes the form data when the patient changes', function (): void {
    $first = Patient::create([
        'first_name' => 'Wanda',
        'last_name' => 'Conner',
        'document_number' => '60356943',
        'birth_date' => '1985-01-15',
        'sex' => PatientSex::Femenino,
        'phone' => '+1 (718) 695-8556',
        'email' => 'xapoci@mailinator.com',
    ]);

    $second = Patient::create([
        'first_name' => 'Bruno',
        'last_name' => 'Diaz',
        'document_number' => '87654321',
        'birth_date' => '2000-07-30',
        'sex' => PatientSex::Masculino,
        'phone' => '987654321',
        'email' => 'bruno@example.com',
    ]);

    Livewire::test(OdontogramBoard::class)
        ->set('patientId', $first->id)
        ->assertFormSet([
            'document_number' => '60356943',
            'sex' => PatientSex::Femenino,
            'email' => 'xapoci@mailinator.com',
        ])
        ->set('patientId', $second->id)
        ->assertFormSet([
            'document_number' => '87654321',
            'sex' => PatientSex::Masculino,
            'email' => 'bruno@example.com',
        ]);
});
