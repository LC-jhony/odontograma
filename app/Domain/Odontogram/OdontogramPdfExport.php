<?php

declare(strict_types=1);

namespace App\Domain\Odontogram;

use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use App\Models\ToothCondition;
use App\Models\ToothDefinition;
use DragonOfMercy\PhpPdf\Color;
use DragonOfMercy\PhpPdf\Document;
use DragonOfMercy\PhpPdf\Font;
use DragonOfMercy\PhpPdf\Image;
use DragonOfMercy\PhpPdf\Orientation;
use DragonOfMercy\PhpPdf\Page;
use DragonOfMercy\PhpPdf\PageFormat;
use DragonOfMercy\PhpPdf\Unit;
use Illuminate\Support\Collection;

final class OdontogramPdfExport
{
    private Document $doc;

    private Font $font;

    private Font $boldFont;

    private Collection $conditionsByCode;

    private array $conditionColors;

    public function __construct()
    {
        $this->doc = new Document(Unit::MM);
        $this->font = Font::helvetica();
        $this->boldFont = Font::helvetica()->bold();
        $this->doc->setDefaultFont($this->font, 10);
        $this->doc->setMargins(15);
        $this->doc->setAutoPageBreak(true, 15);

        $this->conditionsByCode = ToothCondition::orderBy('sort_order')
            ->get()
            ->keyBy('code');

        $this->conditionColors = $this->conditionsByCode->map->color->all();
    }

    public function generate(Odontogram $odontogram): string
    {
        $patient = $odontogram->patient;
        $this->doc->addPage(PageFormat::A4, Orientation::LANDSCAPE);

        $this->renderHeader($patient, $odontogram);
        $this->renderOdontogramChart($odontogram);
        $this->renderFindings($odontogram);
        $this->renderHistory($odontogram);

        if (trim((string) $odontogram->notes) !== '') {
            $this->renderNotes($odontogram);
        }

        return $this->doc->output();
    }

    private function renderHeader(Patient $patient, Odontogram $odontogram): void
    {
        $page = $this->doc->getCurrentPage();

        $page->setFont($this->boldFont, 16);
        $page->text(15, 20, 'Odontograma');

        $page->setFont($this->font, 10);
        $y = 30;
        $page->text(15, $y, "Paciente: {$patient->fullName()}");
        $y += 6;
        $page->text(15, $y, "DNI: {$patient->document_number}");
        $y += 6;
        $page->text(15, $y, "Fecha nacimiento: {$patient->birth_date?->format('d/m/Y')}");
        $page->text(110, $y, "Sexo: {$patient->sex?->value}");
        $y += 6;
        $page->text(15, $y, "Teléfono: {$patient->phone}");
        $page->text(110, $y, "Email: {$patient->email}");

        if ($odontogram->examined_at) {
            $y += 6;
            $page->text(15, $y, "Fecha de examen: {$odontogram->examined_at->format('d/m/Y')}");
        }

        $y += 10;
        $page->line(15, $y, 282, $y)->stroke();
    }

    private function renderOdontogramChart(Odontogram $odontogram): void
    {
        $page = $this->doc->getCurrentPage();
        $dentition = $odontogram->dentition;
        $numberingSystem = $odontogram->numbering_system;

        $teethByCode = $odontogram->teeth()
            ->with(['wholeCondition', 'faces.condition'])
            ->get()
            ->keyBy('fdi_code');

        $definitions = ToothDefinition::query()
            ->where('dentition', $dentition)
            ->orderBy('display_order')
            ->get()
            ->groupBy('arch');

        $svgWidth = 12;
        $svgHeight = 21.6;
        $gap = 1;

        $upper = $definitions['upper'] ?? collect();
        $totalWidth = $upper->count() * ($svgWidth + $gap);
        $startX = (297 - $totalWidth) / 2;

        $this->renderArch($page, $upper, $teethByCode, $dentition, $numberingSystem, $startX, 60, $svgWidth, $svgHeight, $gap);

        $ySep = 60 + $svgHeight + 3;
        $page->setFont($this->font, 7);
        $page->text(140, $ySep + 3, '--- LÍNEA MEDIA ---');

        $lower = $definitions['lower'] ?? collect();
        $this->renderArch($page, $lower, $teethByCode, $dentition, $numberingSystem, $startX, $ySep + 8, $svgWidth, $svgHeight, $gap);
    }

    private function renderArch(
        Page $page,
        Collection $definitions,
        Collection $teethByCode,
        string $dentition,
        string $numberingSystem,
        float $startX,
        float $y,
        float $svgWidth,
        float $svgHeight,
        float $gap,
    ): void {
        foreach ($definitions as $i => $def) {
            $state = $teethByCode->get($def->fdi_code);
            $faceMap = $state?->faceMap() ?? OdontogramTooth::DEFAULT_FACE_MAP;
            $wholeCode = $state?->wholeCondition?->code;

            $svg = ToothSvgBuilder::render(
                def: $def,
                shownNumber: ToothNumbering::display($def->fdi_code, $dentition, $numberingSystem),
                faceMap: $faceMap,
                wholeCode: $wholeCode,
                conditionColors: $this->conditionColors,
                interactive: false,
            );

            $x = $startX + $i * ($svgWidth + $gap);
            $img = Image::fromBytes($svg);
            $page->image($img, $x, $y, $svgWidth, $svgHeight);
        }
    }

    private function renderFindings(Odontogram $odontogram): void
    {
        $page = $this->doc->getCurrentPage();
        $y = 125;

        $this->renderSectionHeader($page, 'Hallazgos', $y);

        $teeth = $odontogram->teeth()
            ->with(['wholeCondition', 'faces.condition'])
            ->get();

        $findings = $this->collectFindings($teeth);

        if (empty($findings)) {
            $page->setFont($this->font, 9);
            $page->text(15, $y, 'Sin hallazgos registrados.');

            return;
        }

        $page->setFont($this->font, 9);
        foreach ($findings as $finding) {
            $this->ensurePageSpace($page, $y, 170);

            $page->setFillColor(Color::hex($finding['color']));
            $page->circle(17, $y - 1.5, 1.5)->fill();
            $page->setFillColor(Color::hex('#000000'));

            $page->text(21, $y, "{$finding['fdi']} — {$finding['label']} ({$finding['face']})");
            $y += 5;
        }
    }

    private function collectFindings(Collection $teeth): array
    {
        $findings = [];

        foreach ($teeth as $tooth) {
            if ($tooth->wholeCondition && $tooth->wholeCondition->code !== ToothCondition::CODE_SANO) {
                $findings[] = [
                    'fdi' => $tooth->fdi_code,
                    'label' => $tooth->wholeCondition->label,
                    'color' => $tooth->wholeCondition->color,
                    'face' => 'Pieza completa',
                ];
            }

            foreach ($tooth->faces as $face) {
                if ($face->condition?->code !== ToothCondition::CODE_SANO) {
                    $findings[] = [
                        'fdi' => $tooth->fdi_code,
                        'label' => $face->condition->label,
                        'color' => $face->condition->color,
                        'face' => ToothCondition::FACE_LABELS[$face->face] ?? $face->face,
                    ];
                }
            }
        }

        return $findings;
    }

    private function renderHistory(Odontogram $odontogram): void
    {
        $page = $this->doc->getCurrentPage();
        $y = $page->getY() + 8;

        $this->ensurePageSpace($page, $y, 160);
        $this->renderSectionHeader($page, 'Historial de tratamientos', $y);

        $log = $odontogram->treatmentLog()
            ->with('condition')
            ->orderByDesc('registered_at')
            ->get();

        if ($log->isEmpty()) {
            $page->setFont($this->font, 9);
            $page->text(15, $y, 'Sin historial registrado todavía.');

            return;
        }

        $page->setFont($this->font, 8);
        foreach ($log as $entry) {
            $this->ensurePageSpace($page, $y, 175);

            $faceLabel = $entry->face
                ? (ToothCondition::FACE_LABELS[$entry->face] ?? $entry->face)
                : 'Pieza completa';
            $conditionLabel = $entry->condition?->label ?? '—';
            $date = $entry->registered_at->format('d/m/Y H:i');
            $obs = $entry->observation ? " \"{$entry->observation}\"" : '';

            $page->text(15, $y, "{$entry->fdi_code} | {$faceLabel} | {$conditionLabel}{$obs} | {$date}");
            $y += 4.5;
        }
    }

    private function renderNotes(Odontogram $odontogram): void
    {
        $page = $this->doc->getCurrentPage();
        $y = $page->getY() + 8;

        $this->ensurePageSpace($page, $y, 160);
        $this->renderSectionHeader($page, 'Notas', $y);

        $page->setFont($this->font, 9);
        $lines = explode("\n", $odontogram->notes);

        foreach ($lines as $line) {
            $this->ensurePageSpace($page, $y, 175);

            $page->text(15, $y, $line);
            $y += 4.5;
        }
    }

    private function renderSectionHeader(Page $page, string $title, float &$y): void
    {
        $page->setFont($this->boldFont, 11);
        $page->text(15, $y, $title);
        $y += 2;
        $page->line(15, $y, 282, $y)->stroke();
        $y += 6;
    }

    private function ensurePageSpace(Page &$page, float &$y, float $threshold): void
    {
        if ($y > $threshold) {
            $this->doc->addPage(PageFormat::A4, Orientation::LANDSCAPE);
            $page = $this->doc->getCurrentPage();
            $y = 20;
        }
    }
}
