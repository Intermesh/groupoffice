<?php
namespace go\core\util;

class Markdown {

	/**
	 * Takes an array of associative arrays (rows) and prints a markdown table to the CLI, with column widths auto-sized for alignment:
	 *
	 * @example
	 * ```
	 * $rows = [
	 * ['table' => 'core_document', 'refs' => 142],
	 * ['table' => 'core_attachment', 'refs' => 58],
	 * ['table' => 'mail_message', 'refs' => 3021],
	 * ];
	 * ```
	 *
	 * Output:
	 * ```
	 * | table           | refs |
	 * | --------------- | ---- |
	 * | core_document   | 142  |
	 * | core_attachment | 58   |
	 * | mail_message    | 3021 |
	 * ```
	 *
	 * @param array $rows
	 * @return string
	 */
	public static function createTable(array $rows): string {
		if (empty($rows)) {
			return "";
		}

		$str = "";

		$headers = array_keys($rows[0]);

		// Calculate max width per column (header vs all cell values)
		$widths = [];
		foreach ($headers as $h) {
			$widths[$h] = mb_strlen($h);
		}
		foreach ($rows as $row) {
			foreach ($headers as $h) {
				$len = mb_strlen((string)($row[$h] ?? ''));
				if ($len > $widths[$h]) {
					$widths[$h] = $len;
				}
			}
		}

		$pad = fn(string $s, int $w) => $s . str_repeat(' ', $w - mb_strlen($s));

		// Header row
		$str .= '| ' . implode(' | ', array_map(fn($h) => $pad($h, $widths[$h]), $headers)) . " |\n";

		// Separator row
		$str .= '| ' . implode(' | ', array_map(fn($h) => str_repeat('-', $widths[$h]), $headers)) . " |\n";

		// Data rows
		foreach ($rows as $row) {
			$str .= '| ' . implode(' | ', array_map(
					fn($h) => $pad((string)($row[$h] ?? ''), $widths[$h]),
					$headers
				)) . " |\n";
		}

		return $str;
	}
}