<?php
return array (
  'Star' => 'Stjärna',
  'Add to group' => 'Lägg till i grupp',
  'Remove from group' => 'Ta bort från grupp',
  'Delete %d items' => 'Ta bort %d objekt',
  'Contact' => 'Kontakt',
  'Multiple Contacts' => 'Flera kontakter',
  'Show' => 'Visa',
  'Organizations' => 'Organisation',
  'Allow new' => 'Tillåt ny',
  'Address books' => 'Adressböcker',
  'emailTypes' => 
  array (
    'work' => 'emailTypes[Arbete]',
    'home' => 'emailTypes[Hem]',
    'billing' => 'emailTypes[Fakturering]',
  ),
  'phoneTypes' => 
  array (
    'work' => 'phoneTypes[Arbete]',
    'home' => 'phoneTypes[Hem]',
    'mobile' => 'phoneTypes[Mobilnr]',
    'workmobile' => 'phoneTypes[Arbete mobilnr]',
    'fax' => 'phoneTypes[fax]',
    'workfax' => 'phoneTypes[fax-arbete]',
  ),
  'addressTypes' => 
  array (
    'visit' => 'addressTypes[Besöksadress]',
    'postal' => 'addressTypes[Postadress]',
    'work' => 'addressTypes[Arbete]',
    'home' => 'addressTypes[Hem]',
    'delivery' => 'addressTypes[Leveransadress]',
  ),
  'dateTypes' => 
  array (
    'birthday' => 'dateTypes[Födelsedag]',
    'anniversary' => 'dateTypes[Jubileum]',
    'action' => 'dateTypes[Händelse]',
  ),
  'Warning: this will copy e-mails to the Group-Office storage and will therefore increase disk space usage. The e-mail will be visible to all people that can view the contact too.' => 'Warning: this will copy e-mails to the {product_name} storage and will therefore increase disk space usage. The e-mail will be visible to all people that can view the contact too.',
  'state' => 'Kommun',
  'salutationTemplate' => 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}=="M"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}',
);
