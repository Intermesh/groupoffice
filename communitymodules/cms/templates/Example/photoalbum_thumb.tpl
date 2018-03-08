<div class="thumb">
<a title="{$file.name}" class="thumb_link" href="{$go_url}modules/files/download.php?path={$file.relpath}" rel="shadowbox[Album];player=img">
	<img src="{thumbnail_url path="`$file.relpath`" zc=1 w=100 h=100}" alt="{$file.name}" />
</a>
<div>{$file.friendly_name}</div>
</div>
