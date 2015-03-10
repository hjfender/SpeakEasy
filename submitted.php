<html>
<head><title>Submitted Form</title></head>
<body>
<?php

	function insertData($first, $last, $email)
	{
		$serverfirst = "localhost";
		$userfirst = "macse_form";
		$password = "phpdataentry";
		$dbfirst = "macse_datacollection";

		// Create connection
		$conn = new mysqli($serverfirst, $userfirst, $password,$dbfirst);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 
		echo "<br />";
		$emailQuery = "SELECT `email` FROM `initialcollection` WHERE `email` LIKE '$email'";
		$sql = "INSERT INTO initialcollection (first,last,email) VALUES ('$first','$last', '$email')";
		if($conn ->query($emailQuery)->num_rows > 0){
			echo "Email already registered";
		} else if ($conn->query($sql) === TRUE) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		$conn -> close();
	}
	
	//firsts may not contain numbers
	function isValidName($name)
	{
		return "" !=== $name &&!preg_match("/[0-9]+/", $name);
	}
	
	//Email must end in @macalester.edu
	function isValidEmail($email)
	{
		return strpos($email,"@macalester.edu") == strlen($email)-15;
	}
	
	//Clean a string for MYSQL
	function clean($toClean)
	{
		$toClean = str_replace('"','',$toClean);
		$toClean = str_replace("'","",$toClean);
		return $toClean;
	}
	
	//Called when an invalid first name is entered
	function invalidFirstName($userFirst)
	{
		
	}
	
	//Called when an invalid first name is entered
	function invalidLastName($userFirst)
	{
		
	}
	
	//Called when an invalid first name is entered
	function invalidEmail($userFirst)
	{
		
	}
	//Called when no or not enough data is received
	function noDataSent() 
	{
		
	}
	
	//Check to make sure first and email sent
	if(isset($_POST['first']) && isset($_POST['last']) && isset($_POST['email']))	{
		//Load userfirst and email
		$userFirst = clean(htmlspecialchars($_POST['first']));
		$userLast = clean(htmlspecialchars($_POST['last']));
		$userEmail = clean(htmlspecialchars($_POST['email']));
		//Verify the data is valid
		$validInput = TRUE;
		if(!isValidfirst($userFirst)) {
			$validInput = FALSE;
			invalidFirstName($userFirst);
		} 
		if(!isValidFirst($userLast)) {
			$validInput = FALSE;
			invalidFirstName($userFirst);
		}
		if(!isValidEmail($userEmail)) {
			$validInput = FALSE;
			invalidFirstName($userFirst);
		}
		if($validInput)	{
			insertData($userFirst,$userLast,$userEmail);
		}
	} else {
		noDataSent();
	}
?>
</body>
</html>