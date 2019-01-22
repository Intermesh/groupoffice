<?php
if(!empty(\GO::config()->serverclient_domains))
{	
	$domains = \GO::config()->serverclient_domains;
	$domains = is_array($domains) ? $domains : explode(',', $domains);
	$GO_SCRIPTS_JS .= 'Ext.namespace("GO.serverclient");GO.serverclient.domains=["'.implode('","', $domains).'"];';
}