{include file="header.tpl"}

{if $password_changed}
	{$lang.lostpassword.success}

	<div class="button-green" onmouseover="this.className='button-green-hover';"  onmouseout="this.className='button-green';">
		<div class="button-green-right">
			<a href="javascript:document.location='{$go_url}';" class="button-green-center">
			{$lang.lostpassword.login}
			</a>
		</div>
	</div>

{else}

	<form name="lpf" method="post" action="">
	<input type="hidden" name="username" value="{$smarty.request.username}" />
	<input type="hidden" name="code1" value="{$smarty.request.code1}" />
	<input type="hidden" name="code2" value="{$smarty.request.code2}" />

	<br />
	<h1>{$lang.lostpassword.enter_password}</h1>

	{if $feedback}
	<div class="error">{$feedback}</div>
	{/if}

	<table>
		<tr>
			<td>{$lang.lostpassword.new_password}: </td>
			<td><input type="password" name="pass1" value="{$smarty.request.pass1}" /></td>
		</tr>
		<tr>
			<td>{$lang.lostpassword.confirm_password}: </td>
			<td><input type="password" name="pass2" value="{$smarty.request.pass2}" /></td>
		</tr>
	</table>

	<div class="button-green" onmouseover="this.className='button-green-hover';"  onmouseout="this.className='button-green';">
		<div class="button-green-right">
			<a href="javascript:document.lpf.submit();" class="button-green-center">
			{$lang.lostpassword.send}
			</a>
		</div>
	</div>

	<br />
	<br />
	</form>
{/if}

{include file="footer.tpl"}