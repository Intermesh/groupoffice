<?php
//This example creates a contact on a Group-Office installation, subscribes it to a newsletter and sends
//an email to notify the system admin.

//Adjust these variables for your installation
$apiKey = "61bb0ed6515d2da17c95e073a0458bdb4421273784cc3";
$apiUrl = 'http://localhost/api/jmap.php';

// make sure this list exists in Group-Office
$addressListId = 1;

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] == "POST") {
	//Handle form POST


	//Create JMAP request body
	$data =
		[
			[
				"Module/set",
				[
					"update" => [
						"1" => [
							"settings" => [
								"maintenanceMode" => true
							]
						]
					]
				],
				"clientCallId-23"
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

    $success = $responses[0][1]['updated'][1]['settings']['maintenanceMode'] ??  false;

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Group-Office API Example</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container">
    <div class="py-5 text-center">
        <h2>Contact form</h2>

			<?php
			if (!empty($success)) {
				?>
          <div class="alert alert-success">
						Maintenance mode enabled
          </div>
				<?php
			}else{
				?>
          <div class="alert alert-danger">
              Could not enable maintenance mode<br />

             <pre>
                <?php	var_dump($responses); ?>
             </pre>
          </div>
				<?php
			}
			?>

        <p class="lead">Click the button to enable maintenance mode.</p>

        <form class="needs-validation" method="POST">
            <button class="btn btn-primary btn-lg btn-block" type="submit">Enable</button>
        </form>
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

