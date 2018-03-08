<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<meta name="robots" content="all,index,follow" />
<meta name="keywords" content="{$file.keywords}" />
<meta name="description" content="{$file.description}" />
<title>{$file.title} - {$site.name}</title>
<link href="{$template_url}css/editor.css" rel="stylesheet" type="text/css" />
<link href="{$template_url}css/stylesheet.css" rel="stylesheet" type="text/css" />
<link href="{$template_url}css/buttons.css" rel="stylesheet" type="text/css" />
<link href="{$template_url}css/tabs.css" rel="stylesheet" type="text/css" />


{if $file.type=='photoalbum'}

<link rel="stylesheet" type="text/css" href="{$cms_url}plugins/shadowbox/shadowbox.css">
<script type="text/javascript" src="{$cms_url}plugins/shadowbox/shadowbox.js"></script>
<script type="text/javascript">
{literal}
Shadowbox.init({
    language:   "en",
    players:    ["img"]
});
{/literal}
</script>

{/if}
{$head}
</head>
<body>
<div class="main-container">

				<div id="login">
				{if $smarty.session.GO_SESSION.user_id>0}
					Welcome {$smarty.session.GO_SESSION.name} | <a href="{login_href}">Logout</a>
				{else}
					<a href="{login_href}">Login</a>
				{/if}
				</div>

	<div class="header">
		<div class="topmenu-container">
			{items level="0" expand_levels="0" item_template="menu/menu_item.tpl" active_item_template="menu/menu_item_active.tpl" class="topmenu-item-center" }
		</div>
	</div>
	<div class="hoofd-kader">
		<div class="hoofd-kader-menu">
			{items level="1" expand_levels="0" item_template="menu/sub_menu_item.tpl" active_item_template="menu/sub_menu_item_active.tpl" class="topmenu-item-center" }
		</div>
		<div class="hoofd-kader-top"></div>
		<div class="hoofd-kader-center">
			<div class="subkader-big-top">
				<div class="subkader-big-bottom">
					<div class="subkader-big-center">
