<?php
/////////////////////////////////////////////////////////////////////////////////
//
// Copyright Intermesh
// 
// This file is part of {product_name}. You should have received a copy of the
// {product_name} license along with {product_name}. See the file /LICENSE.TXT
// 
// If you have questions write an e-mail to info@intermesh.nl
//
// @copyright Copyright Intermesh
// @version $Id: fr.inc.php 17809 2014-07-22 11:23:28Z mschering $
// @author Merijn Schering <mschering@intermesh.nl>
//
// French Translation
// Version : 4.0.99
// Author : Lionel JULLIEN / Boris HERBINIERE-SEVE
// Date : September, 20 2012
//
/////////////////////////////////////////////////////////////////////////////////

//Uncomment this line in new translations!
require($GLOBALS['GO_LANGUAGE']->get_fallback_language_file('files'));

$lang['files']['name'] = 'Fichiers';
$lang['files']['description'] = 'Module gestion des fichiers. Module pour partager des fichiers entre utilisateurs de {product_name}.';
$lang['link_type'][6]='Fichier';
$lang['files']['fileNotFound'] = 'Fichier introuvable';
$lang['files']['folderExists'] = 'Le dossier existe déjà';
$lang['files']['filenameExists'] = 'Le nom de fichier existe déjà';
$lang['files']['uploadedSucces'] = 'Fichier envoyé avec succès';
$lang['files']['ootextdoc']='Document texte Open-Office';
$lang['files']['wordtextdoc']='Document Microsoft Word';
$lang['files']['personal']='Personnel';
$lang['files']['shared']='Partagé';
$lang['files']['general']='Général';
$lang['files']['folder_modified_subject']='Changer en dossier {product_name}';
$lang['files']['folder_modified_body']='Vous avez demandé à être avisé lorsque des changements sont apportés à :

%s

Les changements suivants ont été effectués par %s :

%s
';
$lang['files']['modified']='Modifié';
$lang['files']['new']='Nouveau';
$lang['files']['deleted']='Supprimé';
$lang['files']['file']='Fichier';
$lang['files']['folder']='Dossier';
$lang['files']['files']='Fichiers';
$lang['link_type'][17]='Dossier';
$lang['files']['emptyFile']='Fichier vide';
$lang['files']['downloadLink']= 'Lien de téléchargement';
$lang['files']['clickHereToDownload']= 'Cliquez ici pour télécharger ce fichier de manière sécurisée';
$lang['files']['copyPasteToDownload']= 'Cliquez sur le lien sécurisé ci-dessous ou copiez le dans la barre d\'adresse de votre navigateur pour télécharger le fichier.';
$lang['files']['possibleUntil']= 'possible jusqu\'au';
$lang['files']['fileNotFound']='Désolé, le fichier que vous essayez de télécharger est introuvable.';
$lang['files']['no_folder_id']= 'Un id de répertoire invalide a été passé avec la requête !';