<?php
namespace go\core;

/**
 * Class TemplateExpressionEvaluator
 *
 * A class designed to evaluate logical and mathematical expressions
 * in a structured format. It parses expressions based on a well-defined
 * grammar with support for operators such as addition, subtraction,
 * multiplication, division, comparison, and logical expressions.
 * The grammar defines precedence from low to high, ensuring proper
 * evaluation order.
 *
 * Supported Features:
 * - Logical operations (`&&`, `||`, `!`)
 * - Comparison operations (`==`, `!=`, `>=`, `<=`, `>`, `<`)
 * - Arithmetic operations (`+`, `-`, `*`, `/`, `%`)
 * - Parenthetical grouping for modifying precedence
 * - Literals including numbers, strings, booleans (`true`, `false`),
 *   and `null`.
 *
 * Usage of the class requires evaluating a valid expression string,
 * and any invalid tokens or syntax will result in exceptions being
 * thrown during parsing.
 *
 * Exceptions thrown:
 * - \InvalidArgumentException: If unexpected tokens or invalid
 *   syntax are detected in the input.
 * - \DivisionByZeroError: If a division by zero occurs during
 *   evaluation.
 */
class TemplateExpressionEvaluator
{
	private string $input;
	private int $pos;
	private int $len;

	public function evaluate(string $expression): mixed
	{
		$this->input = trim($expression);

		// empty will filter out all falsey strings, like "0" . In mathematical calculations, this will trigger errors.
		if (strlen($this->input) === 0) {
			return false;
		}

		$this->pos = 0;
		$this->len = strlen($this->input);

		$result = $this->parseExpression();
		$this->skipWhitespace();

		if ($this->pos !== $this->len) {
			throw new \InvalidArgumentException(
				"Unexpected token at position {$this->pos}: '" .
				substr($this->input, $this->pos, 10) . "'"
			);
		}

		return $result;
	}

	// ── Grammar (precedence low → high) ──────────────────────────────────────
	//   expression := or_expr
	//   or_expr    := and_expr  ( '||' and_expr )*
	//   and_expr   := not_expr  ( '&&' not_expr )*
	//   not_expr   := '!' not_expr | comparison
	//   comparison := addition  ( ( '==' | '!=' | '>=' | '<=' | '>' | '<' ) addition )?
	//   addition   := multiply  ( ( '+' | '-' ) multiply )*
	//   multiply   := unary     ( ( '*' | '/' | '%' ) unary )*
	//   unary      := '-' unary | primary
	//   primary    := literal | '(' expression ')'
	//   literal    := number | string | 'true' | 'false' | 'null'

	private function parseExpression(): mixed
	{
		return $this->parseOr();
	}

	private function parseOr(): mixed
	{
		$left = $this->parseAnd();
		while ($this->match('||')) {
			$right = $this->parseAnd();
			$left = $left || $right;
		}
		return $left;
	}

	private function parseAnd(): mixed
	{
		$left = $this->parseNot();
		while ($this->match('&&')) {
			$right = $this->parseNot();
			$left = $left && $right;
		}
		return $left;
	}

	private function parseNot(): mixed
	{
		if ($this->match('!')) {
			return !$this->parseNot();
		}
		return $this->parseComparison();
	}

	private function parseComparison(): mixed
	{
		$left = $this->parseAddition();

		foreach (['==', '!=', '>=', '<=', '>', '<'] as $op) {
			if ($this->match($op)) {
				$right = $this->parseAddition();
				return match ($op) {
					'==' => $left == $right,
					'!=' => $left != $right,
					'>=' => $left >= $right,
					'<=' => $left <= $right,
					'>' => $left > $right,
					'<' => $left < $right,
				};
			}
		}

		return $left;
	}

	private function parseAddition(): mixed
	{
		$left = $this->parseMultiply();
		while (true) {
			if ($this->match('+')) {
				$left = $left + $this->parseMultiply();
			} elseif ($this->match('-')) {
				$left = $left - $this->parseMultiply();
			} else {
				break;
			}
		}
		return $left;
	}

	private function parseMultiply(): mixed
	{
		$left = $this->parseUnary();
		while (true) {
			if ($this->match('*')) {
				$left = $left * $this->parseUnary();
			} elseif ($this->match('/')) {
				$divisor = $this->parseUnary();
				if ($divisor == 0) {
					throw new \DivisionByZeroError("Division by zero.");
				}
				$left = $left / $divisor;
			} elseif ($this->match('%')) {
				$left = $left % $this->parseUnary();
			} else {
				break;
			}
		}
		return $left;
	}

	private function parseUnary(): mixed
	{
		if ($this->match('-')) {
			return -$this->parseUnary();
		}
		return $this->parsePrimary();
	}

	private function parsePrimary(): mixed
	{
		$this->skipWhitespace();

		// Grouped expression
		if ($this->match('(')) {
			$value = $this->parseExpression();
			$this->expect(')');
			return $value;
		}

		// String literal
		if ($this->peek() === '"' || $this->peek() === "'") {
			return $this->parseString();
		}

		// Boolean / null literals — must be checked before parseNumber
		if ($this->matchWord('true')) return true;
		if ($this->matchWord('false')) return false;
		if ($this->matchWord('null')) return null;

		// Numeric literal
		if (ctype_digit($this->peek()) || $this->peek() === '.') {
			return $this->parseNumber();
		}

		throw new \InvalidArgumentException(
			"Unexpected character at position {$this->pos}: '" .
			substr($this->input, $this->pos, 10) . "'"
		);
	}

	// ── Terminals ─────────────────────────────────────────────────────────────

	private function parseString(): string
	{
		$quote = $this->input[$this->pos++];
		$str = '';
		while ($this->pos < $this->len && $this->input[$this->pos] !== $quote) {
			if ($this->input[$this->pos] === '\\') {
				$this->pos++;
				$str .= match ($this->input[$this->pos] ?? '') {
					'n' => "\n",
					't' => "\t",
					'\\' => '\\',
					'\'' => "'",
					'"' => '"',
					default => '\\' . ($this->input[$this->pos] ?? ''),
				};
			} else {
				$str .= $this->input[$this->pos];
			}
			$this->pos++;
		}
		$this->expect($quote);
		return $str;
	}

	private function parseNumber(): int|float
	{
		$start = $this->pos;
		while ($this->pos < $this->len &&
			(ctype_digit($this->input[$this->pos]) || $this->input[$this->pos] === '.')) {
			$this->pos++;
		}
		$raw = substr($this->input, $start, $this->pos - $start);
		return str_contains($raw, '.') ? (float)$raw : (int)$raw;
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	private function skipWhitespace(): void
	{
		while ($this->pos < $this->len && ctype_space($this->input[$this->pos])) {
			$this->pos++;
		}
	}

	private function peek(): string
	{
		$this->skipWhitespace();
		return $this->input[$this->pos] ?? '';
	}

	private function match(string $str): bool
	{
		$this->skipWhitespace();
		if (substr($this->input, $this->pos, strlen($str)) !== $str) {
			return false;
		}
		// Don't consume the first char of a longer operator (e.g. '>' when next char is '=')
		$next = $this->input[$this->pos + strlen($str)] ?? '';
		if (strlen($str) === 1 && in_array($str, ['>', '<', '!', '='], true) && $next === '=') {
			return false;
		}
		$this->pos += strlen($str);
		return true;
	}

	private function matchWord(string $word): bool
	{
		$this->skipWhitespace();
		$len = strlen($word);
		$next = $this->input[$this->pos + $len] ?? '';
		if (substr($this->input, $this->pos, $len) === $word &&
			!ctype_alnum($next) && $next !== '_') {
			$this->pos += $len;
			return true;
		}
		return false;
	}

	private function expect(string $char): void
	{
		$this->skipWhitespace();
		if (($this->input[$this->pos] ?? '') !== $char) {
			throw new \InvalidArgumentException(
				"Expected '$char' at position {$this->pos}, got '" .
				($this->input[$this->pos] ?? 'EOF') . "'"
			);
		}
		$this->pos++;
	}
}