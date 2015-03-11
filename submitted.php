<html>
<head><title>Submitted Form</title></head>
<body>
<?php
	$firstNameErr = "";
	$lastNameErr = "";
	$emailErr = "";
	
	$redirectPage = "thanks.html";
	
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
		$firstNameErr = "Invalid First Name";
		echo "something";
	}
	
	//Called when an invalid first name is entered
	function invalidLastName($userFirst)
	{
		$lastNameErr = "Invalid Last Name";
	}
	
	//Called when an invalid first name is entered
	function invalidEmail($userFirst)
	{
		$emailErr = "Invalid email. Must end in '@macalester.edu'.";
	}
	//Called when no or not enough data is received
	function noDataSent() 
	{
		$emailErr = "Please Fill All Fields";
	}
	
	//Check to make sure first and email sent
	if(isset($_POST['first']) && isset($_POST['last']) && isset($_POST['email']))	{
		//Load userfirst and email
		$userFirst = clean(htmlspecialchars($_POST['first']));
		$userLast = clean(htmlspecialchars($_POST['last']));
		$userEmail = clean(htmlspecialchars($_POST['email']));
		//Verify the data is valid
		$validInput = TRUE;
		if(!isValidName($userFirst)) {
			$validInput = FALSE;
			invalidFirstName($userFirst);
		} 
		if(!isValidName($userLast)) {
			$validInput = FALSE;
			invalidFirstName($userFirst);
		}
		if(!isValidEmail($userEmail)) {
			$validInput = FALSE;
			invalidFirstName($userFirst);
		}
		if($validInput)	{
			insertData($userFirst,$userLast,$userEmail);
		} else{
			$redirectPage = "Homepage.html";
		}
	} else {
		noDataSent();
		$redirectPage = "Homepage.html";
	}
?>

<form id = "err_form" method = "post" action = "<?php echo $redirectPage ?>">
	 <input type = "text" value = "<?php echo $firstNameErr; ?>" id = "firstNameErr" >
	 <input type = "text" value = "<?php echo $lastNameErr; ?>" id = "lastNameErr" >
	 <input type = "text" value = "<?php echo $emailErr; ?>" id = "emailErr" >
</form>

<script type="text/javascript">
	document.getElementById("err_form").submit(); // Here formid is the id of your form
</script>
	
</body>
</html>