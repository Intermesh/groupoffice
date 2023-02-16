<?php
//This example creates a contact on a Group-Office installation, subscribes it to a newsletter and sends
//an email to notify the system admin.

//Adjust these variables for your installation
$apiKey = "63e601fab29f86187a7af84898b3e0d93bae150b209a2";
$apiUrl = 'http://go.localhost/groupoffice/www/api/jmap.php';

// make sure this list exists in Group-Office
$addressListId = 1;

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] == "POST") {
	//Handle form POST


	// This is the mailing list ID. You can find it in the `newsletters_addresslist` database table
	$addressListId = 62;

	//Create JMAP request body
	$data = [
		['business/newsletters/Subscription/unsubscribe', [
          "addressListId" => $addressListId,
          "email" => $_POST['email']
        ], "clientCallId-1"
        ]
	];

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

	$responses = json_decode($result, true);

	// Uncomment to inspect API response
//	echo "<pre>";
//	var_dump($responses);
//	echo "</pre>";

	//check for API error. More details on http://jmap.io
    $setResponse = $responses[0];
    if (isset($setResponse[0]) && $setResponse[0] == "error") {
        $error = "Error: " . $setResponse[1]['message'];
    } else {
        $success = "You are unsubscribed from the mailing list.";
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
		<h2>Unsubscribe</h2>

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

        <p class="lead">Enter your email address to unsubscribe from the list.</p>

        <form class="needs-validation" method="POST">

            <!-- You can specify an address book by ID by uncommenting the following input tag. If you don't specify it the default address book of the admin user will be used. -->
            <!-- <input type="hidden" name="addressBookId" value="1" /> -->

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="" value="" required="">
                    <div class="invalid-feedback">
                        Valid email is required.
                    </div>
                </div>
            </div>
            <button class="btn btn-primary btn-lg btn-block" type="submit">Submit</button>
        </form>

	</div>

</div>

</body>
</html>