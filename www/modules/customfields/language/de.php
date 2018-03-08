<?php

$l["customfields"]='Zusatzfelder';
$l["category"]='Kategorie';
$l["categories"]='Kategorien für zusätzliche Felder';
$l["manageCategories"]='Kategorien verwalten';
$l["numberField"]='<br />Es kann jedes Nummernfeld verwendet werden. Feldnamen müssen mit {} umschließen und eine Leertaste zwischen jedes Wort einfügt werden (z.B. {Nummer1} + {Nummer2}, aber nicht Nummer1+Nummer2):<br /><br />';
$l["selectOptions"]='Optionen auswählen';
$l["noOptions"]='Es wurden noch keine Optionen definiert';
$l["enterText"]='Bitte geben Sie einen Text für die Option ein:';
$l["functionProperties"]='Funktions-Eigenschaften';
$l["restart"]='Die Änderungen werden erst nach einem Neustart von GroupOffice aktiv.';
$l["noFields"]='Keine benutzerdefinierten Felder vorhanden';
$l["createCategoryFirst"]='Sie müssen zuerst eine Kategorie anlegen';
$l['name']='Zusatzfelder';
$l['description']='Modul zum Anlegen benutzerdefinierter Felder im Adressen- und Projekt-Modul';
$l["required"]='Pflichtfeld';
$l["helpText"]='Hilfetext';
$l["importText"]='Laden Sie eine CSV-Datei hoch, bei der jeder Wert in einer separaten Spalte oder in einer neuen Zeile steht.';
$l["multiselect"]='Mehrfachauswahl';
$l["maxOptions"]='Maximale Anzahl von Optionen';
$l["zeroMeansUnlimited"]='0 bedeutet unbegrenzt';
$l["clickApplyFirst"]='Klicken bitte erst auf Übernehmen, bevor Sie importieren.';
$l["treeImportText"]='Sie können eine CSV-Datei importieren, in der jede Spalte eine Ebene repräsentiert, z.B.<br />"Option 1","Option 1.1", "Option 1.1.1"<br />"Option 1","Option 1.2", "Option 1.2.1". Bitte ersetzten Sie das Komma durch Ihre persönliche Einstellung in {product_name}.';
$l["usableOperators"]='Sie können die folgenden Operatoren verwenden: / , * , + und - :<br /><br />';
$l["height"]='Höhe';
$l["bulkEdit"]='Auswahl bearbeiten';
$l["applyToSelectionInstructions"]= 'Nutzen Sie die rechte Checkbox, um die Änderungen der Feldwerte für die ausgewählten Dateien zu übernehmen.';
$l["applyCategoryChanges"]= 'Änderungen für die Auswahl übernehmen';
$l["success"]= 'Erfolgreich';
$l["appliedToSelection"]= 'Die Änderungen wurden für die benutzerdefinierten Felder der ausgewählten Dateien übernommen.';
$l["noFileSelected"]= 'Es wurden keine Dateien ausgewählt. Wählen Sie zunächst Dateien aus.';
$l['enabledCustomFields']='Aktivierte benutzerdefinierte Felder';
$l['enableSelectedCategories']='Aktiviere, dass nur ausgewählte Kategorien angezeigt werden';
$l['defaultValidationError']='Feld war nicht korrekt formatiert';
$l['numberValidationError']='Feld war keine Zahl';

$l['invalidRegex']="Der reguläre Ausdruck ist ungültig.";
$l['nDecimals'] = 'Anzahl der Nachkommastellen';

$l['block']= 'Block';
$l['blocks']= 'Blöcke';

$l['cfDatatype']= 'Datentyp des benutzerdefinierten Feldes';

$l['GO\Addressbook\Model\Contact']= 'Kontakt';
$l['GO\Addressbook\Model\Company']= 'Firma';
$l['GO\Base\Model\User']= 'Benutzer';
$l['GO\Projects\Model\Project']= 'Projekt';
$l['GO\Addressbook\Customfieldtype\Contact']= 'Kontakt';
$l['GO\Addressbook\Customfieldtype\Company']= 'Firma';

$l['manageBlocks']= 'Blöcke verwalten';

$l['GO\Tickets\Model\Ticket']= 'Ticket';
$l['GO\Files\Model\Folder']= 'Ordner';
$l['GO\Files\Model\File']= 'Datei';

$l['cfName']= 'Benutzerdefinierter Feldname';
$l['customfield']= 'Benutzerdefiniertes Feld';

$l['enableBlocks']= 'Blöcke aktivieren';
$l['enabled']= 'Aktiviert';

$l['uniqueValues']= 'Eindeutige Werte';
$l['makeUnique']= 'Benutzerdefinierte Felder mit eindeutigen Werten versehen';
$l['duplicateExistsFeedback']= 'Der eingegebene Wert "%val" für das Feld "%cf" ist in der Datenbank schon vorhanden. Der Wert muss eindeutig sein. Bitte geben Sie für das Feld einen anderen Wert ein.';
$l['maxLength']= 'Maximale Anzahl von Zeichen';

$l['GO\Tasks\Model\Task']= 'Aufgabe';
$l['GO\Notes\Model\Note']= 'Notiz';
$l['GO\Billing\Model\Product']= 'Produkt';
$l['GO\Site\Model\Site']= 'Seite';

$l['validationRegexp']='Regulären Ausdruck überprüfen';
$l['multiselectForLastSlaveOnly']='Nur der letzte TreeSelect kann ein Multiselect-Combo Feld sein';
$l['excludeFromGrid']='Von der Matrix ausschliessen';
$l['cfUsedIn']= 'Benutzerfeld wird benutzt in';
$l['customfieldID']= 'Benutzerfeld ID';
$l['modelTypeListed']= 'Gelisteter Modeltyp';
$l['listedUnder']= 'Gelistet unter';
//$l['makeUniqueRUSure']= 'This makes this custom field\'s values unique. If there are any duplicate values already set with this custom field, they will be deleted. Continue?';
$l['tooManyCustomfields']= 'Die Gesamtmenge der Daten für Ihre benutzerdefinierten Felder (zum Objekttyp %s gehörend) haben den Speichergrenzwert überschritten. Sie können dies ändern durch eine Senkung der maximalen Anzahl der Zeichen einiger benutzerdefinierter Felder. Das aktuelle benutzerdefinierte Feld wurde nicht gespeichert.';
$l['customfieldTooLarge']= 'Das benutzerdefinierte Feld das Sie versuchen zu speichern, hat mehr als die erlaubte Anzahl Zeichen (% s). Bitte reduzieren Sie die maximale Anzahl der Zeichen dieses Feldes und versuchen Sie erneut zu speichern.';
$l['GO\Projects\Model\Hour']= 'Zeiteintrag';
$l['GO\Calendar\Model\Event']= 'Ereignis';
$l['GO\Billing\Model\Order']= 'Rechnung/Angebot';
$l['GO\Site\Model\Content']= 'Inhalt';
$l['addressbookIds']= 'Nur von diesen Adressbüchern (IDs)';

$l['prefix']= 'Prefix';
$l['suffix']= 'Suffix';
