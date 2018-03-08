<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of {product_name}. You should have received a copy of the
 * {product_name} license along with {product_name}. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: sv.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('common'));

$lang['common']['about']= 'Version: {version}<br />
<br />
Copyright (c) 2003-{current_year}, {company_name}<br />
All rights reserved.<br />
Det här programmet omfattas av copyright-lagar och {product_name}-licensen.<br />
';

$lang['common']['totals']='Totalt';
$lang['common']['printPage']='Sida %s av %s';

$lang['common']['htmldirection']= 'ltr';

$lang['common']['quotaExceeded']= 'Du har inget diskutrymme kvar. Ta bort några filer eller kontakta supporten för att öka din quota';
$lang['common']['errorsInForm'] = 'Det finns fel i formuläret. Rätta till dem och försök igen.';

$lang['common']['moduleRequired']= 'Modulen %s krävs för denna funktion';

$lang['common']['loadingCore']= 'Laddar grundsystemet';
$lang['common']['loadingLogin'] = 'Laddar inloggningsdialog';
$lang['common']['renderInterface']= 'Renderar gränssnitt';
$lang['common']['loadingModules']='Laddar moduler';
$lang['common']['loadingModule'] = 'Laddar modul';

$lang['common']['loggedInAs'] = "Inloggad som ";
$lang['common']['search']= 'Sök';
$lang['common']['settings']= 'Inställningar';
$lang['common']['adminMenu']= 'Adminmeny';
$lang['common']['startMenu']= 'Huvudmeny';
$lang['common']['help']= 'Hjälp';
$lang['common']['logout']= 'Logga ut';
$lang['common']['badLogin'] = 'Fel användarnamn eller lösenord';
$lang['common']['badPassword'] = 'Du har angett det nuvarande lösenordet felaktigt';

$lang['common']['passwordMatchError']= 'Lösenorden matchade inte';
$lang['common']['accessDenied']= 'Åtkomst nekad';
$lang['common']['saveError']= 'Fel vid sparande av data';
$lang['common']['deleteError']= 'Fel vid radering av data';
$lang['common']['selectError']= 'Fel vid läsning av data';
$lang['common']['missingField'] = 'Du har inte fyllt i alla obligatoriska fält.';
$lang['common']['invalidEmailError']='E-postadressen var ogiltig';
$lang['common']['invalidDateError']='Du angav ett ogiltigt datum';
$lang['common']['noFileUploaded']= 'Ingen fil mottogs';
$lang['common']['error']='Fel';
$lang['common']['fileCreateError']='Kunde inte skapa filen';
$lang['common']['illegalCharsError']='Namnet innehöll ett av följande otillåtna tecken %s';

$lang['common']['salutation']= 'Hälsning';
$lang['common']['firstName'] = 'Förnamn';
$lang['common']['lastName'] = 'Efternamn';
$lang['common']['middleName'] = 'Mellannamn';
$lang['common']['sirMadam']['M'] = 'herr';
$lang['common']['sirMadam']['F'] = 'fru';
$lang['common']['initials'] = 'Initialer';
$lang['common']['sex'] = 'Kön';
$lang['common']['birthday'] = 'Födelsedag';
$lang['common']['sexes']['M'] = 'Man';
$lang['common']['sexes']['F'] = 'Kvinna';
$lang['common']['title'] = 'Titel';
$lang['common']['addressNo'] = 'Adress';
$lang['common']['workAddressNo'] = 'Husnummer (arbete)';
$lang['common']['postAddress'] = 'Adress (post)';
$lang['common']['postAddressNo'] = 'Gatunummer (post)';
$lang['common']['postCity'] = 'Stad (post)';
$lang['common']['postState'] = 'Län/Stat (post)';
$lang['common']['postCountry'] = 'Land (post)';
$lang['common']['postZip'] = 'Postnummer (post)';
$lang['common']['visitAddress'] = 'Besöksadress';
$lang['common']['postAddressHead'] = 'Postadress';
$lang['common']['name'] = 'Namn';
$lang['common']['name2'] = 'Namn 2';
$lang['common']['user'] = 'Användare';
$lang['common']['username'] = 'Användarnamn';
$lang['common']['password'] = 'Lösenord';
$lang['common']['authcode'] = 'Säkerhetskod';
$lang['common']['country'] = 'Land';
$lang['common']['address_format']='Adressformat';
$lang['common']['state'] = 'Län/Stat';
$lang['common']['city'] = 'Stad';
$lang['common']['zip'] = 'Postnummer';
$lang['common']['address'] = 'Adress';
$lang['common']['email'] = 'E-post';
$lang['common']['phone'] = 'Telefon';
$lang['common']['workphone'] = 'Telefon (arbete)';
$lang['common']['cellular'] = 'Mobil';
$lang['common']['company'] = 'Företag';
$lang['common']['department'] = 'Avdelning';
$lang['common']['function'] = 'Funktion';
$lang['common']['question'] = 'Hemlig fråga';
$lang['common']['answer'] = 'Svar';
$lang['common']['fax'] = 'Fax';
$lang['common']['workFax'] = 'Fax (arbete)';
$lang['common']['homepage'] = 'Hemsida';
$lang['common']['workAddress'] = 'Adress (arbete)';
$lang['common']['workZip'] = 'Postnummer (arbete)';
$lang['common']['workCountry'] = 'Land (arbete)';
$lang['common']['workState'] = 'Län/Stat (arbete)';
$lang['common']['workCity'] = 'Stad (arbete)';
$lang['common']['today'] = 'Idag';
$lang['common']['tomorrow'] = 'Imorgon';

$lang['common']['SearchAll'] = 'Alla fält';
$lang['common']['total'] = 'totalt';
$lang['common']['results'] = 'resultat';


$lang['common']['months'][1]= 'Januari';
$lang['common']['months'][2]= 'Februari';
$lang['common']['months'][3]= 'Mars';
$lang['common']['months'][4]= 'April';
$lang['common']['months'][5]= 'Maj';
$lang['common']['months'][6]= 'Juni';
$lang['common']['months'][7]= 'Juli';
$lang['common']['months'][8]= 'Augusti';
$lang['common']['months'][9]= 'September';
$lang['common']['months'][10]= 'Oktober';
$lang['common']['months'][11]= 'November';
$lang['common']['months'][12]= 'December';

$lang['common']['short_days'][0]= "Sö";
$lang['common']['short_days'][1]= "Må";
$lang['common']['short_days'][2]= "Ti";
$lang['common']['short_days'][3]= "On";
$lang['common']['short_days'][4]= "To";
$lang['common']['short_days'][5]= "Fr";
$lang['common']['short_days'][6]= "Lö";


$lang['common']['full_days'][0] = "Söndag";
$lang['common']['full_days'][1] = "Måndag";
$lang['common']['full_days'][2] = "Tisdag";
$lang['common']['full_days'][3] = "Onsdag";
$lang['common']['full_days'][4] = "Torsdag";
$lang['common']['full_days'][5] = "Fredag";
$lang['common']['full_days'][6] = "Lördag";

$lang['common']['default']= 'Standard';
$lang['common']['description']= 'Beskrivning';
$lang['common']['date']= 'Datum';

$lang['common']['default_salutation']['M']= 'Bäste herr';
$lang['common']['default_salutation']['F']= 'Bästa fru';
$lang['common']['default_salutation']['unknown']= 'Bästa herr / fru';
$lang['common']['dear']='Bästa';

$lang['common']['mins'] = 'min';
$lang['common']['hour'] = 'timme';
$lang['common']['hours'] = 'timmar';
$lang['common']['day'] = 'dag';
$lang['common']['days'] = 'dagar';
$lang['common']['week'] = 'vecka';
$lang['common']['weeks'] = 'veckor';
$lang['common']['month'] = 'månad';
$lang['common']['strMonths'] = 'månader';

$lang['common']['group_everyone']= 'Alla';
$lang['common']['group_admins']= 'Admins';
$lang['common']['group_internal']= 'Internt';

$lang['common']['admin']= 'Administratör';

$lang['common']['beginning']= 'Hälsning';

$lang['common']['max_emails_reached']= "Max antal meddelanden per dag till e-postservern %s har uppnåtts (%s).";
$lang['common']['usage_stats']= 'Diskutrymmesanvändning per %s';
$lang['common']['usage_text']= 'Den här {product_name}-installationen använder';

$lang['common']['database']= 'Databas';
$lang['common']['files']= 'Filer';
$lang['common']['email']= 'E-post';
$lang['common']['total']= 'Totalt';


$lang['common']['confirm_leave']= 'Om du lämnar {product_name} förlorar du osparade ändringar';
$lang['common']['dataSaved']='All data sparades';

$lang['common']['uploadMultipleFiles'] = 'Klicka på \'Browse\' för att välja filer och/eller mappar från din dator. Klicka på \'Upload\' för att överföra filerna till {product_name}. Det här fönstret kommer stängas automatiskt när överföringen är klar.';


$lang['common']['loginToGO']='Klicka här för att logga in i {product_name}';
$lang['common']['links']='Länkar';
$lang['common']['GOwebsite']='{product_name} webbplats';
$lang['common']['GOisAProductOf']='<i>{product_name}</i> är en produkt från <a href="http://www.intermesh.nl/en/" target="_blank">Intermesh</a>';

$lang['common']['yes']='Ja';
$lang['common']['no']='Nej';

$lang['common']['system']='System';

$lang['common']['goAlreadyStarted']='{product_name} har redan startats. Fönstret som skapats av {FUNCTION} har laddats i {product_name}. Du kan nu stänga det här fönstret och fortsätta jobba i {product_name}.';
$lang['common']['no']='Nej';

$lang['commmon']['logFiles']='Loggfiler';

$lang['common']['reminder']='Påminnelse';
$lang['common']['unknown']='Okänd';
$lang['common']['time']='Tid';

$lang['common']['dontChangeAdminsPermissions']='Du kan inte ändra behörigheter för admingruppen';
$lang['common']['dontChangeOwnersPermissions']='Du kan inte ändra behörigheter för ägaren';


$lang['common']['running_sys_upgrade']='Kör obligatorisk systemuppdatering';
$lang['common']['sys_upgrade_text']='Ett ögonblick. All utdata kommer loggas.';
$lang['common']['click_here_to_contine']='Klicka här för att fortsätta';
$lang['common']['parentheses_invalid_error']='Parenteserna i din fråga är ogiltiga. Vänligen korrigera dom.';


$lang['common']['nReminders']='%s påminnelser';
$lang['common']['oneReminder']='1 påminnelse';

//Example: you have 1 reminders in {product_name}.
$lang['common']['youHaveReminders']='Du har %s i %s.';

$lang['common']['createdBy']='Skapad av';
$lang['common']['none']='Ingen';
$lang['common']['alert']='Info';
$lang['common']['theFolderAlreadyExists']='En mapp med det namnet finns redan';

$lang['common']['other']='Övrigt';
$lang['common']['copy']='kopia';

$lang['common']['upload_file_to_big']='Filen du försöker ladda upp var större än den största tillåtna storleken: %s.';