<?php
//Adjust these variables for your installation
$apiKey = "5fdfc8b07892f471c5a63cb7de9e698df049a199155c2";
$apiUrl = 'http://localhost/jmap.php';
$domain = 'example.local';


// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] == "POST") {
	//Handle form POST
	
	$name = $_POST['name'];
	$hostname = $name . '.' . $domain;

	//check if name is valid
	if (!preg_match('/^[a-z0-9-_]*$/', $name)) {
		die("Invalid characters in name");
	}

	//Create JMAP request body
	$data = [
			["Instance/set", 
					[
							"create" => [
									"clientId-1" => [
											"hostname" => $hostname, 
											"isTrial" => true, 
											"storageQuota" => 1024 * 1024 * 1024, //In bytes
											"usersMax" => 0, //0 for infinite
											"welcomeMessage" => "Welcome to Group-Office" //this HTML message will display on the start page
											]
							]
					],
				"clientCallId-1"
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

	//check for API error. More details on http://jmap.io
	if (!empty($responses[0][1]['notCreated'])) {
		die("Error: " . $responses[0][1]['notCreated']['clientId-1']['validationErrors']['hostname']['description']);
	}

	//All went well so redirect to installer.
	header("Location: https://" . $hostname . "/install/install.php");
	exit();
}
?>


<html>

	<body>

		<form method="POST">					
			<fieldset>
				<legend class="fieldset-header">Create your Group-Office instance now</legend>
				<p>Try it free of charge for 30 days. Fill out this form and click on 'Submit'.</p>

				<div>
					<label for="Hosting_name">Name:</label>								
					<span>https://</span>
					<input style="display:inline-block; width: 200px;" required="true" name="name"  type="text" value="">
					<span>.<?php echo $domain; ?></span>
				</div>


				<input type="submit" value="Create instance">			

			</fieldset>
		</form>
	</body>
</html>
