<?php
//This example shows how to send an e-mail with attachments

//Adjust these variables for your installation
$apiKey = 'your-api-key';
$baseUrl = 'http://host.docker.internal/';

$uploadUrl = $baseUrl . '/api/upload.php';

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$attachments = [];
	// If any files have been attached, upload them first
	if (isset($_FILES['attachments']) && count($_FILES['attachments'])) {

		foreach ($_FILES['attachments']['name'] as $key => $attachment) {
            if(empty($_FILES['attachments']['tmp_name'][$key])) {
                continue;
            }
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

            $attachments[] = ['fileName' => $blob['name'], 'blobId' => $blob['id']];

		}
	}



	$message = [
		"priority" => 3, // 1 = high, 3 normal, 5= low
		"notification" => false, // request read notification
		"draft_uid" => 0, // Draft UID when sending

		"reply_uid" => 0, // WHen replying to another message set uid and mailbox
		"reply_mailbox" => "",
		"in_reply_to" => "", // Message ID of reply

		"forward_uid" => 0, // When forwarding a mail mark this as forwarded
		"forward_mailbox" => "",


		"encrypt_smime" => false,
		"sign_smime" => false,


		"to" => $_POST['to'],
		"cc" => "",
		"bcc" => "",
		"subject" => "Test API",
		"alias_id" => $_POST['from_alias_id'],
		"content_type" => "text", // html or text
		"attachments" => json_encode($attachments),
		"inlineAttachments" => [],
		"htmlbody" => '', //set this if content type is "html"
        "plainbody" => $_POST['body'] //set this if content type is "text"
	];


	$ch = curl_init($baseUrl . '/index.php?r=email/message/send'); // Old framework! Will change to JMAP someday
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $apiKey
		)
	);

	$result = curl_exec($ch);

    $result = json_decode($result, true);

    if($result['success']) {
        $success = "Message was sent!";
    } else {
        $error = $result['feedback'];
    }





}


function getFromAliases()  {
	global $baseUrl, $apiKey;
	//obtain from aliases

	$ch = curl_init($baseUrl . '/index.php?r=email/alias/store'); // Old framework! Will change to JMAP someday
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $apiKey
		)
	);

	$result = curl_exec($ch);

	if(!$result) {
		die("Error: Could not get e-mail aliases");
	}

	$fromAliases = json_decode($result, true);

	if(!$fromAliases['success']) {
		die("Error: ".$fromAliases['feedback']);
	}

	return $fromAliases['results'];
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
    <form class="needs-validation" method="POST" enctype="multipart/form-data">

    <div class="row">
		<div class="col-md-8 order-md-1">


            <div class="row">

                <div class="col-md-12">
                    <label for="to">From</label>
                    <?php $aliases = getFromAliases(); ?>
                    <select class="form-control" id="to" name="from_alias_id" value="<?= $aliases[0]['id'] ?? "" ?>">
                        <?php
                        foreach($aliases as $alias) {
                            ?>
                            <option value="<?=$alias['id']; ?>"><?= $alias['name'] ?>: <?= $alias['email'] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">

                <div class="col-md-12">
                    <label for="to">To</label>
                    <input type="email" class="form-control" id="to" name="to" placeholder="you@example.com" value="test@intermesh.localhost">
                    <div class="invalid-feedback">
                        Please enter a valid email address for shipping updates.
                    </div>
                </div>
            </div>
            <div class="row">
                <div  class="col-md-12">
                    <label for="body">Message</label>
                    <textarea class="form-control" id="body" name="body">This is the body</textarea>
                </div>
            </div>



				<div class="row">
					<div class="col-md-12 mb-3">
						<label class="form-label" for="attachments">Attach one or more files</label>
						<input type="file" class="form-control" id="attachments" name="attachments[]" multiple />
					</div>
				</div>
				<hr class="mb-4">



				<button class="btn btn-primary btn-lg btn-block" type="submit">Submit</button>

		</div>
	</div>
    </form>
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

