<?php

namespace go\core\util;

use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;
use Throwable;

/**
 * Read doc blocks of classes, properties and methods
 */
class DocBlock
{
	private static ?PhpdocParser $parser = null;

	/**
	 * The description in a doc block
	 *
	 * This is the summary. Without a summary it's the text after the type of "@var", because many properties are
	 * documented like "@var string 'needs-action', 'accepted' or 'declined'".
	 *
	 * @param string|false $docComment The doc block, as returned by a reflection's getDocComment()
	 * @return string|null Null when there's no description
	 */
	public static function description(string|false $docComment): ?string
	{
		if (!$docComment) {
			return null;
		}

		// A doc block that starts with a tag has no summary. The parser would take text of the tag for it.
		$firstLine = trim(preg_replace('~^\s*/\*\*~', '', ltrim($docComment)));
		$firstLine = trim(preg_replace('~^\*\s?~m', '', $firstLine));
		if (str_starts_with($firstLine, '@')) {
			return self::varDescription($docComment);
		}

		try {
			self::$parser ??= new PhpdocParser(PhpDocumentor::tags()->with([new Summery()]));
			$meta = self::$parser->parse($docComment);
		} catch (Throwable) {
			$meta = [];
		}

		if (!isset($meta['description'])) {
			return self::varDescription($docComment);
		}

		// The parser leaves the end of a doc block on one line, like "/** Name of the note */"
		$description = trim(preg_replace('~\s*\*/\s*$~', '', $meta['description']));
		return $description === '' ? null : $description;
	}

	/**
	 * Description after the type of "@var"
	 */
	private static function varDescription(string $docComment): ?string
	{
		// The type may contain spaces in generics like array<string, int>. An optional variable name follows it.
		if (!preg_match('/@var\s+(?:[^\s<]*<[^>]*>[^\s]*|\S+)(?:\s+\$\w+)?[ \t]+([^@]*)/s', $docComment, $matches)) {
			return null;
		}

		$lines = array_map(fn($line) => trim(preg_replace('~^\s*\*/?\s?|\*/\s*$~', '', $line)), explode("\n", $matches[1]));
		$description = trim(preg_replace('/\s+/', ' ', implode(' ', $lines)));

		return $description === '' ? null : $description;
	}
}
