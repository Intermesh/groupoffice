<?php

// this example shown how to upload a newsletter message
// This is a 3-step process
// 1. We upload the zip file to the server an obtain a receipt called a "blobId"
// 2. We create an email template and pass this "blobId" so it know were to find the file
// 3. This email template can then be used to send the email. @see newsletter-email.php for an example using the 'subject','body', and 'attachments propertyies obtained in this examples

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);


$apiKey = "63e601fab29f86187a7af84898b3e0d93bae150b209a2";
function fetch($data) {
    global $apiKey;
	//Adjust these variables for your installation
	$apiUrl = 'http://go.localhost/groupoffice/www/api/jmap.php';

	$dataStr = json_encode($data);

	// Make POST request with curl
	$ch = curl_init($apiUrl);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json; charset=utf-8',
			"Authorization: Bearer " . $apiKey,
			'Content-Length: ' . strlen($dataStr)
	]);

	$result = curl_exec($ch);

	//check for request error.
	if (!$result) {
		die("Failed to send request!" . curl_error($ch));
	}

	return json_decode($result, true);
}

// upload using JS?
if(isset($_POST['blobId'])) {

//Create JMAP request body
	$data = [
		["EmailTemplate/fromZip",
			[
				"blobId" => $_POST['blobId'],
				"subject" => "Hello world!",
                "module" => "newsletters",
                "package" => "business"
			],
			"clientCallId-1"
		]
	];

//	echo "<pre>";
//	var_dump($data);
//	exit();

	$responses = fetch($data);

// Uncomment to inspect API response
//	echo "<pre>";
//	var_dump($responses);
//	echo "</pre>";

    $template = $responses[0][1]; //	['subject', 'attachments', 'body'];

}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Group-Office API Example</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script>
		function selectFile() {
			const input = document.createElement("input");
			input.setAttribute("type", "file");
			input.addEventListener("change", function (e) {
				// File is selected. Show a loading indicator and upload it to the server:
				upload(this.files[0]);
			});
			input.setAttribute('accept', 'application/zip');
			input.click();
		}
		 function upload(file) {
			  fetch('http://go.localhost/groupoffice/www/api/upload.php', {
				  method: 'POST',
				  headers: {
					  "Authorization": "Bearer <?=$apiKey?>",
                    'X-File-Name': "UTF-8''" + encodeURIComponent(file.name),
                    'Content-Type': file.type,
                    'X-File-LastModified': Math.round(file['lastModified'] / 1000).toString()
				  },
				  body: file
			  }).then(response => response.json())
				  .then( data => handleResponse(data))
				  .catch( error => console.log(error));
		  }
		  function handleResponse(data) {
            // the data contains the blobID when the file is uploaded successfull
             // we post this data to ourself so curl can send it to api.php
             // for this example we add the blobID to a text field and have the viewer post it manually. however, this doesn't need user interaction
             document.getElementById('blobId').value = data.blobId;
          }
	</script>
</head>
<body class="bg-light">
<div class="container">
	<div class="py-5 text-center">
		<h2>Newsletter upload example</h2>

        <?php if(!isset($template)): ?>

		<button class="btn btn-primary btn-lg btn-block" type="submit" onclick="selectFile()">Upload ZIP</button>
<br>
            <hr class="mb-4">
        <h4 class="mb-3">After upload is successful this ID is obtained</h4>
        <form class="needs-validation" method="POST">
        <div class="mb-3">
            <label for="blobId">Blob ID <span class="text-muted">(Upload file)</span></label>
            <input type="text" class="form-control" id="blobId"  name="blobId">
        </div>

         <button class="btn btn-primary btn-lg btn-block" type="submit">Submit</button>
        </form>

         <?php else: ?>

        <form class="needs-validation" method="POST">
            <div class="mb-3">
                <label for="subject">Subject</label>
                <input type="text" class="form-control" id="subject"  name="subject" value="<?=$template['subject']?>">
            </div>

            <div id="email_body"></div>

            <button class="btn btn-primary btn-lg btn-block" type="button" onclick="alert('See send-newsletter.php')">Send</button>
        </form>
        <script>
            const div = document.getElementById('email_body');
				div.attachShadow({ mode: "closed" }).innerHTML = <?=json_encode($template['body'])?>;
        </script>
        <?php endif; ?>
	</div>
</div>
</body>
</html>
