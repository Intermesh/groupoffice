{include file="header.tpl"}

{if $file.type==''}
	{include file="pages/default.tpl"}
{else}
	{include file="pages/`$file.type`.tpl"}
{/if}

{include file="footer.tpl"}
