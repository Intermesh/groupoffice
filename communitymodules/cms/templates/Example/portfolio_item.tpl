<div class="portfolio {if $item_count-1==$item.index}last{/if}">
	<div class="portfolio_image">
		{assign var="escaped_path" value="`$file_storage_path``$item.option_values.image`"|escape:url}
		<img src="{phpthumb_url params="src=$escaped_path&h=190&w=190"}" />
	</div>
	<div class="portfolio_text">
	<h1>{$item.name}</h1>	
	{$item.content}
	</div>
	<div style="clear:both"></div>
</div>
