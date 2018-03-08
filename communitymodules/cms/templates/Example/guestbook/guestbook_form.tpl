<br />
<p>Post a message:</p>

{if $feedback}
	<p class="error">{$feedback}</p>
{/if}

<form name="guestbook_form" method="post">
<input type="hidden" name="guestbook_task" value="add" />
<input type="hidden" name="file_id" value="{$file.id}" />


<table class="guestbook">
<tbody>
<tr>
<td style="white-space:nowrap">
Name:</td>
<td style="width:100%">
{html_input type="text" name="name" value="" extra='style="width:99%;"' }
</td>
</tr>
<tr>
<td style="white-space:nowrap;vertical-align:top">
Message:</td>
<td style="width:100%">
{html_textarea name="content" extra='style="width:99%;height:50px;"' }
</td>

</tr>
<tr>
<td colspan="2">
Please answer the followin antispam question.</td>
</tr>
<tr>
<td style="white-space:nowrap">
{$antispam_question}</td>
<td style="width:100%">
<input type="text" name="{$antispam_var}" value="" style="width:99%;" class="textbox" />
</td>
</tr>
</tbody>
</table>
<input type="submit" name="submit" value="Post message" class="comments_input" />
</form>

<br />