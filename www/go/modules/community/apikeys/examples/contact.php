<?php
//This example creates a contact on a Group-Office installation and sends
//an email to notify the system admin.

//NOTE: A custom field 'newsletter' of type checkbox is used in this example. So
//create that for testing.

//Adjust these variables for your installation
$apiKey = "5c9a88c3a81c66f19e39c2753a3d69c24850aa18f64b3";
$apiUrl = 'http://localhost/api/jmap.php';


// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] == "POST") {
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
					'street' => $_POST['street'],
					'street2' => $_POST['street2'],
					'city' => $_POST['city'],
					'zipCode' => $_POST['zipCode'],
					'country' => $_POST['country']
			]
		];
	
	//Create custom field 'subscribe' to use this.
	$contact['customFields']['newsletter'] = isset($_POST['subscribe']);

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
					"body" =>  "Name: " .$_POST['firstName']. " ". $_POST['lastName'],
					//"to" => ["admin@intermesh.localhost" => "Admin"] //Optional. If empty it will be sent to the system settings email.
			],
			"clientCallId-1"]
	];
	
	
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
	if(isset($responses[0][1][0]) && $responses[0][1][0] == "error") {
		$error = "Error: " . $responses[0][1][1]['message'];
	} else	if (!empty($responses[0][1]['notCreated'])) {
		$error = "Error: " . var_export($responses[0][1]['notCreated']['contact-1']['validationErrors'], true);
	} else if(empty($responses[0][1]['created'])) {
		$error = "Error: " . var_export($responses, true);
	} else
	{
		$success = "Thank you! We received your contact information.";
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
		 
    <p class="lead">Below is an example form built entirely with Bootstrap’s form controls. Each required form group has a validation state that can be triggered by attempting to submit the form without completing it.</p>
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
            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="" value="" required="">
            <div class="invalid-feedback">
              Valid first name is required.
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label for="lastName">Last name</label>
            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="" value="" required="">
            <div class="invalid-feedback">
              Valid last name is required.
            </div>
          </div>
        </div>

       

        <div class="mb-3">
          <label for="email">Email <span class="text-muted">(Optional)</span></label>
          <input type="email" class="form-control" id="email"  name="email" placeholder="you@example.com">
          <div class="invalid-feedback">
            Please enter a valid email address for shipping updates.
          </div>
        </div>
				
				<div class="mb-3">
          <label for="homePhone">Home phone <span class="text-muted">(Optional)</span></label>
          <input type="homePhone" class="form-control" id="homePhone"  name="homePhone">
         
        </div>
				
				<div class="mb-3">
          <label for="mobilePhone">Mobile phone <span class="text-muted">(Optional)</span></label>
          <input type="mobilePhone" class="form-control" id="mobilePhone"  name="mobilePhone">
         
        </div>

        <div class="mb-3">
          <label for="street">Address</label>
          <input type="text" class="form-control" id="street" name="street" placeholder="1234 Main St" required="">
          <div class="invalid-feedback">
            Please enter your shipping address.
          </div>
        </div>

        <div class="mb-3">
          <label for="street2">Address 2 <span class="text-muted">(Optional)</span></label>
          <input type="text" class="form-control" id="street2" name="street2"  placeholder="Apartment or suite">
        </div>
				
				<div class="mb-3">
          <label for="city">City</label>
          <input type="text" class="form-control" id="city" name="city"  placeholder="">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="country">Country</label>
            <select class="custom-select d-block w-100" id="country" name="country"  required="">
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

