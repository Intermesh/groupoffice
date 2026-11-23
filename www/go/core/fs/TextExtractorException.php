<?php

namespace go\core\fs;

/**
 * A file could not be read by the {@see TextExtractor}
 *
 * The message is meant for the user, like "pdftotext is not installed".
 */
class TextExtractorException extends \RuntimeException
{
}
