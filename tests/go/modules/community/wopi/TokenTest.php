<?php

namespace go\modules\community\wopi;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\wopi\model\Token;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * A WOPI token may only be used for the file it was issued for, and must stop working when the user is disabled or
 * changes the password.
 */
class TokenTest extends TestCase
{
	private $authState;
	private User $user;
	private Folder $folder;
	private File $file1;
	private File $file2;
	private int $serviceId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();

		$name = uniqid('wopi');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		go()->setAuthState(new TemporaryState($this->user->id));
		\GO::session()->setCurrentUser($this->user->id);

		$this->folder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->user->id));
		$this->file1 = $this->createFile('one.docx');
		$this->file2 = $this->createFile('two.docx');

		// Saving a Service runs WOPI discovery over HTTP, so insert the row directly
		go()->getDbConnection()->insert('wopi_service', [
			'name' => 'Test',
			'url' => 'https://wopi.example.com',
			'aclId' => $this->folder->acl_id,
			'type' => uniqid('test')
		])->execute();
		$this->serviceId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		go()->getDbConnection()->delete('wopi_service', ['id' => $this->serviceId])->execute();
		$this->folder->delete(true);
		User::delete(['id' => $this->user->id]);
	}

	private function createFile(string $name): File
	{
		file_put_contents($this->folder->fsFolder->path() . '/' . $name, 'test');
		$file = $this->folder->addFile($name, true);
		$this->assertInstanceOf(File::class, $file);
		return $file;
	}

	private function token(File $file): Token
	{
		return Token::get($this->serviceId, $this->user->id, $file->id);
	}

	public function testTokenIsBoundToFile(): void
	{
		$token1 = $this->token($this->file1);
		$token2 = $this->token($this->file2);

		$this->assertNotEquals($token1->getToken(), $token2->getToken());
		$this->assertEquals($token1->getToken(), $this->token($this->file1)->getToken(), 'Token should be reused for the same file');

		$this->assertNotNull(Token::findValid($token1->getToken(), $this->file1->id));
		$this->assertNull(Token::findValid($token1->getToken(), $this->file2->id), 'Token of file 1 must not give access to file 2');
		$this->assertNull(Token::findValid('', $this->file1->id));
		$this->assertNull(Token::findValid('invalid', $this->file1->id));
	}

	public function testExpiredTokenIsRejected(): void
	{
		$token = $this->token($this->file1);
		go()->getDbConnection()->update('wopi_token', ['expiresAt' => new \go\core\util\DateTime('-1 day')], ['id' => $token->id()])->execute();

		$this->assertNull(Token::findValid($token->getToken(), $this->file1->id));

		$this->assertTrue(Token::collectGarbage());
		$this->assertNull(go()->getDbConnection()->selectSingleValue('id')->from('wopi_token')->where('id', '=', $token->id())->single());
	}

	public function testDisabledUserIsRejected(): void
	{
		$token = $this->token($this->file1);

		// Disable without the save listener, to prove findValid checks the user itself
		go()->getDbConnection()->update('core_user', ['enabled' => false], ['id' => $this->user->id])->execute();
		$this->assertNull(Token::findValid($token->getToken(), $this->file1->id));
	}

	public function testDisablingUserRevokesTokens(): void
	{
		$token = $this->token($this->file1);

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$user = User::findById($this->user->id);
		$user->enabled = false;
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		$this->assertNull(go()->getDbConnection()->selectSingleValue('id')->from('wopi_token')->where('id', '=', $token->id())->single());
	}

	public function testPasswordChangeRevokesTokens(): void
	{
		$token = $this->token($this->file1);

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$user = User::findById($this->user->id);
		$user->setPassword('An0ther!!Passw0rd');
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		$this->assertNull(go()->getDbConnection()->selectSingleValue('id')->from('wopi_token')->where('id', '=', $token->id())->single());
	}
}
