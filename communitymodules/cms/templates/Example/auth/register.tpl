{include file="header.tpl"}
{assign var="file_name" value="Register"}


{if $success}
	<h1>Registration successfull</h1>
	<p>Thank you for registering! You are now logged in.</p>
	<input type="button" value="Continue" onclick="document.location='{$success_url}';" />

{else}

	<form name="register" method="post">
	<input type="hidden" name="success_url" value="{$success_url}" />
	<h1>Register at Intermesh Group-Office</h1><p>Fill out this form and click on 'Ok' to register. The fields marked with a * are required.</p>

	{if $feedback}
		<p class="error">{$feedback}</p>
	{/if}

	<table><tbody>
	<tr>
	<td>
	First name*:</td>
	<td>
	{html_input required="true" type="text" name="first_name" }
	</td>
	</tr>
	<tr>
	<td>
	Middle name:</td>
	<td>
	{html_input type="text" name="middle_name" }
	</td>
	</tr>
	<tr>

	<td>
	Last name*:</td>
	<td>
	{html_input required="true" type="text" name="last_name" }
	</td>
	</tr>
	<tr>
	<td>
	Gender:</td>
	<td>
	{html_input type="radio" name="sex" value="M" }
	Man
	{html_input type="radio" name="sex" value="F" }
	Vrouw</td>
	</tr>
	<tr>
	<td>
	E-mail*:</td>
	<td>
	{html_input required="true" type="text" name="email" }
	</td>
	</tr>
	<tr>
	<td colspan="2">
	&nbsp;</td>
	</tr>
	<tr>
	<td>
	Address*:</td>
	<td>
	{html_input required="true" type="text" name="address" }
	</td>
	</tr>
	<tr>
	<td>
	Number of house*:</td>
	<td>
	{html_input required="true" type="text" name="address_no" }
	</td>
	</tr>

	<tr>
	<td>
	ZIP/Postal code*:</td>
	<td>
	{html_input required="true" type="text" name="zip" }
	</td>
	</tr>
	<tr>
	<td>
	City*:</td>

	<td>
	{html_input required="true" type="text" name="city" }
	</td>
	</tr>
	<tr>
	<td>
	State/Province*:</td>
	<td>
	{html_input required="true" type="text" name="state" }
	</td>
	</tr>
	<tr>
	<td>
	Country*:</td>
	<td>

	{html_options name=country options=$countries selected=$country}
	</td>
	</tr>
	<tr>
	<td colspan="2">
	&nbsp;</td>
	</tr>
	<tr>
	<td>
	Phone:</td>
	<td>
	{html_input type="text" name="home_phone" }

	</td>
	</tr>
	<tr>
	<td>
	Mobile:</td>
	<td>
	{html_input type="text" name="cellular" }
	</td>
	</tr>
	<tr>
	<td colspan="2">
	&nbsp;</td>
	</tr>
	<tr>
	<td>
	Company:</td>
	<td>
	{html_input type="text" name="company" }
	</td>
	</tr>
	<tr>
	<td>
	Department:</td>
	<td>
	{html_input type="text" name="department" }
	</td>
	</tr>
	<tr>
	<td>
	Function:</td>
	<td>

	{html_input type="text" name="function" }
	</td>
	</tr>
	<tr>
	<td colspan="2">
	&nbsp;</td>
	</tr>
	<tr>
	<td>
	Username*:</td>
	<td>
	{html_input required="true" type="text" name="username" }
	</td>
	</tr>
	<tr>
	<td>

	Password*:</td>
	<td>
	{html_input required="true" type="password" name="pass1" }
	</td>
	</tr>
	<tr>
	<td>
	Confirm*:</td>
	<td>
	{html_input required="true" type="password" name="pass2" }
	</td>
	</tr>
	</tbody></table>
	<input type="submit" name="cmdOk" value="Ok" />
	<input type="button" name="cmdReset" value="Reset" onclick="document.register.reset();" />
	<input type="button" name="cmdCancel" value="Cancel" onclick="document.location='login.php?file_id={$file.id}&amp;success_url={$success_url|escape:'url'}&cancel_url={$cancel_url|escape:'url'}';" />
	</form>
{/if}


{include file="footer.tpl"}