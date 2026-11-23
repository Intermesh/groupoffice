<?php
namespace GO\Base\Util;

use PHPUnit\Framework\TestCase;

/**
 * Word splits text into runs, also inside template tags. The condition of a split <gotpl> tag must still work and the
 * document must stay valid XML.
 */
class TemplateParserTest extends TestCase
{
	/**
	 * The tag as Word saved it in a customer's template (#38372)
	 */
	private const SPLIT_TEMPLATE = '<w:p><w:r><w:t>Pensum: {contact:col_11}</w:t></w:r>'
		. '<w:r w:rsidR="00FD3B90" w:rsidRPr="00336FA2"><w:t>&lt;gotpl if="</w:t></w:r>'
		. '<w:r w:rsidR="00FD3B90"><w:t>contact:col_12</w:t></w:r>'
		. '<w:r w:rsidR="00FD3B90" w:rsidRPr="00336FA2"><w:t xml:space="preserve"> "&gt;</w:t></w:r></w:p>'
		. '<w:p><w:r><w:t xml:space="preserve">Mentor: </w:t></w:r><w:r><w:t>{</w:t></w:r><w:r><w:t>contact:col_1</w:t></w:r>'
		. '<w:r w:rsidR="00C148F1"><w:t>2</w:t></w:r><w:r w:rsidRPr="00336FA2"><w:t>}&lt;/gotpl&gt;</w:t></w:r></w:p>'
		. '<w:p><w:r><w:t>End</w:t></w:r></w:p>';

	private static function parse(string $xml, array $attributes): string
	{
		$result = (new TemplateParser())->parse($xml, $attributes);

		$doc = new \DOMDocument();
		self::assertTrue($doc->loadXML('<w:document xmlns:w="w"><w:body>' . $result . '</w:body></w:document>'), 'Invalid XML: ' . $result);
		return $doc->textContent;
	}

	public function testSplitConditionWithValue(): void
	{
		$this->assertEquals('Pensum: 80%Mentor: Hans MüllerEnd', self::parse(self::SPLIT_TEMPLATE, ['contact:col_11' => '80%', 'contact:col_12' => 'Hans Müller']));
	}

	public function testSplitConditionWithoutValue(): void
	{
		$this->assertEquals('Pensum: 80%End', self::parse(self::SPLIT_TEMPLATE, ['contact:col_11' => '80%']));
	}

	public function testSplitNameAndClosingTag(): void
	{
		$xml = '<w:p><w:r><w:t>&lt;go</w:t></w:r><w:r><w:t>tpl if="x"&gt;Shown&lt;/</w:t></w:r><w:r><w:t>gotpl&gt;</w:t></w:r></w:p>'
			. '<w:p><w:r><w:t>&lt;gotpl if="y"&gt;Hidden&lt;/gotp</w:t></w:r><w:r><w:t>l&gt;</w:t></w:r></w:p>';

		$this->assertEquals('Shown', self::parse($xml, ['x' => '1']));
	}

	public function testTagInOneRun(): void
	{
		$xml = '<w:p><w:r><w:t>&lt;gotpl if="x"&gt;A&lt;/gotpl&gt;&lt;gotpl if="y"&gt;B&lt;/gotpl&gt;</w:t></w:r></w:p>';
		$this->assertEquals('A', self::parse($xml, ['x' => '1']));
	}
}
