<?php
//This example creates a contact on a Group-Office installation and sends
//an email to notify the system admin.

//NOTE: A custom field 'newsletter' of type checkbox is used in this example. So
//create that for testing.

//Adjust these variables for your installation
$apiKey = 'your-api-key';
$baseUrl = 'http://host.docker.internal:8000/api/';
$apiUrl = $baseUrl . 'jmap.php';
$uploadUrl = $baseUrl . 'upload.php';

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$attachments = [];
	// If any files have been attached, upload them first
	if (isset($_FILES['attachments']) && count($_FILES['attachments'])) {

		foreach ($_FILES['attachments']['name'] as $key => $attachment) {
			$cFile = new CURLFile($_FILES['attachments']['tmp_name'][$key], $_FILES['attachments']['type'][$key], $_FILES['attachments']['name'][$key]);
			$chf = curl_init($uploadUrl);
			$data = ['test_file' => $cFile];
			curl_setopt($chf, CURLOPT_POST, true);
			curl_setopt($chf, CURLOPT_POSTFIELDS, $data);
			curl_setopt($chf, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($chf, CURLOPT_HTTPHEADER, array(
					"Content-Type: application/json; charset=utf-8",
					"Authorization: Bearer " . $apiKey,
					"Content-Length: " . $_FILES['attachments']['size'][$key],
					"X-File-Name: " . "UTF8''" . $_FILES['attachments']['name'][$key],
//					"COOKIE: XDEBUG_SESSION=PHPSTORM" // Xdebug users may want to use this for debugging
				)
			);

			$result = curl_exec($chf);

			//check for request error.
			if (!$result) {
				die("Failed to send request!" . curl_error($chf));
			}

			$attachments[] = json_decode($result, true);

			// Uncomment to inspect API response
//			echo "<pre>";
//			var_dump($response);
//			echo "</pre>";

		}
	}

	//Build contact object
	$contact = [
		'firstName' => $_POST['firstName'],
		'lastName' => $_POST['lastName'],
	];

	if (isset($_POST['addressBookId'])) {
		$contact['addressBookId'] = $_POST['addressBookId'];
	} else {
		$contact['addressBookId'] = 1; // "Shared"
	}

	if (!empty($_POST['email'])) {
		$contact['emailAddresses'] = [['email' => $_POST['email']]];
	}

	$contact['phoneNumbers'] = [];
	if (!empty($_POST['homePhone'])) {
		$contact['phoneNumbers'][] = ['type' => 'home', 'number' => $_POST['homePhone']];
	}

	if (!empty($_POST['mobilePhone'])) {
		$contact['phoneNumbers'][] = ['type' => 'mobile', 'number' => $_POST['mobilePhone']];
	}

	$contact['addresses'] = [
		[
			'address' => $_POST['address'],
			'city' => $_POST['city'],
			'zipCode' => $_POST['zipCode'],
			'country' => $_POST['country']
		]
	];

	//Create custom field 'subscribe' to use this.
	$contact['customFields']['newsletter'] = isset($_POST['subscribe']);

	// Finally attach the BLOBs
	$contact['attachments'] = $attachments;

	//Create JMAP request body
	$data = [
		["Contact/set",
			[
				"create" => [
					"contact-1" => $contact
				]
			],
			"clientCallId-1"
		],
		["core/Notify/mail", [
			"subject" => "New contact created from website",
			"body" => "Name: " . $_POST['firstName'] . " " . $_POST['lastName'],
//			"to" => ["admin@intermesh.localhost" => "Admin"] //Optional. If empty it will be sent to the system settings email.
		],
			"clientCallId-1"]
	];

	// Uncomment to inspect data
//	echo "<pre>";
//	var_dump($data);
//	exit();


	$dataStr = json_encode($data);

	// Make POST request with curl
	$ch = curl_init($apiUrl);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json; charset=utf-8",
			"Authorization: Bearer " . $apiKey,
			"Content-Length: " . strlen($dataStr),
//			"COOKIE: XDEBUG_SESSION=PHPSTORM" // Uncomment to use XDebug to debug the API call
		)
	);

	$result = curl_exec($ch);

	//check for request error.
	if (!$result) {
		die("Failed to send request!" . curl_error($ch));
	}

	$responses = json_decode($result, true);

	// Uncomment to inspect API response
//	echo "<pre>";
//	var_dump($responses);
//	echo "</pre>";

	//check for API error. More details on https://jmap.io
	if (isset($responses[0][1][0]) && $responses[0][1][0] == "error") {
		$error = "Error: " . $responses[0][1][1]['message'];
	} else if (!empty($responses[0][1]['notCreated'])) {
		$error = "Error: " . var_export($responses[0][1]['notCreated']['contact-1']['validationErrors'], true);
	} else if (empty($responses[0][1]['created'])) {
		$error = "Error: " . var_export($responses, true);
	} else {
		$success = "Thank you! We received your contact information.";
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Group-Office API Example</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
	      integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
	        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
	        crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
	        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct"
	        crossorigin="anonymous"></script>

</head>
<body class="bg-light">
<div class="container">
	<div class="py-5 text-center">
		<h2>Contact form</h2>

		<?php
		if (isset($success)) {
			?>
			<div class="alert alert-success">
				<?= $success; ?>
			</div>
			<?php
		}
		?>

		<?php
		if (isset($error)) {
			?>
			<div class="alert alert-danger">
				<?= $error; ?>
			</div>
			<?php
		}
		?>

		<p class="lead">Below is an example form built entirely with Bootstrap’s form controls. Each required form group
			has a validation state that can be triggered by attempting to submit the form without completing it.</p>
	</div>

	<div class="row">

		<div class="col-md-8 order-md-1">
			<h4 class="mb-3">Billing address</h4>
			<form class="needs-validation" method="POST" enctype="multipart/form-data">

				<!-- You can specify an address book by ID by uncommenting the following input tag. If you don't specify it the default address book of the admin user will be used. -->
				<!-- <input type="hidden" name="addressBookId" value="1" /> -->

				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="firstName">First name</label>
						<input type="text" class="form-control" id="firstName" name="firstName" placeholder="" value=""
						       required="">
						<div class="invalid-feedback">
							Valid first name is required.
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<label for="lastName">Last name</label>
						<input type="text" class="form-control" id="lastName" name="lastName" placeholder="" value=""
						       required="">
						<div class="invalid-feedback">
							Valid last name is required.
						</div>
					</div>
				</div>


				<div class="mb-3">
					<label for="email">Email <span class="text-muted">(Optional)</span></label>
					<input type="email" class="form-control" id="email" name="email" placeholder="you@example.com">
					<div class="invalid-feedback">
						Please enter a valid email address for shipping updates.
					</div>
				</div>

				<div class="mb-3">
					<label for="homePhone">Home phone <span class="text-muted">(Optional)</span></label>
					<input type="homePhone" class="form-control" id="homePhone" name="homePhone">

				</div>

				<div class="mb-3">
					<label for="mobilePhone">Mobile phone <span class="text-muted">(Optional)</span></label>
					<input type="mobilePhone" class="form-control" id="mobilePhone" name="mobilePhone">

				</div>

				<div class="mb-3">
					<label for="address">Address</label>
					<input type="text" class="form-control" id="address" name="address" placeholder="1234 Main St"
					       required="">
					<div class="invalid-feedback">
						Please enter your shipping address.
					</div>
				</div>


				<div class="mb-3">
					<label for="city">City</label>
					<input type="text" class="form-control" id="city" name="city" placeholder="">
				</div>

				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="country">Country</label>
						<select class="custom-select d-block w-100" id="country" name="country" required="">
							<option value="">Choose...</option>
							<option value="UK" selected>United Kingdom</option>
						</select>
						<div class="invalid-feedback">
							Please select a valid country.
						</div>
					</div>

					<div class="col-md-6 mb-3">
						<label for="zipCode">Zip</label>
						<input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="" required="">
						<div class="invalid-feedback">
							Zip code required.
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label" for="attachments">Upload one or more files</label>
						<input type="file" class="form-control" id="attachments" name="attachments[]" multiple />
					</div>
				</div>
				<hr class="mb-4">

				<h4 class="mb-4">Custom field</h4>
				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="exampleCheck1" name="subscribe" value="1">
					<label class="form-check-label" for="exampleCheck1">Subscribe to newsletter</label>
				</div>
				<hr class="mb-4">

				<button class="btn btn-primary btn-lg btn-block" type="submit">Submit</button>
			</form>
		</div>
	</div>

	<footer class="my-5 pt-5 text-muted text-center text-small">
		<p class="mb-1">© 2017-<?php echo (new DateTime())->format("Y"); ?> Company Name</p>
		<ul class="list-inline">
			<li class="list-inline-item"><a href="#">Privacy</a></li>
			<li class="list-inline-item"><a href="#">Terms</a></li>
			<li class="list-inline-item"><a href="#">Support</a></li>
		</ul>
	</footer>
</div>

</body>
</html>

