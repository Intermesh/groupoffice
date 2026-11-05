<?php
namespace go\modules\community\notes\model;

use go\core\fs\Blob;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

class NoteTest extends TestCase
{
	private NoteBook $noteBook;

	/**
	 * @var string[]
	 */
	private array $blobIds = [];

	protected function setUp(): void
	{
		$this->noteBook = new NoteBook();
		$this->noteBook->name = "Markdown test " . uniqid();
		if(!$this->noteBook->save()) {
			throw new SaveException($this->noteBook);
		}
	}

	protected function tearDown(): void
	{
		NoteBook::delete(['id' => $this->noteBook->id]);

		if(!empty($this->blobIds)) {
			Blob::delete(Blob::find()->where('id', 'IN', $this->blobIds));
		}
	}

	private function createNote(string $content, string $type = Note::TYPE_HTML): Note
	{
		$note = new Note();
		$note->noteBookId = $this->noteBook->id;
		$note->name = "Test";
		$note->content = $content;
		$note->type = $type;
		return $note;
	}

	private function findImageBlobIds(Note $note): array
	{
		return go()->getDbConnection()
			->selectSingleValue('blobId')
			->from('notes_note_image')
			->where('noteId', '=', $note->id)
			->all();
	}

	public function testHtmlIsDefaultType()
	{
		$note = $this->createNote("<p>Hello</p>");
		$note->type = Note::TYPE_HTML;
		$this->assertTrue($note->save());

		$note = Note::findById($note->id);
		$this->assertEquals(Note::TYPE_HTML, $note->type);
		$this->assertEquals(Note::TYPE_HTML, (new Note())->type);
	}

	public function testMarkdownIsNotSanitizedAsHtml()
	{
		$markdown = "# Title\n\n```html\n<script>alert('code example');</script>\n```\n\n1 < 2 && 3 > 2";

		$note = $this->createNote($markdown, Note::TYPE_MARKDOWN);
		$this->assertTrue($note->save(), json_encode($note->getValidationErrors()));

		$note = Note::findById($note->id);
		$this->assertEquals(Note::TYPE_MARKDOWN, $note->type);
		$this->assertEquals($markdown, $note->content);
	}

	public function testHtmlIsSanitized()
	{
		$note = $this->createNote("<p>Hello</p><script>alert('xss');</script>");
		$this->assertTrue($note->save(), json_encode($note->getValidationErrors()));

		$note = Note::findById($note->id);
		$this->assertStringContainsString("<p>Hello</p>", $note->content);
		$this->assertStringNotContainsString("<script", $note->content);
	}

	public function testInvalidTypeIsRejected()
	{
		$note = $this->createNote("Hello", "rtf");
		$this->assertFalse($note->save());
		$this->assertArrayHasKey('type', $note->getValidationErrors());
	}

	public function testTypeOfExistingNoteCantBeChanged()
	{
		$note = $this->createNote("<p>Hello</p>");
		$this->assertTrue($note->save());

		$note = Note::findById($note->id);
		$note->type = Note::TYPE_MARKDOWN;
		$this->assertFalse($note->save());
		$this->assertArrayHasKey('type', $note->getValidationErrors());
	}

	public function testMarkdownImagesAreAttached()
	{
		$blob = Blob::fromString("markdown note image " . uniqid());
		$blob->type = 'image/png';
		$blob->name = 'image.png';
		$blob->staleAt = new DateTime("+1 hour");
		$this->assertTrue($blob->save());
		$this->blobIds[] = $blob->id;

		$missingBlobId = sha1("does not exist " . uniqid());

		$markdown = "Image:\n\n![image.png](" . Blob::url($blob->id) . ")\n\n![missing](" . Blob::url($missingBlobId) . ")";

		$note = $this->createNote($markdown, Note::TYPE_MARKDOWN);
		$this->assertTrue($note->save(), json_encode($note->getValidationErrors()));

		$this->assertEquals([$blob->id], $this->findImageBlobIds($note));
	}
}
