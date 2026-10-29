<?php

declare(strict_types=1);

namespace App\Services\PdfDecompressor\Exception;

/**
 * Thrown when the tokenizer or object parser encounters malformed input.
 */
class ParserException extends PdfDecompressorException
{
}
