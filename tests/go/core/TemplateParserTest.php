<?php
namespace go\core;

use go\core\model\Link;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\EmailAddress;

class TemplateParserTest extends \PHPUnit\Framework\TestCase
{
	private function getAddressBook() {
		$addressBook = AddressBook::find()->where(['name' => 'Test'])->single();
		if(!$addressBook) {
			$addressBook = new AddressBook();
			$addressBook->name = "Test";
			$success = $addressBook->save();

			$this->assertEquals(true, $success);
		}
		return $addressBook;
	}

	public function testLinks()
	{
		$addressBook = $this->getAddressBook();

		$contact1 = new Contact();
		$contact1->addressBookId = $addressBook->id;
		$contact1->firstName = "John";
		$contact1->lastName = "Doe";

		$contact1->addresses[0] = $a = new Address($contact1);

		$a->type = Address::TYPE_POSTAL;
		$a->address =	"Street 1";
		$a->city = "Den Bosch";
		$a->zipCode = "5222 AE";
		$a->countryCode = "NL";


		$contact1->emailAddresses[] = (new EmailAddress($contact1))
			->setValues(["type" => EmailAddress::TYPE_WORK, 'email' => 'work@intermesh.localhost']);

		$contact1->emailAddresses[] = (new EmailAddress($contact1))
			->setValues(["type" => EmailAddress::TYPE_HOME, 'email' => 'home@intermesh.localhost']);

		$contact1->emailAddresses[] = (new EmailAddress($contact1))
			->setValues(["type" => EmailAddress::TYPE_HOME, 'email' => 'aaa@intermesh.localhost']);

		$success = $contact1->save();
		$this->assertEquals(true, $success);


		$contact2 = new Contact();
		$contact2->addressBookId = $addressBook->id;
		$contact2->firstName = "Linda";
		$contact2->lastName = "Smith";
		$success = $contact2->save();
		$this->assertEquals(true, $success);

		Link::create($contact1, $contact2);

		$tplParser = new TemplateParser();
		$tplParser->addModel('contact', $contact1);

		$tpl = '[assign firstContactLink = contact | links:Contact | first]{{firstContactLink.name}}';

		$str = $tplParser->parse($tpl);

		$this->assertEquals($contact2->name, $str);

		$tpl = '[assign address = contact.addresses | filter:type:"postal" | first]{{address.zipCode}}';

		$str = $tplParser->parse($tpl);

		$this->assertEquals($a->zipCode, $str);

		$tpl = '[assign address1 = contact.addresses | filter:type:"postal" | first]{{address1.zipCode}}[assign address = contact]{{address.addresses[0].zipCode}}';

		$str = $tplParser->parse($tpl);

		$this->assertEquals($a->zipCode.$a->zipCode, $str);


		$tpl = '{{contact.addresses |  filter:type:"postal" | first | prop:"zipCode"}}';
		$zipCode = $tplParser->parse($tpl);
		$this->assertEquals($a->zipCode, $zipCode);

		$tpl = '{{contact.addresses |  filter:type:"notexisting" | first | prop:"zipCode"}}';
		$notexistingType = $tplParser->parse($tpl);
		$this->assertEquals(null, $notexistingType);

		$tpl = '{{contact.addresses |  filter:type:"postal" | first | prop:"notexisting"}}';
		$notexistingProp = $tplParser->parse($tpl);
		$this->assertEquals(null, $notexistingProp);


		$tpl =  '{{contact.id | Entity:Contact | prop:emailAddresses | first | prop:email}}';
		$firstEmail = $tplParser->parse($tpl);
		$this->assertEquals($contact1->emailAddresses[0]->email, $firstEmail);


		$tpl =  '{{contact.id | Entity:Contact | prop:emailAddresses | sort:email | first | prop:email}}';
		$firstSortedEmail = $tplParser->parse($tpl);
		$this->assertEquals($contact1->emailAddresses[2]->email, $firstSortedEmail);

		$tpl =  '{{contact.id | Entity:Contact | prop:emailAddresses | rsort:type:home | first | prop:type}}';
		$home = $tplParser->parse($tpl);
		$this->assertEquals(EmailAddress::TYPE_HOME, $home);




	}


	public function testIf() {

		$tplParser = new TemplateParser();
		$tplParser->addModel('contact', ['firstName' => 'Linda', 'lastName' => 'Smith']);

		$tpl = '[if {{contact.firstName}} == "Linda"]yes[else]no[/if]';

		$if = $tplParser->parse($tpl);
		$this->assertEquals("yes", $if);


		$tpl = '[if {{contact.firstName}} == "Linda" && {{contact.lastName}} == "Smith"]yes[else]no[/if]';

		$if = $tplParser->parse($tpl);
		$this->assertEquals("yes", $if);

		$tpl = '[if {{contact.firstName}} == "not" && {{contact.lastName}} == "Smith"]yes[else]no[/if]';

		$if = $tplParser->parse($tpl);
		$this->assertEquals("no", $if);

		$tpl = '[if !{{contact.firstName}} || !{{contact.lastName2}}]yes[else]no[/if]';

		$if = $tplParser->parse($tpl);
		$this->assertEquals("yes", $if);

		$tplParser->parse('[assign foo = "bar"]');
		$tpl = '[if {{foo}} != "bar"]no[else]yes[/if]';
		$if = $tplParser->parse($tpl);
		$this->assertEquals("yes", $if);
	}

	public function testMath() {

		$tplParser = new TemplateParser();
		$tplParser->addModel('mathVar1', 5);
		$tplParser->addModel('mathVar2', 2);

		$tplParser->parse('[assign mathVar2 = 2]');

		$tpl = '[assign sum = ((5*2) + 15) / ({{mathVar1}} * {{mathVar2}})]{{sum}}';

		$result = $tplParser->parse($tpl);
		$this->assertEquals("2.5", $result);

		$result = $tplParser->parse('[if {{sum}} > 2]yes[/if]');
		$this->assertEquals("yes", $result);

		$result = $tplParser->parse('[if {{sum}} &gt; 2]yes[/if]');
		$this->assertEquals("yes", $result);

		$result = $tplParser->parse('[if {{sum}} >= 2.5]yes[/if]');
		$this->assertEquals("yes", $result);

		$result = $tplParser->parse('[if {{sum}} > 2.5]yes[/if]');
		$this->assertEquals("", $result);
	}



	public function testSalutation() {
		$tpl = 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}=="M"]Mr.[else]Ms.[/if][/if][/if] {{contact.lastName}}';


		$tplParser = new TemplateParser();

		$contact = new Contact();
		$contact->gender = "M";
		$contact->lastName = "Smith";

		$contact->firstName = "John";
		$tplParser->addModel('contact', $contact);

		$result = $tplParser->parse($tpl);
		$this->assertEquals("Dear Mr. Smith", $result);

		$contact->gender = "F";
		$result = $tplParser->parse($tpl);
		$this->assertEquals("Dear Ms. Smith", $result);

		$contact->prefixes = "Dr.";
		$result = $tplParser->parse($tpl);
		$this->assertEquals("Dear Dr. Smith", $result);
	}


	public function testLiteral() {

		$str = <<<ID
<!--StartFragment-->

<p class="MsoNormal"><span style="mso-ansi-language:#2000">Podia RAI EMC3<o:p></o:p></span></p>

<p class="MsoNormal"><span style="mso-ansi-language:#2000"><o:p>&nbsp;</o:p></span></p>

<p class="MsoNormal"><span style="mso-ansi-language:#2000">RGBWW ledstrip met DMX
drivers (18W/m RGB+2700K)<o:p></o:p></span></p>

<p class="MsoNormal"><span style="mso-ansi-language:#2000"><o:p>&nbsp;</o:p></span></p>

<p class="MsoNormal"><span style="mso-ansi-language:#2000">Sponning platen worden
op het podium geschroefd!<o:p></o:p></span></p>

<p class="MsoNormal"><span style="mso-ansi-language:#2000"><o:p>&nbsp;</o:p></span></p>

<p class="MsoNormal"><b><span style="mso-ansi-language:#2000">Ronde tredes</span></b><span style="mso-ansi-language:#2000"><o:p></o:p></span></p>

<ul style="margin-top:0cm" type="disc">
 <li class="MsoNormal" style="mso-list:l4 level1 lfo1;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">4x 2x10m led<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l4 level1 lfo1;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">8x 3m sponning plaat (24m, 10 stuks)<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l4 level1 lfo1;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">4x 8 ronde segmenten met sponning (1.25m
     lang; 32 stuks) (ong 40m lengte)<o:p></o:p></span></li>
</ul>

<p class="MsoNormal"><span style="mso-ansi-language:#2000">.<o:p></o:p></span></p>

<p class="MsoNormal"><b><span style="mso-ansi-language:#2000">Bovenrand podium</span></b><span style="mso-ansi-language:#2000"><o:p></o:p></span></p>

<ul style="margin-top:0cm" type="disc">
 <li class="MsoNormal" style="mso-list:l3 level1 lfo2;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">22m led<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l3 level1 lfo2;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">22m sponning plaat (10 stuks)<o:p></o:p></span></li>
</ul>

<p class="MsoNormal"><span style="mso-ansi-language:#2000">Totaal led meters: 86m<o:p></o:p></span></p>

<p class="MsoNormal"><span style="mso-ansi-language:#2000">.<o:p></o:p></span></p>

<p class="MsoNormal"><b><span style="mso-ansi-language:#2000">Afwerking</span></b><span style="mso-ansi-language:#2000"><o:p></o:p></span></p>

<ul style="margin-top:0cm" type="disc">
 <li class="MsoNormal" style="mso-list:l0 level1 lfo3;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">63m met&nbsp;195mm&nbsp;hoogte;<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l0 level1 lfo3;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">27x mdf zwart afdekplaat 2400x195mm,
     vastgetacked aan de sponningplaten<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l0 level1 lfo3;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">12m voorkant podium 1000mm hoog, af te
     rokken (flanel geen onderdeel van de offerte)<o:p></o:p></span></li>
</ul>

<p class="MsoNormal"><span style="mso-ansi-language:#2000">.<o:p></o:p></span></p>

<p class="MsoNormal"><b><span style="mso-ansi-language:#2000">Podium delen</span></b><span style="mso-ansi-language:#2000"><o:p></o:p></span></p>

<ul style="margin-top:0cm" type="disc">
 <li class="MsoNormal" style="mso-list:l2 level1 lfo4;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">4 delen van 22mm Hardwood met frame en
     voorzien poten voor 1000mm dekhoogte<o:p></o:p></span></li>
</ul>

<p class="MsoNormal"><span style="mso-ansi-language:#2000"><o:p>&nbsp;</o:p></span></p>

<p class="MsoNormal"><b><span style="mso-ansi-language:#2000">Dek halve cirkel</span></b><span style="mso-ansi-language:#2000"><o:p></o:p></span></p>

<ul style="margin-top:0cm" type="disc">
 <li class="MsoNormal" style="mso-list:l5 level1 lfo5;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">7000mm diameter (halve cirkel) uit 5 delen<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l5 level1 lfo5;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">uit 21mm hardwood<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l5 level1 lfo5;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">pootjes op 200mm<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l5 level1 lfo5;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">diepte 500+30mm sponning<o:p></o:p></span></li>
</ul>

<p class="MsoNormal"><span style="mso-ansi-language:#2000">.<o:p></o:p></span></p>

<p class="MsoNormal"><b><span style="mso-ansi-language:#2000">Installatie op
locatie</span></b><span style="mso-ansi-language:#2000"><o:p></o:p></span></p>

<ul style="margin-top:0cm" type="disc">
 <li class="MsoNormal" style="mso-list:l1 level1 lfo6;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">Transport met bus&nbsp;Maacken&nbsp;Inhuur<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l1 level1 lfo6;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">2 pax op 30 mei vanaf 12:00<o:p></o:p></span></li>
 <li class="MsoNormal" style="mso-list:l1 level1 lfo6;tab-stops:list 36.0pt"><span style="mso-ansi-language:#2000">1 pax op 31 mei vanaf 8:00-12:00<o:p></o:p></span></li>
</ul>

<p class="MsoNormal"><span lang="en-NL"><o:p>&nbsp;</o:p></span></p>

<!--EndFragment--><br>
ID;


		$tplParser = new TemplateParser();
		$tplParser->addModel('literal', $str);

		$tpl = '[if {{literal}}]{{literal}}[/if]';
		$result = $tplParser->parse($tpl);
		$this->assertEquals($str, $result);

	}


	public function testAssign() {
		$tplParser = new TemplateParser();

		$this->assertEquals("bar", $tplParser->parse('[assign foo = "bar"]{{foo}}'));

		$this->assertEquals("3", $tplParser->parse('[assign foo = 3]{{foo}}'));

		$this->assertEquals("3", $tplParser->parse('[assign foo = 1 + 2]{{foo}}'));

		$this->assertEquals("5", $tplParser->parse('[assign bar = {{foo}} + 2]{{bar}}'));
	}


}