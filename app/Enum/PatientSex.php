<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Override;

enum PatientSex: string implements HasColor, HasIcon, HasLabel
{
    case Masculino = 'Masculino';
    case Femenino = 'Femenino';
    case Otro = 'Otro';

    #[Override]
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Masculino => 'Masculino',
            self::Femenino => 'Femenino',
            self::Otro => 'Otro',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Masculino => 'success',
            self::Femenino => 'danger',
            self::Otro => 'info'
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Masculino => 'ionicon-male-outline',
            self::Femenino => 'ionicon-male-female-outline',
            self::Otro => 'tabler-gender-intergender'
        };
    }
}
