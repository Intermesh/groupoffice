<?php
//This example shows how to upload a file and obtain a blob ID

//Adjust these variables for your installation
$apiKey = 'your-api-key';
$baseUrl = 'http://host.docker.internal/api/';

$uploadUrl = $baseUrl . 'upload.php';

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$blobIds = [];
	// If any files have been attached, upload them first
	if (isset($_FILES['attachments']) && count($_FILES['attachments'])) {

		foreach ($_FILES['attachments']['name'] as $key => $attachment) {
			$chf = curl_init($uploadUrl);
			curl_setopt($chf, CURLOPT_POST, true);
			curl_setopt($chf, CURLOPT_INFILE, fopen($_FILES['attachments']['tmp_name'][$key], 'r'));
			curl_setopt($chf, CURLOPT_INFILESIZE, $_FILES['attachments']['size'][$key]);
			curl_setopt($chf, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($chf, CURLOPT_HTTPHEADER, array(
					"Content-Type: " . $_FILES['attachments']['type'][$key] ,
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

            $info = curl_getinfo($chf);

            if($info['http_code'] != 201) {
                $error = "Error " . $info['http_code'].": ". $result;
              break;
            }



            $blob = json_decode($result, true);
			$blobIds[] = $blob['blobId'];

		}
	}

	//check for API error. More details on https://jmap.io
//	if (isset($responses[0][1][0]) && $responses[0][1][0] == "error") {
//		$error = "Error: " . $responses[0][1][1]['message'];
//	} else if (!empty($responses[0][1]['notCreated'])) {
//		$error = "Error: " . var_export($responses[0][1]['notCreated']['contact-1']['validationErrors'], true);
//	} else if (empty($responses[0][1]['created'])) {
//		$error = "Error: " . var_export($responses, true);
//	} else {
        if(!isset($error)) {
	        $success = "Success: We stored these blob ID's: " . implode(", ", $blobIds);
        }
//	}
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

	</div>

	<div class="row">

		<div class="col-md-8 order-md-1">
			<h4 class="mb-3">Upload files</h4>
			<form class="needs-validation" method="POST" enctype="multipart/form-data">

				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label" for="attachments">Upload one or more files</label>
						<input type="file" class="form-control" id="attachments" name="attachments[]" multiple />
					</div>
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

