
{if $success}

	{include_file path="data/formSuccess"}

{else}

	<form method="POST" name="contact_form">

	<input type="hidden" name="return_to" value="<?php echo $_SERVER['PHP_SELF']; ?>" />
	<input type="hidden" name="addressbook" value="{$file.option_values.addressbook}" />
	<!-- <input type="hidden" name="mailings[]" value="blabla" /> -->
	<!-- <input type="hidden" name="notify_users" value="1,2" /> -->
	<input type="hidden" name="notify_addressbook_owner" value="1" />
	<input type="hidden" name="comment[Datum]" value="date" />

	<h1>Contact</h1>
	Values submitted will be entered in the Group-Office addressbook "{$file.option_values.addressbook}".

	{if $feedback}
		<div class="error">{$feedback}</div>
	{/if}

	<table class="formulier" cellpadding="0" cellspacing="2">
		
	<tr>
		<td class="label">e-mail *</td>
		<td>{html_input name="email" required="true"}</td>

	</tr>
	<tr>
		<td class="label">organisatie</td>
		<td><input class="textbox" type="" name="company" value=""  /></td>
	</tr>
	<tr>
		<td class="label">functie</td>
		<td><input class="textbox" type="" name="function" value=""  /></td>

	</tr>
	<tr>
		<td class="label">aanhef</td>
		<td>
		<label for="id_1000">
		<input type="radio" name="sex" value="M" id="id_1000" checked="checked" />De heer
		</label>
		<label for="id_1001">
		<input type="radio" name="sex" value="F" id="id_1001" />Mevrouw
		</label>

		</td>
	</tr>
		
	<tr>
		<td class="label">voornaam *</td>
		<td><input class="textbox" type="" name="first_name" value=""  /><input type="hidden" name="required[]" value="first_name" /></td>
	</tr>
	<tr>
		<td class="label">achternaam *</td>

		<td><input class="textbox" type="" name="last_name" value=""  /><input type="hidden" name="required[]" value="last_name" /></td>
	</tr>
	<tr>
		<td class="label">telefoonnummer</td>
		<td><input class="textbox" type="" name="home_phone" value=""  /></td>
	</tr>
	<tr>
		<td class="label">vraag | opmerking</td>

		<td><textarea class="textbox" name="comment[Opmerking]" value="" ></textarea></td>
	</tr>
	<tr>
		<td></td>
		<td>
				<div class="button-green" onmouseover="this.className='button-green-hover';"  onmouseout="this.className='button-green';">
					<div class="button-green-right">
						<a href="javascript:document.contact_form.submit();" class="button-green-center">
						Submit
						</a>
					</div>
				</div>
		</td>
	</tr>
	</table>

	</form>
{/if}