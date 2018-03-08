{include file="header.tpl"}
{assign var="file_name" value="Login"}
		
<form name="login" method="post">
<input type="hidden" name="success_url" value="{$success_url}" />
	<h1>Are you here for the first time?</h1>
	<p><a href="register.php?file_id={$file.id}&amp;success_url={$success_url|escape:'url'}&amp;cancel_url={$cancel_url|escape:'url'}">Click here to register once</a></p>

	<h1>Login if you are already registered</h1>

	{if $failed}
		<p class="error">Wrong username or password</p>
	{/if}

	<table border="0">
	<tr>
		<td>Username:</td>
		<td>{html_input type="text" name="username" required="true" }</td>
	</tr>
	<tr>
		<td>Password:</td>
		<td>{html_input type="password" name="password" required="true" } <a href="lost_password.php?file_id={$file.id}&amp;success_url={$success_url|escape:'url'}&amp;cancel_url={$cancel_url|escape:'url'}">Lost password?</a></td>
	</tr>
	</table>
	<input type="submit" value="Login" />
	<input type="button" name="cmdCancel" value="Cancel" onclick="document.location='{$cancel_url}';" />
</form>
					

{include file="footer.tpl"}