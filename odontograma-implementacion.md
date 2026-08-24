# Implementación OdontogramBoard

Código completo generado para la implementación del odontograma interactivo.

---

## 1. `app/Livewire/OdontogramBoard.php`

```php
<?php

namespace App\Livewire;

use App\Domain\Odontogram\ToothNumbering;
use App\Domain\Odontogram\ToothSvgBuilder;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\OdontogramToothFace;
use App\Models\Patient;
use App\Models\ToothCondition;
use App\Models\ToothDefinition;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OdontogramBoard extends Component
{
    public Patient $patient;

    public ?Odontogram $odontogram = null;

    public string $dentition = 'adult';

    public string $numberingSystem = 'fdi';

    public ?string $activeCondition = null;

    public string $saveMessage = '';

    public function mount(Patient $patient, ?Odontogram $odontogram = null): void
    {
        $this->patient = $patient;

        // Reanuda el odontograma más reciente del paciente si no se pasó uno explícito.
        $this->odontogram = $odontogram
            ?? $patient->odontograms()->latest()->first()
            ?? $this->createOdontogram();

        $this->dentition = $this->odontogram->dentition;
        $this->numberingSystem = $this->odontogram->numbering_system;
    }

    protected function createOdontogram(): Odontogram
    {
        return $this->patient->odontograms()->create([
            'dentition' => $this->dentition,
            'numbering_system' => $this->numberingSystem,
        ]);
    }

    #[Computed]
    public function conditions()
    {
        return ToothCondition::orderBy('sort_order')->get();
    }

    #[Computed]
    public function definitions()
    {
        return ToothDefinition::query()
            ->where('dentition', $this->dentition)
            ->orderBy('display_order')
            ->get()
            ->groupBy('arch'); // ['upper' => Collection, 'lower' => Collection]
    }

    #[Computed]
    public function teethByCode()
    {
        return $this->odontogram
            ->teeth()
            ->with(['wholeCondition', 'faces.condition'])
            ->get()
            ->keyBy('fdi_code');
    }

    #[Computed]
    public function findings()
    {
        return $this->odontogram->findings();
    }

    /** Selecciona una condición de la paleta (equivalente a `active` en el Alpine original). */
    public function pickCondition(string $code): void
    {
        $this->activeCondition = $this->activeCondition === $code ? null : $code;
    }

    /**
     * Aplica la condición activa a una cara o a la pieza completa.
     * $face === 'numero' => aplica/quita a la pieza completa (equivalente al click en el número).
     */
    public function selectZone(int $fdiCode, string $face): void
    {
        if (! $this->activeCondition) {
            return;
        }

        $condition = ToothCondition::where('code', $this->activeCondition)->firstOrFail();

        $tooth = $this->odontogram->teeth()->firstOrCreate(['fdi_code' => $fdiCode]);

        if ($face === 'numero') {
            $this->applyWholeCondition($tooth, $condition);
        } else {
            $this->applyFaceCondition($tooth, $face, $condition);
        }

        unset($this->teethByCode, $this->findings); // invalida los computed cacheados
    }

    protected function applyWholeCondition(OdontogramTooth $tooth, ToothCondition $condition): void
    {
        if ($condition->code === 'sano') {
            $tooth->update(['whole_condition_id' => null]);

            return;
        }

        if (! $condition->appliesToTooth()) {
            return; // esta condición solo aplica a caras, no a pieza completa
        }

        $tooth->update(['whole_condition_id' => $condition->id]);
    }

    protected function applyFaceCondition(OdontogramTooth $tooth, string $face, ToothCondition $condition): void
    {
        if ($condition->code === 'sano') {
            $tooth->faces()->where('face', $face)->delete();

            return;
        }

        if (! $condition->appliesToFace()) {
            return;
        }

        OdontogramToothFace::updateOrCreate(
            ['odontogram_tooth_id' => $tooth->id, 'face' => $face],
            ['condition_id' => $condition->id]
        );
    }

    public function setDentition(string $value): void
    {
        $this->dentition = $value;
        $this->odontogram->update(['dentition' => $value]);
        unset($this->definitions, $this->teethByCode, $this->findings);
    }

    public function setNumberingSystem(string $value): void
    {
        $this->numberingSystem = $value;
        $this->odontogram->update(['numbering_system' => $value]);
    }

    /** Genera el SVG de una pieza (usado desde la vista, por cada diente del arco). */
    public function toothSvg(ToothDefinition $definition): string
    {
        $tooth = $this->teethByCode[$definition->fdi_code] ?? null;
        $conditions = $this->conditions->keyBy('code');

        return ToothSvgBuilder::render(
            def: $definition,
            shownNumber: ToothNumbering::display($definition->fdi_code, $this->dentition, $this->numberingSystem),
            faceMap: $tooth?->faceMap() ?? ['v' => 'sano', 'o' => 'sano', 'p' => 'sano', 'm' => 'sano', 'd' => 'sano'],
            wholeCode: $tooth?->wholeCondition?->code,
            conditionColors: $conditions->map->color->all(),
        );
    }

    public function save(): void
    {
        $this->odontogram->touch();
        $this->saveMessage = 'Guardado ✓';
        $this->dispatch('odontogram-saved');
    }

    public function render()
    {
        return view('livewire.odontogram-board');
    }
}
```

---

## 2. `resources/views/livewire/odontogram-board.blade.php`

```blade
<div x-data="{ hover: null }" class="space-y-6">

    {{-- Toolbar: paciente, dentición, numeración, guardar --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold">{{ $patient->fullName() }}</h2>
            <p class="text-xs text-gray-500" x-show="hover" x-cloak
               x-text="hover ? ('Pieza ' + hover.code + ' · ' + hover.face) : ''"></p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex overflow-hidden rounded-lg border border-gray-300">
                <button type="button" wire:click="setDentition('adult')"
                    @class([
                        'px-3 py-1.5 text-xs font-semibold transition-colors',
                        'bg-primary-600 text-white' => $dentition === 'adult',
                        'bg-white text-gray-700 hover:bg-gray-50' => $dentition !== 'adult',
                    ])>Adulto · 32</button>
                <button type="button" wire:click="setDentition('child')"
                    @class([
                        'border-l border-gray-300 px-3 py-1.5 text-xs font-semibold transition-colors',
                        'bg-primary-600 text-white' => $dentition === 'child',
                        'bg-white text-gray-700 hover:bg-gray-50' => $dentition !== 'child',
                    ])>Niño · 20</button>
            </div>

            <div class="inline-flex overflow-hidden rounded-lg border border-gray-300">
                <button type="button" wire:click="setNumberingSystem('fdi')"
                    @class([
                        'px-3 py-1.5 text-xs font-semibold transition-colors',
                        'bg-primary-600 text-white' => $numberingSystem === 'fdi',
                        'bg-white text-gray-700 hover:bg-gray-50' => $numberingSystem !== 'fdi',
                    ])>FDI</button>
                <button type="button" wire:click="setNumberingSystem('universal')"
                    @class([
                        'border-l border-gray-300 px-3 py-1.5 text-xs font-semibold transition-colors',
                        'bg-primary-600 text-white' => $numberingSystem === 'universal',
                        'bg-white text-gray-700 hover:bg-gray-50' => $numberingSystem !== 'universal',
                    ])>Universal</button>
            </div>

            <button type="button" wire:click="save"
                class="inline-flex items-center gap-1.5 rounded-lg bg-success-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-success-500">
                Guardar
            </button>
        </div>
    </div>

    {{-- Paleta de condiciones --}}
    <div class="flex flex-wrap gap-2">
        @foreach ($this->conditions as $condition)
            <button type="button"
                wire:click="pickCondition('{{ $condition->code }}')"
                class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold transition-all"
                style="border-color: {{ $condition->color }};
                       {{ $activeCondition === $condition->code ? 'background:'.$condition->color.'; color:#fff;' : 'color:'.$condition->color.';' }}">
                <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $condition->color }}"></span>
                {{ $condition->label }}
            </button>
        @endforeach
    </div>

    {{-- Arco superior --}}
    <div class="flex justify-center overflow-x-auto pb-1">
        <div class="flex gap-1 sm:gap-2">
            @foreach ($this->definitions['upper'] ?? [] as $definition)
                <div class="tooth-wrap">{!! $this->toothSvg($definition) !!}</div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="h-px flex-1 bg-gray-200"></div>
        <span class="text-[10px] font-mono text-gray-400">LÍNEA MEDIA</span>
        <div class="h-px flex-1 bg-gray-200"></div>
    </div>

    {{-- Arco inferior --}}
    <div class="flex justify-center overflow-x-auto pt-1">
        <div class="flex gap-1 sm:gap-2">
            @foreach ($this->definitions['lower'] ?? [] as $definition)
                <div class="tooth-wrap">{!! $this->toothSvg($definition) !!}</div>
            @endforeach
        </div>
    </div>

    {{-- Hallazgos --}}
    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold">Hallazgos</div>
        <div class="space-y-2 p-4">
            @forelse ($this->findings as $finding)
                <div class="flex items-center gap-2 text-sm">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $finding['color'] }}"></span>
                    <span class="font-mono font-semibold">{{ $finding['fdi_code'] }}</span>
                    <span>{{ $finding['label'] }} ({{ $finding['face_label'] }})</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Sin hallazgos registrados todavía.</p>
            @endforelse
        </div>
    </div>

    @if ($saveMessage)
        <div wire:transition class="text-xs font-semibold text-success-600">{{ $saveMessage }}</div>
    @endif
</div>
```

---

## 3. `app/Filament/Resources/Patients/Schemas/PatientForm.php`

```php
<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enum\PatientSex;
use App\Livewire\OdontogramBoard;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\LivewireField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                LivewireField::make('odontogram')
                    ->component(OdontogramBoard::class)
                    ->data(fn (Patient $record) => ['patient' => $record])
                    ->visible(fn (?Patient $record) => (bool) ($record?->exists ?? false))
                    ->columnSpanFull(),
            ]);
    }
}
```

> Nota: se reemplazó el `ViewField::make('view')->view('livewire.odontogram-board')` por `LivewireField`. El `ViewField` renderizaba la vista inline sin montar el componente Livewire, por lo que `wire:click` y las propiedades computed no habrían funcionado.

---

## 4. `resources/css/filament/admin/theme.css` — añadir al final

```css
@source '../../../../resources/views/**/*';

:root {
    --ink: #24303a;
    --border: #cbd5e1;
    --surface-alt: #eef2f6;
}

.tooth-svg {
    display: block;
    width: 100%;
    height: auto;
}

.tooth-wrap {
    width: 88px;
    flex-shrink: 0;
}
```

Las variables CSS (`--ink`, `--border`, `--surface-alt`) y `.tooth-svg`/`.tooth-wrap` son necesarias: `ToothSvgBuilder` emite `<svg viewBox="0 0 100 180" class="tooth-svg">` sin ancho/alto y usa esas `var()` de color internamente, y nada las definía en este proyecto.

---

## Post-implementación

- `vendor/bin/pint --dirty --format agent`
- Rebuild de assets: `npm run build` (o `npm run dev`)