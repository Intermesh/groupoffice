<?php

namespace go\modules\community\davclient\model;

use PHPUnit\Framework\TestCase;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

class SyncTest extends TestCase {

//	const HOST = 'go.localhost/dav/';
//	const USER = 'admin';
//	const PASS = 'admin1';
//
//	public static function setUpBeforeClass(): void
//	{
////		$success = \go\modules\community\davclient\Module::get()->install();
////		if(!$success) {
////			throw new \Exception("Could not install davclient module");
////		}
//	}
//
//	private function getSynchronizer(): DavSynchronizer {
//		$name = hash("crc32b", self::HOST.self::USER.self::PASS);
//		$davAccount = DavAccount::find()->where(['name' => $name])->single();
//		if(!$davAccount) {
//
//			$davAccount = new DavAccount();
//			$davAccount->name = $name;
//			$davAccount->connectionDsn = "caldav:host=".self::HOST;
//			$davAccount->username = self::USER;
//			$davAccount->password = self::PASS;
//
//			$this->assertTrue($davAccount->save(), 'Could not save dav accounts');
//		}
//		return new DavSynchronizer($davAccount);
//  }
//
//  public function testSetup() {
//	  \go\modules\community\davclient\Module::get()->install();
//	  $s = $this->getSynchronizer();
//	  $data = $s->serviceDiscovery();
//	  $this->assertEquals($data['baseUri'], self::HOST.'/.well-known');
//	  $this->assertEquals($data['principalHref'], self::HOST.'/principals');
//	  $this->assertEquals($data['homeSetUri'], self::HOST.'/calendars');
//
//	  // will not check $data['collections']
//  }
//
//  public function testHomeDir() {
//	  	$syncer = $this->getSynchronizer();
//
//  }
//
//  public function testSyncCalendar() {
//
//	  $syncer = $this->getSynchronizer();;
//
//  }
//
//  public function testChanges() {
//    $contact = Contact::find()->single();
//
//    $success = Contact::delete(['id' => $contact->id]);
//
//    $this->assertEquals(true, $success);
//  }
//
//	public function testResync() {
//
//	}
//
//	public function testDeleteAccount() {
//
//	}
}