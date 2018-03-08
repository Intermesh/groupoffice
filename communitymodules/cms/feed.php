<?php
header('Content-Type: text/html; charset=UTF-8');

require('../../Group-Office.php');

$GO_MODULES->load_modules();

if(!isset($GO_MODULES->modules['cms'])){
        header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");
        exit();
}

require_once($GO_MODULES->modules['cms']['class_path'].'cms.class.inc.php');
require_once($GO_MODULES->modules['cms']['class_path'].'output.class.inc.php');
require_once($GO_MODULES->modules['cms']['class_path'].'cms_smarty.class.inc.php');
$cms = new cms();

$co = new cms_output();

$co->set_by_id(0, $_REQUEST['folder_id']);

function replace_template($t, $r){
	foreach($r as $key=>$v){
		if(!strpos($v, 'enclosure')){
			$v=htmlspecialchars($v,ENT_QUOTES,'UTF-8');
		}
		$t = str_replace('{'.$key.'}', $v, $t);
	}

	$t = preg_replace('/\{[^\}]*\}/', '', $t);

	return $t;
}

$t['title']=$co->folder['name'];
$t['link']=$GO_MODULES->modules['cms']['full_url'].'rss.php?folder_id='.$co->folder['id'];
$t['description']='Last messages of group '.$co->folder['name'];
$t['webmaster']=$t['managingEditor']=$co->site['webmaster'];
$t['language']=$co->site['language'];


$header = '<?xml version="1.0" ?>
<rss version="2.0">
<channel>
<title>{title}</title>
<link>{link}</link>
<description>{description}</description>
<language>{language}</language>
<pubDate>'.date('r').'</pubDate>
<lastBuildDate>'.date('r').'</lastBuildDate>
<docs>http://www.rssboard.org/rss-specification</docs>
<generator>'.$GO_CONFIG->product_name.' '.$GO_CONFIG->version.'</generator>
<managingEditor>{managingEditor}</managingEditor>
<webMaster>{webMaster}</webMaster>
<ttl>60</ttl>
';

$footer = '
</channel>
</rss>';

$item = '<item>
<title>{title}</title>
<link>{link}</link>
<description>{description}</description>
<pubDate>{pubDate}</pubDate>
</item>
';

echo replace_template($header, $t);

$cms->get_files($co->folder['id']);
while($file = $cms->next_record()){
	$ri['title']=$file['name'];
	$ri['link']=$co->create_href_by_file($file);
	$ri['pubDate']=date('r', $file['mtime']);
	$ri['description']=$file['content'];
	
	echo replace_template($item, $ri);
}
echo $footer;
