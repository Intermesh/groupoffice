<?php
/* 
ALTER TABLE `files_node` 
DROP COLUMN `deletedBy`,
DROP COLUMN `deletedAt`,
DROP COLUMN `modSeq`,
DROP INDEX `parentId_name_UNIQUE` ,
ADD UNIQUE INDEX `parentId_name_UNIQUE` (`parentId` ASC, `name` ASC),
DROP INDEX `modSeq` ;
 
 
 */
