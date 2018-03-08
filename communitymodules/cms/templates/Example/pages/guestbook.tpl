{go_authenticate}

{guestbook root_path="data/guestbook"}

{if $guestbook_pages}
					
		<table class="guestbook_pages">		
		<tr>
			<td>		
			{if $guestbook_pages.firstpage_href}
				<a href="{$guestbook_pages.previous_href}">vorige pagina</a> 						
			{else}
				<span class="disabled">vorige pagina</span>
			{/if}
			</td>
			
			{foreach from=$guestbook_pages.page_hrefs item=page}					
				<td><a href="{$page.href}" class="{$page.active}">{$page.page}</a></td>
			{/foreach}
			
			<td>
			{if $guestbook_pages.lastpage_href}
				<a href="{$guestbook_pages.next_href}">volgende pagina</a>
			{else}
				<span class="disabled">volgende pagina</span>
			{/if}
			</td>		
		</tr>
		</table>
{/if}