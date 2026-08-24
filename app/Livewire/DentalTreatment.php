<?php

namespace App\Livewire;

use App\Models\Odontogram;
use App\Models\Patient;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class DentalTreatment extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public Patient $patient;

    public function mount(Patient $patient): void
    {
        $this->patient = $patient;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Odontogram::query()->where('patient_id', $this->patient->id))
            ->striped()
            ->searchable()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('dentition'),
                TextColumn::make('numbering_system'),
            ])
            ->filters([
                // ...
            ])
            ->headerActions([
                Action::make('register')
                    ->label('Registro odontograma')
                    ->icon('fas-tooth')
                    ->outlined()
                    ->modalWidth(Width::ScreenTwoExtraLarge)
                    ->modalHeading('Registro odontograma')
                    ->modalSubmitAction(false)
                    ->form([
                        ViewField::make('view')
                            ->view('filament.patients.odontogram-board')
                            ->viewData(fn(): array => ['record' => $this->patient])
                            ->columnSpanFull(),
                    ]),
            ])
            ->recordActions([
                Action::make('odontogram-edit'),
                DeleteAction::make()
            ])
            ->toolbarActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.dental-treatment');
    }
}
