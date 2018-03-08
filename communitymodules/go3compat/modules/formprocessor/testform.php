<?php
require('../../Group-Office.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Test form</title>
		<style>
			body{
				font: 12px arial;
			}
			input{
			}
			input, textarea, select, file{
				width:250px;
			}
			td{
				
				vertical-align:top;
			}
			td.label{
				padding-top:5px;
			}
		</style>
	</head>
	<body>


		<form method="POST" action="submit.php" enctype="multipart/form-data">

			<input type="hidden" name="return_to" value="<?php echo $_SERVER['PHP_SELF']; ?>" />
			<input type="hidden" name="addressbook" value="Test Addressbook"  />
			<input type="hidden" name="confirmation_email" value="testConfirmationEmail.eml"  />
<!--			<input type="hidden" name="contact_id" value="22290"  />-->
			<input type="hidden" name="mailings[]" value="Test Adreslijst"  />
			<!-- <input type="hidden" name="mailings[]" value="blabla" /> -->
			<!--
	Enable this input to send an e-mail confirmation. 

	The path is relative to the directory where your config.php file is.


			<input type="hidden" name="confirmation_email" value="confirm.eml" />
	-->

<!--			<input type="hidden" name="notify_users" value="1,2" />

			<input type="hidden" name="notify_addressbook_owner" value="1" />-->

			<?php
			if(isset($_REQUEST['feedback'])) {
				echo '<p style="color:red">'.$_REQUEST['feedback'].'</p>';
			}

			if(isset($_POST['submitted'])) {
				echo '<p>You submitted:</p>';

				echo nl2br(var_export($_POST, true));
			}

			?>
			<table class="formulier" cellpadding="1" cellspacing="2">
				<tr>
					<td class="label">E-mail *:</td>
					<td><input class="textbox" type="" name="email" value="hallo@noreply.nl"  /><input type="hidden" name="required[]" value="email" /></td>

				</tr>
				<tr>
					<td class="label">Company:</td>
					<td><input class="textbox" type="" name="company" value="Intermesh"  /></td>
				</tr>
				<tr>
					<td class="label">Function:</td>
					<td><input class="textbox" type="" name="function" value="Testpersoon"  /></td>
				</tr>
				<tr>
					<td class="label">Salutation:</td>
					<td><label for="id_1000">
							<input type="radio" name="sex" value="M" id="id_1000" checked="checked" />Mr.
						</label>
						<label for="id_1001">
							<input type="radio" name="sex" value="F" id="id_1001" />Mrs.
						</label>

					</td>
				</tr>
				<tr>
					<td class="label">First name *:</td>
					<td><input class="textbox" type="" name="first_name" value=""  /><input type="hidden" name="required[]" value="first_name" /></td>
				</tr>
				<tr>
					<td class="label">Middle name:</td>

					<td><input class="textbox" type="" name="middle_name" value=""  /><input type="hidden" name="required[]" value="last_name" /></td>
				</tr>
				<tr>
					<td class="label">Last name *:</td>
					<td><input class="textbox" type="" name="last_name" value=""  /><input type="hidden" name="required[]" value="last_name" /></td>
				</tr>
				<tr>
					<td class="label">Initials:</td>
					<td><input class="textbox" type="" name="initials" value=""  /></td>
				</tr>
				<tr>
					<td class="label">Gender:</td>
					<td>
						<select>
							<option label="Male" value="M">Male</option>
							<option label="Female" value="F">Female</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label">Title:</td>
					<td><input class="textbox" type="" name="title" value="Hooggeëerd"  /></td>
				</tr>
				<tr>
					<td class="label">Phone:</td>
					<td><input class="textbox" type="" name="home_phone" value="0900 - HOME FOON"  /></td>
				</tr>
				<tr>
					<td class="label">Fax:</td>
					<td><input class="textbox" type="" name="fax" value="0900 - FAXI FAXI"  /></td>
				</tr>
				<tr>
					<td class="label">Cell phone:</td>
					<td><input class="textbox" type="" name="cellular" value="06 - CELL ULAR"  /></td>
				</tr>
				<tr>
					<td class="label">Address:</td>
					<td><input class="textbox" type="" name="address" value="Adresstraat"  /></td>
				</tr>
				<tr>
					<td class="label">Address no:</td>
					<td><input class="textbox" type="" name="address_no" value="12a"  /></td>
				</tr>
				<tr>
					<td class="label">Zip code:</td>
					<td><input class="textbox" type="" name="zip" value="9083 SD"  /></td>
				</tr>
				<tr>
					<td class="label">City:</td>
					<td><input class="textbox" type="" name="city" value="Rommeldam"  /></td>
				</tr>
				<tr>
					<td class="label">State:</td>
					<td><input class="textbox" type="" name="state" value="Drenthe"  /></td>
				</tr>
				<tr>
					<td class="label">Country:</td>
					<td><input class="textbox" type="" name="country" value="NL"  /></td>
				</tr>
				<tr>
					<td class="label">Department:</td>
					<td><input class="textbox" type="" name="department" value="Voltooide Partitie (Af Deling)"  /></td>
				</tr>
				<tr>
					<td class="label">Work phone:</td>
					<td><input class="textbox" type="" name="work_phone" value="0900 - WERK FOON"  /></td>
				</tr>
				<tr>
					<td class="label">Work fax:</td>
					<td><input class="textbox" type="" name="work_fax" value="0900 - WERK FAXI"  /></td>
				</tr>
				<tr>
					<td class="label">Comments:</td>

					<td><textarea class="textbox" name="comment[Opmerking]" ></textarea></td>
				</tr>
				<tr>
					<td class="label">Customfield Col 2:</td>
					<td><input class="textbox" name="col_2" value="COL 2" /></td>
				</tr>
				<tr>
					<td class="label">Customfield Col 3:</td>
					<td><input class="textbox" name="col_3" value="COL 3" /></td>
				</tr>
				<tr>
					<td class="label">Customfield Col 5:</td>
					<td><input class="textbox" name="col_5" value="COL 5" /></td>
				</tr>
				<tr>
					<td class="label">File attachment:</td>
					<td><input type="file" name="attachment1" /></td>
				</tr>
				<tr>
					<td class="label">Foto:</td>
					<td><input type="file" name="photo" /></td>
				</tr>
				<tr>
					<td colspan="2">
						<label for="email_allowed"><input id="email_allowed" type="checkbox" name="email_allowed" style="vertical-align: middle;" />I'd like to recieve newsletters</label>
					</td>
				</tr>

				<?php



//				require_once($GO_CONFIG->class_path.'smarty/Smarty.class.php');
//				$smarty = new Smarty();
//
//				require_once($GLOBALS['GO_MODULES']->modules['cms']['path'].'smarty_plugins/function.html_input.php');
//				require_once($GO_CONFIG->class_path.'smarty/plugins/function.html_options.php');
//
//				require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'].'customfields.class.inc.php');
//				$cf = new customfields();
//
//				$fields = $cf->get_authorized_fields(1,5);
//
//				for($i=0;$i<count($fields);$i++) {
//
//					$fieldparams=array(
//						'name'=>$fields[$i]['dataname'],
//						'class'=>isset($params['class']) ? $params['class'] : ''
//					);
//
//					echo '<tr><td class="label">'.$fields[$i]['name'].':</td><td>';
//
//					require_once($GO_MODULES->modules['recruity']['class_path'].'recruity.class.inc.php');
//							$r = new recruity();
//
//					switch($fields[$i]['datatype']) {
//						case 'vakgebied':
//							$fields[$i]['options']=array();
//							$fields[$i]['multiple']='yes';
//
//							foreach($r->fielddata['vakgebieden'] as $opleiding){
//								$fields[$i]['options'][]=array($opleiding);
//							}
//							$fields[$i]['datatype']='select';
//
//						break;
//						case 'opleiding':
//
//							$fields[$i]['options']=array();
//							
//
//							foreach($r->fielddata['opleidingen'] as $opleiding){
//								$fields[$i]['options'][]=array($opleiding);
//							}
//							$fields[$i]['datatype']='select';
//
//
//							break;
//					}
//
//					switch($fields[$i]['datatype']) {
//						case 'select':
//
//							$fieldparams['options']=array();
//							foreach($fields[$i]['options'] as $option) {
//								$fieldparams['options'][$option[0]]=$option[0];
//							}
//
//							$fieldparams['selected'] = isset($_POST[$fieldparams['name']]) ? ($_POST[$fieldparams['name']]) : $fieldparams['name'];
//							if(isset($fields[$i]['multiple']))
//								$fieldparams['multiple']=$fields[$i]['multiple'];
//
//
//							echo smarty_function_html_options($fieldparams, $smarty);
//							break;
//						default:
//							echo smarty_function_html_input($fieldparams, $smarty);
//							break;
//					}
//
//					echo '</td></tr>';
//				}
				?>

				<tr>
					<td></td>
					<td>
						<input type="submit" value="Send" />
					</td>
				</tr>
			</table>

		</form>

	</body>
</html>