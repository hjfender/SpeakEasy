<!DOCTYPE html>
<html>
<head lang="en">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="main.css"/>
    <meta charset="UTF-8">
    <title>Thanks for signing up!</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/icon" />
</head>
<body>
<?php 

	//Clean a string for MYSQL
	function clean($toClean)
	{
		$toClean = str_replace('"','',$toClean);
		$toClean = str_replace("'","",$toClean);
		return $toClean;
	}
	
	
	if(!isset($_POST['email'])) {
		echo "ERROR";
	} else {
		$email = clean($_POST['email']);
		$checkboxvar = $_POST['checkboxvar'];
		$catcher = '0';
		$housing = '0';
		$blank = '0';
		$soccer = '0';
		$science = '0';
		$quizzes = '0';
		$election = '0';
		$catvideos = '0';
		$harrypotter = '0';
		$music = '0';
		
		if(count($checkboxvar) > 0)
		{
			if(in_array('catcher',$checkboxvar)) {
				$catcher = '1';
			}
			if(in_array('blank',$checkboxvar)) {
				$blank = '1';
			}
			if(in_array('soccer',$checkboxvar)) {
				$soccer = '1';
			}
			if(in_array('science',$checkboxvar)) {
				$science = '1';
			}
			if(in_array('quizzes',$checkboxvar)) {
				$quizzes = '1';
			}
			if(in_array('election',$checkboxvar)) {
				$election = '1';
			}	
			if(in_array('catvideos',$checkboxvar)) {
				$catvideos = '1';
			}	
			if(in_array('harrypotter',$checkboxvar)) {
				$harrypotter = '1';
			}	
			if(in_array('music',$checkboxvar)) {
				$music = '1';
			}	
			if(in_array('housing',$checkboxvar)) {
				$housing = '1';
			}
		}
		
		$custom = '';
		if(isset($_POST['custom'])) {
			$custom = clean($_POST['custom']);
		}
		
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
		$sql = "INSERT INTO interestscollection (email,catcher,blank,soccer,science,quizzes,election,catvideos,harrypotter,music,housing,custom) VALUES ('$email',$catcher, $blank,$soccer,$science,$quizzes,$election,$catvideos,$harrypotter,$music,$housing,'$custom')";
		if($conn -> query($sql) !== TRUE)
		{
			echo "Data not logged";
		}
		$conn -> close();
	}
	
	?>
<div class = "page-header"><img src="imgs/LASTLOGO.JPG"/></div>

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">SpeakEasy</a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><a href="index.php">Home <span class="sr-only">(current)</span></a></li>
                <li><a href="#about">About</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
            <!--    <li><a href="#">Register</a></li>
                <li><a href="#">Login</a></li> -->
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
    <div class="jumbotron">
        <div class="container">
            <h1>Thanks so much for signing up for testing! We'll be in touch soon! In the meantime, like us on <a href="http://www.facebook.com/OfficialSpeakEasy">Facebook</a> and follow us on <a href="http://www.twitter.com/MacSpeakEasy">Twitter</a> for more updates.</h1>
        </div>
    </div>
</nav>
</body>
</html>