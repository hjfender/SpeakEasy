<html>
<head><title>Submitted Form</title></head>
<body>
<?php
	$firstNameErr = " ";
	$lastNameErr = " ";
	$emailErr = " ";
	
	$redirectPage = "interests.html";
	
	function insertData($first, $last, $email)
	{
		global $emailErr;
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
			$emailErr = "Email already registered";
			$redirectPage = "Homepage.html";
		} else if ($conn->query($sql) === TRUE) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		$conn -> close();
	}
	
	//Name may not contain numbers
	function isValidName($name)
	{
		return "" !== $name &&!preg_match("/[0-9]+/", $name);
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
		global $firstNameErr;
		$firstNameErr = "Invalid First Name";
	}
	
	//Called when an invalid first name is entered
	function invalidLastName($userLast)
	{
		global $lastNameErr;
		$lastNameErr = "Invalid Last Name";
	}
	
	//Called when an invalid first name is entered
	function invalidEmail($userFirst)
	{
		global $emailErr;
		$emailErr = "Invalid email. Must end in '@macalester.edu'.";
	}
	//Called when no or not enough data is received
	function noDataSent() 
	{
		global $emailErr;
		$emailErr = "Please Fill All Fields";
	}
	
	//Check to make sure first and email sent
	if(isset($_POST['firstName']) && isset($_POST['lastName']) && isset($_POST['email']))	{
		//Load userfirst and email
		$userFirst = clean(htmlspecialchars($_POST['firstName']));
		$userLast = clean(htmlspecialchars($_POST['lastName']));
		$userEmail = clean(htmlspecialchars($_POST['email']));
		//Verify the data is valid
		$validInput = TRUE;
		if(!isValidName($userFirst)) {
			$validInput = FALSE;
			invalidFirstName($userFirst);
		} 
		if(!isValidName($userLast)) {
			$validInput = FALSE;
			invalidLastName($userLast);
		}
		if(!isValidEmail($userEmail)) {
			$validInput = FALSE;
			invalidEmail($userEmail);
		}
		if($validInput)	{
			insertData($userFirst,$userLast,$userEmail);
		} else{
			$redirectPage = "index.php";
		}
	} else {
		noDataSent();
		$redirectPage = "index.php";
	}
	
?>

<form id = "err_form" method = "post" action = "<?php echo $redirectPage ?>">
	 <input type = "text" value = "<?php echo $firstNameErr; ?>" name = "firstNameErr" >
	 <input type = "text" value = "<?php echo $lastNameErr; ?>" name = "lastNameErr" >
	 <input type = "text" value = "<?php echo $emailErr; ?>" name = "emailErr" >
</form>

<script type="text/javascript">
	document.getElementById("err_form").submit(); // Here formid is the id of your form
</script>
	
</body>
</html>