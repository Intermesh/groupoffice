<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$title = \GO::config()->get_setting('dokuwiki_title');
if (empty($title)) $title='Dokuwiki';

/*
if(!$mode){
	$mode='grid';
}
$GO_SCRIPTS_JS .= 'Ext.namespace("GO.dokuwiki");Ext.namespace("GO.dokuwiki.settings");'.
		'GO.dokuwiki.settings.externalUrl="'.$GO_CONFIG->get_setting('dokuwiki_external_url').'";'.
		'GO.dokuwiki.settings.title="'.$title.'";';
 */

?>
<script type="text/javascript">
	Ext.namespace("GO.dokuwiki");
	Ext.namespace("GO.dokuwiki.settings");
	GO.dokuwiki.settings.externalUrl='<?php echo \GO::config()->get_setting('dokuwiki_external_url'); ?>';
	GO.dokuwiki.settings.title='<?php echo $title; ?>';
</script>