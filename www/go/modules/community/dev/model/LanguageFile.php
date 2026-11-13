<?php

namespace go\modules\community\dev\model;

/**
 * Adds and changes translations in a language file without reformatting it
 *
 * Language files return an array: "return array ( ... );" or "return [ ... ];". Existing entries keep their place and
 * style; only their value changes. New entries are added at the end with the indentation of the file. A file is only
 * rewritten as a whole when it doesn't exist yet.
 *
 * @example
 * ```
 * $file = new LanguageFile('/path/to/language/it.php');
 * $file->set(['Save' => 'Salva']);
 * $file->save();
 * ```
 */
class LanguageFile
{
	private string $content;
	private bool $exists;

	/**
	 * @var string[] Messages about entries that were skipped
	 */
	public array $warnings = [];

	public function __construct(private readonly string $path)
	{
		$this->exists = is_file($path);
		$this->content = $this->exists ? file_get_contents($path) : "<?php\nreturn array (\n);\n";
	}

	/**
	 * Check that a translation has the same placeholders, HTML tags and line breaks as the English text
	 *
	 * @param string $en
	 * @param string $translation
	 * @return bool
	 */
	public static function hasSameTokens(string $en, string $translation): bool
	{
		$tokens = function (string $s): array {
			preg_match_all('/\{\{[^}]*\}\}|\{[\w.]+\}|%\d*\$?[sdf]|<[^>]+>|\\\\n|\n|&[a-z]+;/', $s, $m);
			sort($m[0]);
			return $m[0];
		};
		return $tokens($en) === $tokens($translation);
	}

	private static function quote(string $s): string
	{
		return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $s) . "'";
	}

	/**
	 * Add or change translations
	 *
	 * @param array<string, string|array> $translations English text => translation. An array value is for entries like
	 *  the names of months; those can only be added, not changed.
	 */
	public function set(array $translations): void
	{
		$new = '';
		foreach ($translations as $key => $value) {
			$key = (string) $key;
			// Only top level entries: nested arrays, like the country names, can have the same keys
			$indent = preg_quote($this->indent(), '/');
			$existing = '/^(' . $indent . preg_quote(self::quote($key), '/') . '\s*=>\s*)\'(?:\\\\.|[^\'\\\\])*\'/m';

			if (is_array($value)) {
				if (preg_match('/^' . $indent . preg_quote(self::quote($key), '/') . '\s*=>/m', $this->content, $m, PREG_OFFSET_CAPTURE)) {
					$this->addArrayItems($key, $m[0][1] + strlen($m[0][0]), $value);
				} else {
					$new .= $this->indent() . self::quote($key) . " => array (\n";
					foreach ($value as $k => $v) {
						$new .= $this->indent() . $this->indent() . (is_int($k) ? $k : self::quote((string) $k)) . ' => ' . self::quote((string) $v) . ",\n";
					}
					$new .= $this->indent() . "),\n";
				}
				continue;
			}

			if (preg_match($existing, $this->content)) {
				$this->content = preg_replace_callback($existing, fn($m) => $m[1] . self::quote($value), $this->content, 1);
			} else {
				$new .= $this->indent() . self::quote($key) . ' => ' . self::quote($value) . ",\n";
			}
		}

		if ($new !== '') {
			$this->append($new);
		}
	}

	/**
	 * Add the missing items to an existing array entry, like the countries. Existing items aren't changed.
	 *
	 * @param string $key The entry, for the warnings
	 * @param int $offset Position in the content after "'key' =>"
	 * @param array<string|int, string> $items
	 */
	private function addArrayItems(string $key, int $offset, array $items): void
	{
		// PHP's tokenizer finds the closing bracket, also when items contain brackets or quotes
		$tokens = token_get_all("<?php " . substr($this->content, $offset));
		$pos = $offset - strlen("<?php ");
		$depth = 0;
		$existing = [];
		$lastString = null;
		$close = null;
		foreach ($tokens as $token) {
			$text = is_array($token) ? $token[1] : $token;
			if ($text === '(' || $text === '[') {
				$depth++;
			} else if ($text === ')' || $text === ']') {
				if (--$depth === 0) {
					$close = $pos;
					break;
				}
			} else if ($depth === 1 && is_array($token) && in_array($token[0], [T_CONSTANT_ENCAPSED_STRING, T_LNUMBER], true)) {
				$lastString = $token[0] === T_LNUMBER ? (int) $text : stripcslashes(substr($text, 1, -1));
			} else if ($depth === 1 && is_array($token) && $token[0] === T_DOUBLE_ARROW && isset($lastString)) {
				$existing[(string) $lastString] = true;
			}
			$pos += strlen($text);
		}

		if (!isset($close)) {
			$this->warnings[] = "Not changed, could not find the end of the array: " . $key;
			return;
		}

		$new = array_filter($items, fn($k) => !isset($existing[(string) $k]), ARRAY_FILTER_USE_KEY);
		if (empty($new)) {
			return;
		}

		// Use the indentation of the existing items
		$body = substr($this->content, $offset, $close - $offset);
		$itemIndent = preg_match('/\n([ \t]+)[\'"\d]/', $body, $m) ? $m[1] : $this->indent() . $this->indent();
		$closeIndent = preg_match('/\n([ \t]*)$/', $body, $m) ? $m[1] : $this->indent();

		$lines = '';
		foreach ($new as $k => $v) {
			$lines .= $itemIndent . (is_int($k) ? $k : self::quote((string) $k)) . ' => ' . self::quote((string) $v) . ",\n";
		}

		$head = rtrim(substr($this->content, 0, $close));
		if (!str_ends_with($head, ',') && !str_ends_with($head, '(') && !str_ends_with($head, '[')) {
			$head .= ',';
		}
		$this->content = $head . "\n" . $lines . $closeIndent . substr($this->content, $close);
	}

	/**
	 * The indentation of the top level entries: the first entry after "return array (" or "return ["
	 */
	private function indent(): string
	{
		return preg_match('/return\s*(?:array\s*\(|\[)\s*?\n([ \t]+)[\'"]/', $this->content, $m) ? $m[1] : '  ';
	}

	private function append(string $lines): void
	{
		if (!preg_match('/\n?\s*([)\]])\s*;\s*$/', $this->content, $m, PREG_OFFSET_CAPTURE)) {
			throw new \LogicException("Unknown format of " . $this->path . ". It must end with ); or ];");
		}
		$close = $m[1][0];
		$head = rtrim(substr($this->content, 0, $m[0][1]));
		// The last entry needs a comma before new ones are added
		if (!str_ends_with($head, ',') && !str_ends_with($head, '(') && !str_ends_with($head, '[')) {
			$head .= ',';
		}
		$this->content = $head . "\n" . $lines . $close . ";\n";
	}

	/**
	 * Write the file
	 *
	 * @return bool
	 */
	public function save(): bool
	{
		if (!$this->exists && !is_dir(dirname($this->path)) && !mkdir(dirname($this->path), 0775, true)) {
			return false;
		}
		return file_put_contents($this->path, $this->content) !== false;
	}
}
