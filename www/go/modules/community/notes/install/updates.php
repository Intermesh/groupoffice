<?php
$updates['201711071208'][] = 'RENAME TABLE `no_notes` TO `notes_note`;';

$updates['201711071208'][] = 'ALTER TABLE `notes_note` CHANGE `category_id` `categoryId` INT(11) NOT NULL;';
$updates['201711071208'][] = 'ALTER TABLE `notes_note` CHANGE `user_id` `createdBy` INT(11) NOT NULL DEFAULT \'0\';';
$updates['201711071208'][] = 'ALTER TABLE `notes_note` CHANGE `muser_id` `modifiedBy` INT(11) NOT NULL DEFAULT \'0\';';
$updates['201711071208'][] = 'ALTER TABLE `notes_note` CHANGE `files_folder_id` `filesFolderId` INT(11) NOT NULL DEFAULT \'0\';';
$updates['201711071208'][] = 'ALTER TABLE `notes_note` ADD `modSeq` INT NOT NULL AFTER `id`, ADD INDEX (`modSeq`);';

$updates['201711071208'][] = 'ALTER TABLE `notes_note` ADD `createdAt` DATETIME NULL DEFAULT NULL AFTER `password`, ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `createdAt`, ADD `deletedAt` DATETIME NULL DEFAULT NULL AFTER `createdAt`;';
$updates['201711071208'][] = 'update notes_note set createdAt = from_unixtime(ctime), modifiedAt = from_unixtime(mtime);';

$updates['201711071208'][] = 'ALTER TABLE `notes_note`
  DROP `ctime`,
  DROP `mtime`;';

$updates['201711071208'][] = 'RENAME TABLE `no_categories` TO `notes_folder`;';
$updates['201711071208'][] = 'ALTER TABLE `notes_folder` CHANGE `user_id` `createdBy` INT(11) NOT NULL;';

$updates['201711071208'][] = 'ALTER TABLE `notes_folder` CHANGE `acl_id` `aclId` INT(11) NOT NULL;';


$updates['201711071208'][] = 'ALTER TABLE `notes_folder` CHANGE `files_folder_id` `fileFolderId` INT(11) NULL DEFAULT NULL;';


$updates['201711071208'][] = 'ALTER TABLE `notes_note` CHANGE `categoryId` `folderId` INT(11) NOT NULL;';

$updates['201711071208'][] = 'ALTER TABLE `notes_folder` ADD FOREIGN KEY (`aclId`) REFERENCES `core_acl`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
$updates['201711071208'][] = 'ALTER TABLE `notes_folder` ADD `modSeq` INT NOT NULL AFTER `id`, ADD `deletedAt` DATETIME NULL DEFAULT NULL AFTER `modSeq`;';

$updates['201711071208'][] = 'ALTER TABLE `notes_note` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;';

$updates['201711071208'][] = 'RENAME TABLE `notes_folder` TO `notes_note_book`;';
$updates['201711071208'][] = 'ALTER TABLE `notes_note` CHANGE `folderId` `noteBookId` INT(11) NOT NULL;';


$updates['201711071208'][] = 'ALTER TABLE `notes_note` ADD FOREIGN KEY (`noteBookId`) REFERENCES `notes_note_book`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;';


$updates['201711071208'][] = 'RENAME TABLE `cf_no_notes` TO `notes_note_custom_fields`;';
$updates['201711071208'][] = 'ALTER TABLE `notes_note_custom_fields` CHANGE `model_id` `id` INT(11) NOT NULL DEFAULT \'0\';';
$updates['201711071208'][] = 'ALTER TABLE `notes_note_custom_fields` CHANGE `id` `id` INT(11) NOT NULL;';
$updates['201711071208'][] = 'delete from notes_note_custom_fields where id not in(select id from notes_note);';
$updates['201711071208'][] = 'ALTER TABLE `notes_note_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `notes_note`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;';

$updates['201711071208'][] = 'update `core_entity` set moduleId = (select id from core_module where name=\'notes\'), name = \'Note\', clientName = \'Note\' where name = \'GO\\\\Notes\\\\Model\\\\Note\';';

$updates['201712141425'][] = function() {
		\go\modules\community\notes\model\NoteBook::entityType();
		\go\modules\community\notes\model\Note::entityType();		
};


//update 27: convert to html
$updates['201801111300'][] = function() {
	$notes = (new \go\core\db\Query)
		->select("id,content")
		->from('notes_note');
	
	foreach($notes as $note) {
		
		if(substr($note['content'], 0, 9) == "{GOCRYPT}"){
			continue;
		}
		
		$html = \go\core\util\StringUtil::textToHtml($note['content']);
		
		\go\core\App::get()->getDbConnection()->update('notes_note', ['content' => $html], ['id' => $note['id']])->execute();
	}
};


$updates['201801251511'][] = "ALTER TABLE `notes_note` CHANGE `filesFolderId` `filesFolderId` INT(11) NULL DEFAULT NULL;";
$updates['201801251511'][] = "update notes_note set filesFolderId = null where filesFolderId = 0 OR filesFolderId not in (select id from fs_folders);";

$updates['201804181402'][] = "ALTER TABLE `notes_note` CHANGE `createdBy` `createdBy` INT(11) NOT NULL;";
$updates['201804181402'][] = "ALTER TABLE `notes_note` CHANGE `modifiedBy` `modifiedBy` INT(11) NOT NULL; ";

$updates['201804181402'][] = "ALTER TABLE `notes_note` DROP `modSeq`;";
$updates['201804181402'][] = "ALTER TABLE `notes_note` DROP `deletedAt`;";


$updates['201804181402'][] = "ALTER TABLE `notes_note_book` DROP `modSeq`;";

$updates['201903291350'][] = function() {	
	$m = new \go\core\install\MigrateCustomFields63to64();
	$m->migrateEntity("Note");	
};

$updates['201905201417'][] = "ALTER TABLE `notes_note` DROP FOREIGN KEY `notes_note_ibfk_1`;";
$updates['201905201417'][] = "ALTER TABLE `notes_note` ADD CONSTRAINT `notes_note_ibfk_1` FOREIGN KEY (`noteBookId`) REFERENCES `notes_note_book`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";


$updates['201909201327'][] = "CREATE TABLE `notes_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultNoteBookId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['201909201327'][] = "ALTER TABLE `notes_user_settings`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `defaultNoteBookId` (`defaultNoteBookId`);";

$updates['201909201327'][] = "ALTER TABLE `notes_user_settings`
  ADD CONSTRAINT `notes_user_settings_ibfk_1` FOREIGN KEY (`defaultNoteBookId`) REFERENCES `notes_note_book` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_user_settings_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;";

$updates['202003261139'][] = "CREATE TABLE `notes_note_image` (
`noteId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$updates['202003261139'][] = "ALTER TABLE `notes_note_image`
  ADD PRIMARY KEY (`noteId`,`blobId`),
  ADD KEY `blobId` (`blobId`);";

$updates['202003261139'][] = "ALTER TABLE `notes_note_image`
  ADD CONSTRAINT `notes_note_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `notes_note_image_ibfk_2` FOREIGN KEY (`noteId`) REFERENCES `notes_note` (`id`) ON DELETE CASCADE;";

$updates['202003261139'][] = function() {
	$notes = \go\modules\community\notes\model\Note::find()->where('content', 'LIKE', '%<img%');
	foreach($notes as $note) {
		try {
			$note->save();
		}
		catch(\Exception $e) {
			echo "Error saving note: " . $e->getMessage() ."\n";
		}
	}
};

$updates['202006091038'][] = "ALTER TABLE `notes_note_book` CHANGE `fileFolderId` `filesFolderId` INT(11) NULL DEFAULT NULL;";
$updates['202102111108'][] = "alter table notes_note drop foreign key notes_note_ibfk_1;";

$updates['202102111108'][] = "alter table notes_note
	add constraint notes_note_ibfk_1
		foreign key (noteBookId) references notes_note_book (id)
			on delete cascade;";

//Intentianlly blank!
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";
$updates['202012041036'][] = "";

