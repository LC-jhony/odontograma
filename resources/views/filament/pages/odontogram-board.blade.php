<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->form }}
    </div>

    @if ($patient = $this->getSelectedPatient())
        <livewire:odontogram-board :patient="$patient" :key="'odontogram-' . $patient->id" />
    @else
        <p class="text-sm text-gray-500">Seleccione un paciente para registrar su odontograma.</p>
    @endif
</x-filament-panels::page>