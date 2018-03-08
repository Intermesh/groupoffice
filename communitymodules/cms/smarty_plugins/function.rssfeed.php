<?php
function smarty_function_rssfeed($params, &$smarty)
{
	if(!isset($_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['articles']) || $_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['timestamp']<time()-3600)
	{
		$_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['articles']=array();
		
		$sXml = simplexml_load_file($params['url']);
	
		foreach ($sXml->channel->item as $item)
		{
			$article = array();
			$article['title'] = (string) $item->title;
			$article['link'] = (string) $item->link;
			$article['pubDate'] = (string) $item->pubDate;
			//$article['timestamp'] = strtotime($item->pubDate);
			$article['localPubDate'] = Date::format($item->pubDate);
	
			if(isset($item->comments))
				$article['comments'] = (string) $item->comments;
						
			if(isset($item->description))
				$article['description'] = (string) trim($item->description);
				
			$article['isPermaLink'] = !empty($item->guid['isPermaLink']);			
			
			$_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['articles'][]=$article;			
		}
		$_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['timestamp']=time();
	}
	$count =  count($_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['articles']);
	
	$start = isset($params['start']) ? $params['start'] : 0;
	$offset = isset($params['offset']) && $params['offset']>0 && $params['offset'] <= $count ? $params['offset'] : $count;
	
	$articles=array();
	for($i=$start;$i<$offset;$i++)
	{
		$articles[]=$_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['articles'][$i];
		if(!empty($params['template']))
		{
			$smarty->assign('article', $_SESSION['GO_SESSION']['rssfeeds'][$params['url']]['articles'][$i]);			
			$smarty->display($params['template']);
		}
	}	
	if(!empty($params['assign']))
	{
		$smarty->assign($params['assign'], $articles);
	}
}
?>