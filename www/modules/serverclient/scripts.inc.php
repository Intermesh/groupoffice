<?php
if(!empty(\GO::config()->serverclient_domains))
{	
	$domains = explode(',', \GO::config()->serverclient_domains);
	$GO_SCRIPTS_JS .= 'Ext.namespace("GO.serverclient");GO.serverclient.domains=["'.implode('","', $domains).'"];';
}
?>