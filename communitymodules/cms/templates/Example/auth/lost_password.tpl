{include file="header.tpl"}
{assign var="file_name" value="Lost password"}


{if $success}
	<h1>Password sent</h1>
	<p>A new password has been sent to your e-mail address</p>
	<input type="button" value="Continue" onclick="document.location='login.php?file_id={$file.id}&amp;success_url={$success_url|escape:'url'}&cancel_url={$cancel_url|escape:'url'}';" />
{else}	
		<form name="register" method="post">
		<input type="hidden" name="success_url" value="{$success_url}" />
		<h1>Lost password</h1>
		<p>Enter your e-mail address. If a valid user account with that e-mail address is found, your username and a new password will be sent to your e-mail address.</p>

		{if $feedback}
			<p class="error">{$feedback}</p>
		{/if}

		<table>
		<tbody>
		<tr>
		<td>
		E-mail:</td>
		<td>
		{html_input required="true" type="text" name="email" value="" }
		</td>
		</tr>
		</tbody>
		</table>
		<input type="submit" name="cmdOk" value="Ok" />
		<input type="button" name="cmdCancel" value="Cancel" onclick="document.location='login.php?file_id={$file.id}&amp;success_url={$success_url|escape:'url'}&amp;cancel_url={$cancel_url|escape:'url'}';" />
		</form>
{/if}	



{include file="footer.tpl"}