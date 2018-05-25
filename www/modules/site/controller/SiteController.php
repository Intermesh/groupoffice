<?php


namespace GO\Site\Controller;


class SiteController extends \GO\Base\Controller\AbstractJsonController {
	
	/**
	 * Redirect to the homepage
	 * 
	 * @param array $params
	 */
	protected function actionRedirectToFront($params){
		
		$site = \GO\Site\Model\Site::model()->findByPk($params['id']);
		
		header("Location: ".$site->getBaseUrl());
		exit();
	}
	
	protected function actionLoad($params) {
		$model = \GO\Site\Model\Site::model()->createOrFindByParams($params);
		
		echo $this->renderForm($model);
	}
	
	protected function actionSubmit($params) {
		$model = \GO\Site\Model\Site::model()->createOrFindByParams($params);
		$model->setAttributes($params);
		$model->save();
		echo $this->renderSubmit($model);
	}
	
		
	/**
	 * Build the tree for the backend
	 * 
	 * @param array $params
	 * @return array
	 */
	protected function actionTree($params){
		$response=array();
	
		if(!isset($params['node']))
			return $response;
		
		$extractedNode = \GO\Site\SiteModule::extractTreeNode($params['node']);
		
		// 1_menuitem_6 = array('siteId' => '1','type' =>'menuitem','modelId' => '6');
		// 1_root = array('siteId' => '1','type' =>'root','modelId' => false);
		// 1_content = array('siteId' => '1','type' =>'content','modelId' => false);
		// 1_menu = array('siteId' => '1','type' =>'menu','modelId' => false);
		// 1_menu_1 = array('siteId' => '1','type' =>'menu','modelId' => '1');
				
		switch($extractedNode['type']){
			case 'root':
				$response = \GO\Site\Model\Site::getTreeNodes();
				break;
			case 'content':
				if(empty($extractedNode['modelId'])){
					$response = \GO\Site\Model\Site::getTreeNodes();
				} else {
					$content = \GO\Site\Model\Content::model()->findByPk($extractedNode['modelId']);
					if($content)
						$response = $content->getChildrenTree();
				}
				break;
			case 'menu':
				$menu = \GO\Site\Model\Menu::model()->findByPk($extractedNode['modelId']);
					if($menu)
						$response = $menu->getChildrenTree();
				break;
			case 'menuitem':
				$menuitem = \GO\Site\Model\MenuItem::model()->findByPk($extractedNode['modelId']);
					if($menuitem)
						$response = $menuitem->getChildrenTree();
				break;
		}
		
		echo $this->renderJson($response);
	}
	
	
	/**
	 * Rearrange the tree based on the given sorting
	 * 
	 * @param array $params
	 * @return array
	 */
	protected function actionTreeSort($sortOrder, $parent){
//		EXAMPLE INPUT
//		parent:1_menu_11
//		sortOrder:["1_menuitem_30","1_menuitem_31","1_menuitem_33","1_menuitem_8"]
			$sortOrder = json_decode($sortOrder, true);
			$extractedParentNode = \GO\Site\SiteModule::extractTreeNode($parent);
			
			switch($extractedParentNode['type']){
				case 'content':
					$allowedTypes = array('content');
					return \GO\Site\Model\Content::setTreeSort($extractedParentNode, $sortOrder, $allowedTypes);
					break;
//				case 'site':
//					$allowedTypes = array('content');
//					return \GO\Site\Model\Site::setTreeSort($extractedParentNode, $sortOrder, $allowedTypes);
//					break;
				case 'menu':
					$allowedTypes = array('menuitem');
					return \GO\Site\Model\Menu::setTreeSort($extractedParentNode, $sortOrder, $allowedTypes);
					break;
				case 'menuitem':
					$allowedTypes = array('menuitem');
					return \GO\Site\Model\MenuItem::setTreeSort($extractedParentNode, $sortOrder, $allowedTypes);
					break;
			}
	}
	
	/**
	 * Save the state of the tree
	 * 
	 * @param array $params
	 * @return array
	 */
	protected function actionSaveTreeState($params) {
		$response['success'] = \GO::config()->save_setting("site_tree_state", $params['expandedNodes'], \GO::user()->id);
		return $response;
	}	
	
	
	
	protected function actionCheatSheet(){
		echo  '<pre style="padding:10px; font-family: \'Lucida Console\', Monaco, monospace;">
			
Links:
1. Paste images from the clipboard to insert them.
Note: Supported in Chrome,Safari and IE 11. 
In Firefox make sure the editor has focus, then click 
anywhere OUTSIDE the editor and press CTRL+V.

2. Drag and drop images or files from the file manager 
   to the content editor to insert them.

3. Drag and drop content items from the tree to 
   link to those pages.

Markdown syntax:

# Header 1
## Header 2
### Header 3
#### Header 4
##### Header 5

## Markdown plus h2 with a custom ID ## {#id-goes-here}
[Link back to H2](#id-goes-here)

This is a paragraph, which is text surrounded by whitespace. 
Paragraphs can be on one line (or many), and can drone on 
for hours.  

Here is a Markdown link:

[Group-Office](https://www.group-office.com) 

Now some inline markup like *italics*,  **bold**, and 
`code()`.

![picture alt](/images/photo.jpeg "Title is optional")     

> Blockquotes are like quoted text in email replies
>> And, they can be nested

* Bullet lists are easy too
- Another one
+ Another one

1. A numbered list
2. Which is numbered
3. With periods and a space

And now some code:

    // Code is just text indented a bit
    which(is_easy) to_remember();

~~~

// Markdown extra adds un-indented code blocks too

if (this_is_more_code == true && !indented) {
    // tild wrapped code blocks, also not indented
}

~~~

Text with  
two trailing spaces  
(on the right)  
can be used  
for things like poems  

### Horizontal rules

* * * *
****
--------------------------

<div class="custom-class" markdown="1">
This is a div wrapping some Markdown plus.  
Without the DIV attribute,
it ignores the block. 
</div>

## Markdown plus tables ##

| Header | Header | Right  |
| ------ | ------ | -----: |
|  Cell  |  Cell  |   $10  |
|  Cell  |  Cell  |   $20  |

* Outer pipes on tables are optional
* Colon used for alignment (right versus left)

## Markdown plus definition lists ##

Bottled water
: $ 1.25
: $ 1.55 (Large)

Milk
Pop
: $ 1.75

* Multiple definitions and terms are possible
* Definitions can include multiple paragraphs too

*[ABBR]: Markdown plus abbreviations (produces an <abbr> tag)

</pre>';
	}
}
