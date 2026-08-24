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

    {{-- Historial de tratamientos --}}
    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-4 py-3 text-sm font-semibold">Historial de tratamientos</div>
        <div class="space-y-2 p-4">
            @forelse ($this->history as $entry)
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="font-mono font-semibold">{{ $entry->fdi_code }}</span>
                    <span class="text-xs text-gray-500">{{ $this->faceLabel($entry->face) }}</span>
                    @if ($entry->condition)
                        <span class="inline-flex items-center gap-1.5 text-xs">
                            <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $entry->condition->color }}"></span>
                            {{ $entry->condition->label }}
                        </span>
                    @endif
                    @if ($entry->observation)
                        <span class="text-xs italic text-gray-600">“{{ $entry->observation }}”</span>
                    @endif
                    @if ($entry->pending ?? false)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Sin guardar</span>
                    @endif
                    <span class="ml-auto text-xs text-gray-400">{{ $entry->registered_at->format('d/m/Y H:i') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Sin historial registrado todavía.</p>
            @endforelse
        </div>
    </div>

    @if ($saveMessage)
        <div wire:transition class="text-xs font-semibold text-success-600">{{ $saveMessage }}</div>
    @elseif (count($pending) > 0)
        <div wire:transition class="text-xs font-semibold text-amber-600">Hay cambios sin guardar</div>
    @endif

    {{-- Modal de observación al re-tratar una zona ya tratada --}}
    @if ($showObservationModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
                <h3 class="text-sm font-semibold">Observación del re-tratamiento</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Pieza {{ $observationFdiCode }} · {{ $this->observationZoneLabel() }}
                </p>
                <textarea
                    wire:model="observationText"
                    rows="3"
                    class="mt-3 w-full rounded-lg border border-gray-300 p-2 text-sm"
                    placeholder="Describa la observación del re-tratamiento..."
                ></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        wire:click="cancelObservation"
                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                    >Cancelar</button>
                    <button
                        type="button"
                        wire:click="submitObservation"
                        class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-500"
                    >Guardar observación</button>
                </div>
            </div>
        </div>
    @endif
</div>
