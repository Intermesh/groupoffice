<?php

namespace go\modules\community\email\model;

use go\modules\community\email\Module;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase {

	public function testFromFilter() {
		$module = Module::get();
		if (!$module->isInstalled()) {
			$this->assertNotFalse($module->install(), "Could not install email module");
		}

		$db = go()->getDbConnection();
		$aclId = $db->selectSingleValue('id')->from('core_acl')->orderBy(['id' => 'ASC'])->single();
		$db->insert('email_account', ['name' => 'filter', 'email' => 'filter@intermesh.localhost', 'mtaDsn' => '',
			'mdaDsn' => '', 'mdaCapabilities' => '[]', 'createdAt' => '2026-10-01 00:00:00', 'aclId' => $aclId])->execute();
		$accountId = (int)$db->getPDO()->lastInsertId();
		$db->insert('email_thread', ['subjectHash' => sha1('x'), 'accountId' => $accountId])->execute();
		$threadId = (int)$db->getPDO()->lastInsertId();

		$ids = [];
		foreach (['alice@example.com' => 'Alice', 'bob@example.com' => 'Bob'] as $email => $name) {
			$db->insert('email_email', ['threadId' => $threadId, 'subject' => 'Hi', 'accountId' => $accountId])->execute();
			$ids[$email] = (int)$db->getPDO()->lastInsertId();
			$db->insert('email_address', ['fk' => $ids[$email], 'type' => 'from', 'email' => $email, 'name' => $name])->execute();
			// a recipient must not match
			$db->insert('email_address', ['fk' => $ids[$email], 'type' => 'to', 'email' => 'carol@example.com', 'name' => 'Carol'])->execute();
		}

		$found = fn($value) => array_map('intval', Email::find()->selectSingleValue('e.id')
			->filter(['accountId' => $accountId, 'from' => $value])->all());

		$this->assertSame([$ids['alice@example.com']], $found('alice@example.com'));
		$this->assertSame([$ids['bob@example.com']], $found('Bob'));
		$this->assertSame([], $found('carol@example.com'));

		// the filter can be combined with itself
		$both = array_map('intval', Email::find()->selectSingleValue('e.id')
			->filter(['accountId' => $accountId, 'operator' => 'OR', 'conditions' => [['from' => 'alice'], ['from' => 'bob']]])
			->orderBy(['e.id' => 'ASC'])->all());
		$this->assertSame(array_values($ids), $both);
	}
}
