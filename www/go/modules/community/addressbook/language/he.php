<?php
return array (
  'Contact' => 'جهة اتصال',
  'salutationTemplate' => 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}=="M"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}',
  'Warning: this will copy e-mails to the Group-Office storage and will therefore increase disk space usage. The e-mail will be visible to all people that can view the contact too.' => 'Warning: this will copy e-mails to the {product_name} storage and will therefore increase disk space usage. The e-mail will be visible to all people that can view the contact too.',
);
