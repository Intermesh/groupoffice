<?php
//This example updates a contact on a Group-Office installation

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);
function fetch($data) {
    //Adjust these variables for your installation
	$apiKey = "63e601fab29f86187a7af84898b3e0d93bae150b209a2";
	$apiUrl = 'http://go.localhost/groupoffice/www/api/jmap.php';

	$dataStr = json_encode($data);

	// Make POST request with curl
	$ch = curl_init($apiUrl);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			"Authorization: Bearer " . $apiKey,
			'Content-Length: ' . strlen($dataStr))
	);

	$result = curl_exec($ch);

	//check for request error.
	if (!$result) {
		die("Failed to send request!" . curl_error($ch));
	}

	return json_decode($result, true);
}



// The contact ID is usually already known or obtained after first using Contact/query and Contact/get
// in this example it is hard coded to "1"
$contactId = 5538;

$contact = null;

if ($_SERVER['REQUEST_METHOD'] == "GET") {

	$data = [
		["Contact/get",
			[
				"ids" => [$contactId]
			],
			"clientCallId-1"
		]
	];
	// not a POST request so we fetch the existing contact
	$responses = fetch($data);

    $contact = $responses[0][1]['list'][0];

} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
	//Handle form POST

	//Build contact object
	$contact = [
		'firstName' => $_POST['firstName'],
		'lastName' => $_POST['lastName']
	];

	if(isset($_POST['addressBookId'])) {
		$contact['addressBookId'] = $_POST['addressBookId'];
	}

	if(!empty($_POST['email'])) {
		$contact['emailAddresses'] = [['email' => $_POST['email']]];
	}

	$contact['phoneNumbers'] = [];
	if(!empty($_POST['homePhone'])) {
		$contact['phoneNumbers'][] = ['type'=>'home', 'number' => $_POST['homePhone']];
	}

	if(!empty($_POST['mobilePhone'])) {
		$contact['phoneNumbers'][] = ['type'=>'mobile', 'number' => $_POST['mobilePhone']];
	}

	$contact['addresses'] = [
		[
			'address' => $_POST['address'],
			'city' => $_POST['city'],
			'zipCode' => $_POST['zipCode'],
			'country' => $_POST['country']
		]
	];


	//Create JMAP request body
	$data = [
		["Contact/set",
			[
				"update" => [
					$contactId => $contact
				]
			],
			"clientCallId-1"
		],
		["core/Notify/mail", [
			"subject" => "Contact changed from website",
			"body" =>  "Name: " .$_POST['firstName']. " ". $_POST['lastName'],
			//"to" => ["admin@intermesh.localhost" => "Admin"] //Optional. If empty it will be sent to the system settings email.
		], "clientCallId-1"]
	];

//	echo "<pre>";
//	var_dump($data);
//	exit();

    $responses = fetch($data);

	// Uncomment to inspect API response
//	echo "<pre>";
//	var_dump($responses);
//	echo "</pre>";

	//check for API error. More details on http://jmap.io
	if(isset($responses[0][1][0]) && $responses[0][1][0] == "error") {
		$error = "Error: " . $responses[0][1][1]['message'];
	} else	if (!empty($responses[0][1]['notUpdated'])) {
		$error = "Error: " . var_export($responses[0][1]['notUpdated'][$contactId]['validationErrors'], true);
	} else if(empty($responses[0][1]['updated'])) {
		$error = "Error: " . var_export($responses, true);
	} else {
		$success = "Thank you! We updated your contact information.";
	}

}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Group-Office API Example</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container">
	<div class="py-5 text-center">
		<h2>Contact form</h2>

		<?php
		if(isset($success)) {
			?>
			<div class="alert alert-success">
				<?= $success; ?>
			</div>
			<?php
		}
		?>

		<?php
		if(isset($error)) {
			?>
			<div class="alert alert-danger">
				<?= $error; ?>
			</div>
			<?php
		}
		?>
        <?php if(isset($contact['name'])): ?>
		<p class="lead">Below we are editing contact: <?=$contact['name']?></p>
        <?php endif; ?>
	</div>

	<div class="row">

		<div class="col-md-8 order-md-1">
			<h4 class="mb-3">Billing address</h4>
			<form class="needs-validation" method="POST">

				<!-- You can specify an address book by ID by uncommenting the following input tag. If you don't specify it the default address book of the admin user will be used. -->
				<!-- <input type="hidden" name="addressBookId" value="1" /> -->

				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="firstName">First name</label>
						<input type="text" class="form-control" id="firstName" name="firstName" placeholder="" value="<?=$contact['firstName']?>" required="">
						<div class="invalid-feedback">
							Valid first name is required.
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<label for="lastName">Last name</label>
						<input type="text" class="form-control" id="lastName" name="lastName" placeholder="" value="<?=$contact['lastName']?>" required="">
						<div class="invalid-feedback">
							Valid last name is required.
						</div>
					</div>
				</div>



				<div class="mb-3">
					<label for="email">Email <span class="text-muted">(Optional)</span></label>
					<input type="email" class="form-control" id="email"  name="email" placeholder="you@example.com" value="<?=isset($contact['emailAddresses'][0]) ? $contact['emailAddresses'][0]['email'] : ''?>">
					<div class="invalid-feedback">
						Please enter a valid email address for shipping updates.
					</div>
				</div>

				<div class="mb-3">
					<label for="homePhone">Home phone <span class="text-muted">(Optional)</span></label>
					<input type="homePhone" class="form-control" id="homePhone"  name="homePhone" value="<?=isset($contact['phoneNumbers'][0]) ? $contact['phoneNumbers'][0]['number'] : ''?>">

				</div>

				<div class="mb-3">
					<label for="mobilePhone">Mobile phone <span class="text-muted">(Optional)</span></label>
					<input type="mobilePhone" class="form-control" id="mobilePhone"  name="mobilePhone" value="<?=isset($contact['phoneNumbers'][1]) ? $contact['phoneNumbers'][1]['number'] : ''?>">

				</div>

				<div class="mb-3">
					<label for="address">Address</label>
					<input type="text" class="form-control" id="address" name="address" placeholder="1234 Main St" value="<?=isset($contact['addresses'][0]) ? $contact['addresses'][0]['address'] : ''?>" required="">
					<div class="invalid-feedback">
						Please enter your shipping address.
					</div>
				</div>


				<div class="mb-3">
					<label for="city">City</label>
					<input type="text" class="form-control" id="city" name="city"  placeholder="" value="<?=isset($contact['addresses'][0]) ? $contact['addresses'][0]['city'] : ''?>">
				</div>

				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="country">Country</label>
						<select class="custom-select d-block w-100" id="country" name="country"required="">
							<option value="">Choose...</option>
							<option value="UK" <?=isset($contact['addresses'][0]) && $contact['addresses'][0]['countryCode']=='UK' ?  'selected' : ''?>>United Kingdom</option>
                            <option value="NL" <?=isset($contact['addresses'][0]) && $contact['addresses'][0]['countryCode']=='NL' ?  'selected' : ''?>>The Netherlands</option>
                            <option value="DE" <?=isset($contact['addresses'][0]) && $contact['addresses'][0]['countryCode']=='DE' ?  'selected' : ''?>>Germany</option>
						</select>
						<div class="invalid-feedback">
							Please select a valid country.
						</div>
					</div>

					<div class="col-md-6 mb-3">
						<label for="zipCode">Zip</label>
						<input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="" value="<?=isset($contact['addresses'][0]) ? $contact['addresses'][0]['zipCode'] : ''?>" required="">
						<div class="invalid-feedback">
							Zip code required.
						</div>
					</div>
				</div>

				<hr class="mb-4">

				<button class="btn btn-primary btn-lg btn-block" type="submit">Submit</button>
			</form>
		</div>
	</div>

	<footer class="my-5 pt-5 text-muted text-center text-small">
		<p class="mb-1">© 2017-2019 Company Name</p>
		<ul class="list-inline">
			<li class="list-inline-item"><a href="#">Privacy</a></li>
			<li class="list-inline-item"><a href="#">Terms</a></li>
			<li class="list-inline-item"><a href="#">Support</a></li>
		</ul>
	</footer>
</div>



</body>
</html>

