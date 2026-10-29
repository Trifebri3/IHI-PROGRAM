<?php

declare(strict_types=1);

namespace App\Services\PdfDecompressor\Parser;

use App\Services\PdfDecompressor\Type\PdfObject;
use App\Services\PdfDecompressor\Type\PdfReference;

/**
 * Resolves an indirect reference to the object it points at.
 *
 * Injected into {@see ObjectParser} so a stream whose /Length is an indirect
 * reference can be read exactly (rather than falling back to scanning for
 * 'endstream'). Implemented by {@see \App\Services\PdfDecompressor\Document\Document}.
 */
interface ObjectResolver
{
    public function resolve(PdfReference $reference): ?PdfObject;
}
