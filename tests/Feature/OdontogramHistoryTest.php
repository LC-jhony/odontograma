<?php

use App\Domain\Odontogram\OdontogramConsolidator;
use App\Livewire\OdontogramBoard;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\OdontogramToothFace;
use App\Models\Patient;
use App\Models\ToothCondition;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeCondition(string $code, string $label, string $target): ToothCondition
{
    return ToothCondition::create([
        'code' => $code,
        'label' => $label,
        'color' => '#D6455A',
        'target' => $target,
        'category' => 'patologia',
        'sort_order' => 10,
    ]);
}

it('reutiliza el único odontograma del paciente al montar el tablero dos veces', function (): void {
    $patient = Patient::create(['first_name' => 'Ana', 'last_name' => 'Lopez']);

    Livewire::test(OdontogramBoard::class, ['patient' => $patient]);
    Livewire::test(OdontogramBoard::class, ['patient' => $patient]);

    expect(Odontogram::where('patient_id', $patient->id)->count())->toBe(1);
});

it('consolida los odontogramas duplicados conservando el historial', function (): void {
    Schema::table('odontograms', function (Blueprint $table) {
        $table->dropUnique('odontograms_patient_id_unique');
    });

    $patient = Patient::create(['first_name' => 'Bruno', 'last_name' => 'Diaz']);
    $caries = makeCondition('caries', 'Caries', 'face');
    $corona = makeCondition('corona', 'Corona', 'tooth');

    $first = Odontogram::create(['patient_id' => $patient->id, 'dentition' => 'adult', 'numbering_system' => 'fdi']);
    $firstTooth = OdontogramTooth::create(['odontogram_id' => $first->id, 'fdi_code' => 11, 'whole_condition_id' => $corona->id]);
    OdontogramToothFace::create(['odontogram_tooth_id' => $firstTooth->id, 'face' => 'v', 'condition_id' => $caries->id]);

    $second = Odontogram::create(['patient_id' => $patient->id, 'dentition' => 'adult', 'numbering_system' => 'fdi']);
    $secondTooth = OdontogramTooth::create(['odontogram_id' => $second->id, 'fdi_code' => 21]);
    OdontogramToothFace::create(['odontogram_tooth_id' => $secondTooth->id, 'face' => 'o', 'condition_id' => $caries->id]);

    (new OdontogramConsolidator)->consolidate();

    expect(Odontogram::where('patient_id', $patient->id)->count())->toBe(1);

    $survivor = $patient->odontogram;

    // El historial conserva los tratamientos del odontograma fusionado
    // (corona + caries de la pieza 11); el del superviviente es estado actual.
    expect($survivor->treatmentLog()->count())->toBe(2);

    // El estado actual fusiona ambas piezas.
    expect($survivor->teeth()->where('fdi_code', 11)->first()->whole_condition_id)->toBe($corona->id);
    expect($survivor->teeth()->where('fdi_code', 21)->first())->not->toBeNull();
});

it('no re-marca una zona ya tratada y registra una observación', function (): void {
    $patient = Patient::create(['first_name' => 'Carla', 'last_name' => 'Ruiz']);
    $odontogram = $patient->odontogram()->create(['dentition' => 'adult', 'numbering_system' => 'fdi']);
    $caries = makeCondition('caries', 'Caries', 'face');
    makeCondition('obturacion', 'Obturación', 'face');

    $tooth = OdontogramTooth::create(['odontogram_id' => $odontogram->id, 'fdi_code' => 11]);
    $face = OdontogramToothFace::create(['odontogram_tooth_id' => $tooth->id, 'face' => 'v', 'condition_id' => $caries->id]);

    $component = Livewire::test(OdontogramBoard::class, ['patient' => $patient]);

    $component->set('activeCondition', 'obturacion')
        ->call('selectZone', 11, 'v');

    // El estado no cambia y aún no hay entrada en la bitácora.
    expect($face->fresh()->condition_id)->toBe($caries->id);
    expect($odontogram->treatmentLog()->count())->toBe(0);

    // Se abre el modal de observación con la zona detectada.
    $component->assertSet('showObservationModal', true)
        ->assertSet('observationFdiCode', 11)
        ->assertSet('observationFace', 'v')
        ->assertSet('observationConditionCode', 'caries');

    $component->set('observationText', 'Se re-trató la pieza por dolor')
        ->call('submitObservation')
        ->call('save');

    expect($odontogram->treatmentLog()->count())->toBe(1);

    $entry = $odontogram->treatmentLog()->first();
    expect($entry->face)->toBe('v')
        ->and($entry->observation)->toBe('Se re-trató la pieza por dolor')
        ->and($entry->condition_id)->toBe($caries->id);

    // El estado sigue sin cambios.
    expect($face->fresh()->condition_id)->toBe($caries->id);
});

it('registra en la bitácora un tratamiento nuevo sobre una zona sin tratar', function (): void {
    $patient = Patient::create(['first_name' => 'Daniel', 'last_name' => 'Torres']);
    $odontogram = $patient->odontogram()->create(['dentition' => 'adult', 'numbering_system' => 'fdi']);
    $caries = makeCondition('caries', 'Caries', 'face');

    $component = Livewire::test(OdontogramBoard::class, ['patient' => $patient]);

    $component->set('activeCondition', 'caries')
        ->call('selectZone', 11, 'v');

    // Aún no se persiste nada: el marcado queda pendiente hasta pulsar "Guardar".
    expect($odontogram->teeth()->count())->toBe(0);
    expect($component->get('pending'))->toHaveCount(1);

    $component->call('save');

    $tooth = $odontogram->teeth()->where('fdi_code', 11)->first();

    expect($tooth)->not->toBeNull();
    expect($tooth->faces()->where('face', 'v')->first()->condition_id)->toBe($caries->id);
    expect($odontogram->treatmentLog()->count())->toBe(1);
    expect($odontogram->treatmentLog()->first()->condition_id)->toBe($caries->id);
});

it('borra una zona tratada con sano y lo registra en la bitácora', function (): void {
    $patient = Patient::create(['first_name' => 'Elena', 'last_name' => 'Vega']);
    $odontogram = $patient->odontogram()->create(['dentition' => 'adult', 'numbering_system' => 'fdi']);
    $corona = makeCondition('corona', 'Corona', 'tooth');
    makeCondition('sano', 'Sano / Borrar', 'both');

    $tooth = OdontogramTooth::create(['odontogram_id' => $odontogram->id, 'fdi_code' => 11, 'whole_condition_id' => $corona->id]);

    $component = Livewire::test(OdontogramBoard::class, ['patient' => $patient]);

    $component->set('activeCondition', 'sano')
        ->call('selectZone', 11, 'numero');

    // El borrado queda pendiente hasta pulsar "Guardar".
    expect($tooth->fresh()->whole_condition_id)->toBe($corona->id);
    expect($odontogram->treatmentLog()->count())->toBe(0);

    $component->call('save');

    expect($tooth->fresh()->whole_condition_id)->toBeNull();
    expect($odontogram->treatmentLog()->count())->toBe(1);
    expect($odontogram->treatmentLog()->first()->condition_id)->toBe($corona->id);
});

it('aplica una condición de pieza completa al clicar el cuerpo del diente', function (): void {
    $patient = Patient::create(['first_name' => 'Fabián', 'last_name' => 'Mendoza']);
    $odontogram = $patient->odontogram()->create(['dentition' => 'adult', 'numbering_system' => 'fdi']);
    $extraccion = makeCondition('extraccion', 'Extracción indicada', 'tooth');

    $component = Livewire::test(OdontogramBoard::class, ['patient' => $patient]);

    $component->set('activeCondition', 'extraccion')
        ->call('selectZone', 11, 'v')
        ->call('save');

    $tooth = $odontogram->teeth()->where('fdi_code', 11)->first();

    expect($tooth)->not->toBeNull();
    expect($tooth->whole_condition_id)->toBe($extraccion->id);
    expect($tooth->faces()->count())->toBe(0);
    expect($odontogram->treatmentLog()->count())->toBe(1);
    expect($odontogram->treatmentLog()->first()->face)->toBeNull()
        ->and($odontogram->treatmentLog()->first()->condition_id)->toBe($extraccion->id);
});

it('abre el modal al re-clicar el cuerpo de una pieza ya tratada', function (): void {
    $patient = Patient::create(['first_name' => 'Gabriela', 'last_name' => 'Núñez']);
    $odontogram = $patient->odontogram()->create(['dentition' => 'adult', 'numbering_system' => 'fdi']);
    $extraccion = makeCondition('extraccion', 'Extracción indicada', 'tooth');
    OdontogramTooth::create(['odontogram_id' => $odontogram->id, 'fdi_code' => 11, 'whole_condition_id' => $extraccion->id]);

    Livewire::test(OdontogramBoard::class, ['patient' => $patient])
        ->set('activeCondition', 'extraccion')
        ->call('selectZone', 11, 'v')
        ->assertSet('showObservationModal', true)
        ->assertSet('observationConditionCode', 'extraccion');

    expect($odontogram->treatmentLog()->count())->toBe(0);
    expect($odontogram->teeth()->where('fdi_code', 11)->first()->whole_condition_id)->toBe($extraccion->id);
});
