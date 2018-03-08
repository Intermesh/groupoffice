<?php

$title = GO::config()->get_setting('mediawiki_title');
if (empty($title)) $title='Mediawiki';

$wikiurl = GO::config()->get_setting('mediawiki_external_url');

$GO_SCRIPTS_JS .="
	Ext.namespace('GO.mediawiki');
	Ext.namespace('GO.mediawiki.settings');
	GO.mediawiki.settings.externalUrl='$wikiurl';
	GO.mediawiki.settings.title='$title';";
