<?php

//Polish Translation v1.0
//Author : Robert GOLIAT info@robertgoliat.com  info@it-administrator.org
//Date : January, 20 2009
//Polish Translation v1.1
//Author : Paweł Dmitruk pawel.dmitruk@gmail.com
//Date : September, 03 2010
//Polish Translation v1.2
//Author : rajmund
//Date : January, 26 2011

require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('common'));
$lang['common']['about']='Version: {version}<br />
<br />
Copyright (c) 2003-{current_year}, {company_name}<br />
All rights reserved.<br />
This program is protected by copyright law and the {product_name} license.<br />
';

$lang['common']['totals']='Razem';
$lang['common']['printPage']='Strona %s z %s';

$lang['common']['htmldirection']= 'ltr';

$lang['common']['quotaExceeded']='Brak dostępnego miejscs. Usuń kilka plików lub skontaktuj się z administratorem w celu zwiększenia miejsca';
$lang['common']['errorsInForm'] = 'Wystąpiły błedy w formularzu. Popraw je i spróbuj ponownie.';

$lang['common']['moduleRequired']='Do wykonania tej operacji wymagany jest moduł %s';

$lang['common']['loadingCore']= 'Ładowanie systemu podstawowego';
$lang['common']['loadingLogin'] = 'Ładowanie okna dialogowego logowania';
$lang['common']['renderInterface']='Renderowanie interfejsu';
$lang['common']['loadingModules']='Ładowanie modułów';
$lang['common']['loadingModule'] = 'Ładowanie modułu';

$lang['common']['loggedInAs'] = "Zalogowano jako ";
$lang['common']['search']='Szukaj';
$lang['common']['settings']='Ustawienia';
$lang['common']['adminMenu']='Menu Admina';
$lang['common']['startMenu']='Start menu';
$lang['common']['help']='Pomoc';
$lang['common']['logout']='Wyloguj';
$lang['common']['badLogin'] = 'Niewłasciwy użytkownik lub hasło';
$lang['common']['badPassword'] = 'Wprowadzone bieżące hasło jest złe';

$lang['common']['passwordMatchError']='Hasła nie zgadzają się';
$lang['common']['accessDenied']='Brak dostępu';
$lang['common']['saveError']='Bład podczas zapisywania danych';
$lang['common']['deleteError']='Błąd podczas usuwania danych';
$lang['common']['selectError']='Błąd podczas próby odczytu danych';
$lang['common']['missingField'] = 'Nie wypełniono wszystkich wymaganych pól.';
$lang['common']['invalidEmailError']='Adres e-mail jest nieprawidłowy';
$lang['common']['invalidDateError']='Wprowadzono niepoprawną datę';
$lang['common']['noFileUploaded']='Nie odebrano żadnych plików';
$lang['common']['error']='Błąd';
$lang['common']['fileCreateError']='Nie można utworzyć pliku';
$lang['common']['illegalCharsError']='Nazwa zawiera jeden z niedozwolonych znaków %s';

$lang['common']['salutation']='Powitanie';
$lang['common']['firstName'] = 'Imię';
$lang['common']['lastName'] = 'Nazwisko';
$lang['common']['middleName'] = 'Drugie imię';
$lang['common']['sirMadam']['M'] = 'Pan';
$lang['common']['sirMadam']['F'] = 'Pani';
$lang['common']['initials'] = 'Inicjały';
$lang['common']['sex'] = 'Płeć';
$lang['common']['birthday'] = 'Data urodzenia';
$lang['common']['sexes']['M'] = 'Mężczyzna';
$lang['common']['sexes']['F'] = 'Kobieta';
$lang['common']['title'] = 'Tytuł';
$lang['common']['addressNo'] = 'Nr domu';
$lang['common']['workAddressNo'] = 'Nr domu (praca)';
$lang['common']['postAddress'] = 'Adres (praca)';
$lang['common']['postAddressNo'] = 'Nr domu (poczta)';
$lang['common']['postCity'] = 'Miasto (poczta)';
$lang['common']['postState'] = 'Województwo (poczta)';
$lang['common']['postCountry'] = 'Kraj (poczta)';
$lang['common']['postZip'] = 'Kod pocztowy (poczta)';
$lang['common']['visitAddress'] = 'Visit address';
$lang['common']['postAddressHead'] = 'Post address';
$lang['common']['name'] = 'Nazwa';
$lang['common']['user'] = 'Użytkownik';
$lang['common']['username'] = 'Użytkownik';
$lang['common']['password'] = 'Hasło';
$lang['common']['authcode'] = 'Kod autoryzacji';
$lang['common']['country'] = 'Kraj';
$lang['common']['address_format']='Format adresu';
$lang['common']['state'] = 'Województwo';
$lang['common']['city'] = 'Miasto';
$lang['common']['zip'] = 'Kod pocztowy';
$lang['common']['address'] = 'Adres';
$lang['common']['email'] = 'E-mail';
$lang['common']['phone'] = 'Telefon';
$lang['common']['workphone'] = 'Telefon (praca)';
$lang['common']['cellular'] = 'Telefon komórkowy';
$lang['common']['company'] = 'Firma';
$lang['common']['department'] = 'Dział';
$lang['common']['function'] = 'Funkcja';
$lang['common']['question'] = 'Tajne pytanie';
$lang['common']['answer'] = 'Odpowiedź';
$lang['common']['fax'] = 'Fax';
$lang['common']['workFax'] = 'Fax (praca)';
$lang['common']['homepage'] = 'Strona domowa';
$lang['common']['workAddress'] = 'Adres (praca)';
$lang['common']['workZip'] = 'Kod pocztowy (praca)';
$lang['common']['workCountry'] = 'Kraj (praca)';
$lang['common']['workState'] = 'Województwo (praca)';
$lang['common']['workCity'] = 'Miasto (praca)';
$lang['common']['today'] = 'Dziś';
$lang['common']['tomorrow'] = 'Jutro';

$lang['common']['SearchAll'] = 'Wszystkie pola';
$lang['common']['total'] = 'razem';
$lang['common']['results'] = 'wyników';

$lang['common']['months'][1]='Styczeń';
$lang['common']['months'][2]='Luty';
$lang['common']['months'][3]='Marzec';
$lang['common']['months'][4]='Kwiecień';
$lang['common']['months'][5]='Maj';
$lang['common']['months'][6]='Czerwiec';
$lang['common']['months'][7]='Lipiec';
$lang['common']['months'][8]='Sierpień';
$lang['common']['months'][9]='Wrzesień';
$lang['common']['months'][10]='Październik';
$lang['common']['months'][11]='Listopad';
$lang['common']['months'][12]='Grudzień';

$lang['common']['short_days'][0]="Nd";
$lang['common']['short_days'][1]="Pn";
$lang['common']['short_days'][2]="Wt";
$lang['common']['short_days'][3]="Śr";
$lang['common']['short_days'][4]="Cz";
$lang['common']['short_days'][5]="Pt";
$lang['common']['short_days'][6]="So";

$lang['common']['full_days'][0] = "Niedziela";
$lang['common']['full_days'][1] = "Poniedziałek";
$lang['common']['full_days'][2] = "Wtorek";
$lang['common']['full_days'][3] = "Środa";
$lang['common']['full_days'][4] = "Czwartek";
$lang['common']['full_days'][5] = "Piątek";
$lang['common']['full_days'][6] = "Sobota";

$lang['common']['default']='Domyślnie';
$lang['common']['description']='Opis';
$lang['common']['date']='Data';

$lang['common']['default_salutation']['M']='Szanowny Pan';
$lang['common']['default_salutation']['F']='Szanowna Pani';
$lang['common']['default_salutation']['unknown']='Szanowna Pani/Pan';
$lang['common']['dear']='Szanowny';

$lang['common']['mins'] = 'Minut';
$lang['common']['hour'] = 'godzina';
$lang['common']['hours'] = 'godzin';
$lang['common']['day'] = 'dzień';
$lang['common']['days'] = 'dni';
$lang['common']['week'] = 'tydzień';
$lang['common']['weeks'] = 'tygodni';
$lang['common']['month']= 'miesiąc';
$lang['common']['strMonths']= 'miesiące/miesięcy';

$lang['common']['group_everyone']='Wszyscy';
$lang['common']['group_admins']='Administratorzy';
$lang['common']['group_internal']='Wbudowane';

$lang['common']['admin']='Administrator';

$lang['common']['beginning']='Powitanie';

$lang['common']['max_emails_reached']= "Osiągnięto maksymalną liczbę przesyłek of e-mail dla hosta %s z %s na dzień.";
$lang['common']['usage_stats']='Użycie miejsca na dysku na %s';
$lang['common']['usage_text']='Ta instalacja {product_name} używa';

$lang['common']['database']='Baza danych';
$lang['common']['files']='Pliki';
$lang['common']['email']='E-mail';
$lang['common']['total']='Razem';

$lang['common']['confirm_leave']='Jeśli opuścisz {product_name} to stracisz niezapisane zmiany';
$lang['common']['dataSaved']='Dane zostały zapisane';

$lang['common']['uploadMultipleFiles']= 'Kliknij \'Przeglądaj\' aby wybrać pliki i/lub foldery z komputera. Kliknij \'Wyślij\' aby przesłać dane do {product_name}. To okno zamknie się automatycznie po zakończeniu transferu.';

$lang['common']['loginToGO']='Kliknij aby się zalogować do {product_name}';
$lang['common']['links']='Odnośniki';
$lang['common']['GOwebsite']='{product_name} website';
$lang['common']['GOisAProductOf']='<i>{product_name}</i> is a product of <a href="http://www.intermesh.nl/en/" target="_blank">Intermesh</a>';

$lang['common']['yes']='Tak';
$lang['common']['no']='Nie';

$lang['common']['system']='System';

$lang['common']['goAlreadyStarted']='{product_name} uruchomiony. Żądana operacja uruchomiona w {product_name}. Możesz już zamknąć te okno lub kartę i kontynuować pracę w {product_name}.';

$lang['commmon']['logFiles']='Pliki logów';

$lang['common']['reminder']='Przypomnienie';
$lang['common']['unknown']='Nieznany';
$lang['common']['time']='Czas';

$lang['common']['dontChangeAdminsPermissions']='Nie możesz zmienić uprawnień dla grupy administratorów';
$lang['common']['dontChangeOwnersPermissions']='Nie możesz zmienić uprawnień dla użytkownika';

$lang['common']['running_sys_upgrade']='Uruchomienie wymaga aktualizacji systemu';
$lang['common']['sys_upgrade_text']='Proszę czekać. Wszystkie operacje będą rejestrowane.';
$lang['common']['click_here_to_contine']='Kliknij aby kontynuować';
$lang['common']['parentheses_invalid_error']='Nawiasy w zapytaniu są nieprawidłowe. Proszę poprawić.';

$lang['common']['nReminders']='%s przypomnień/nia';
$lang['common']['oneReminder']='1 przypomnienie';

$lang['common']['youHaveReminders']='Masz %s z %s.';

$lang['common']['createdBy']='Utworzone przez';
$lang['common']['none']='Brak';
$lang['common']['alert']='Ostrzeżenie';
$lang['common']['theFolderAlreadyExists']='Folder o tej nazwie istnieje';
$lang['common']['name2']= 'Nazwa 2';
$lang['common']['other']='Inne';
$lang['common']['copy']='kopiuj';
$lang['common']['upload_file_to_big']='Plik, który próbowano przesłać na serwej był większy niż maksymalny dozwolony (obecny limit: %s).';
