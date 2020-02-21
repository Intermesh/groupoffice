## Project template

- Create new project template, fill all information (do not enable automatic sequence for projects)
- Create folders via project template dialog

```
$config['mail2project'] = [
    'deleteMessage' => true, //delete message from mailbox
    'useCustomNameGenerator' => false, //use this intead of project template generator
    //project default params
    'project' => [ 
            'parent_project_id' => 0,
            'template_id' => 12,
            'type_id' => 1,
            'status_id' => 1
    ],
    'vu' => [
        'company' => 'col_14', 
        'defaultAddressbook' => 75, 
        'mailTo' => 'info@michalcharvat.cz'
    ],
    'vn' => [
        'contact' => 'col_49', 
        'company' => 'col_81', 
        'defaultAddressbook' => 73, 
        'mailTo' => 'info@michalcharvat.cz'
    ],
    'mak' => [
        'contact' => 'col_49', 
        'company' => 'col_81', 
        'defaultAddressbook' => 74, 
        'mailTo' => 'info@michalcharvat.cz'
     ],
    'sv' => 'col_38',
    'ast' => 'col_53',
    'schadennummer' => 'col_46',
    'emailDate' => 'col_39',
    'newContactTpl' => 'New contact {CONTACT} was created in addressbook {ADDRESSBOOK}',
    'existingContactMailTo' => 'info@michalcharvat.cz',
    'existingContactTpl' => 'Project {PROJECT_NAME} was created',
    'existingContactSubject' => 'Created project {PROJECT_NAME}',
    'replyTpl' => 'vu:{VU}, vn:{VN}, mak:{MAK}, sv:{SV}, ast:{AST}, schadennummer: {SCHADENNUMMER} could be used in this template',
    'pdfMail' => [
        'subject' => 'Pdf Mail',
        'attachment' => 'relativePath/to/email/attachment.pdf',
        'tpl' => 'Email template with attachment'
    ]
];
```