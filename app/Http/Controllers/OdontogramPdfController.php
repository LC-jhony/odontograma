<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Odontogram\OdontogramPdfExport;
use App\Models\Odontogram;
use Illuminate\Http\Response;

final class OdontogramPdfController
{
    public function show(Odontogram $odontogram): Response
    {
        $bytes = (new OdontogramPdfExport)->generate($odontogram);

        $filename = "odontograma-{$odontogram->patient->document_number}-".now()->format('Y-m-d').'.pdf';

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
