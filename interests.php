<!DOCTYPE html>
<html>
<head lang="en">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="main.css"/>
    <meta charset="UTF-8">
    <title>Interests</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/icon" />
</head>
<body>
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
    <?php
	if(isset($_POST['email'])) {
		$begin = '<div class="container">
        <h1>Please select the topics below that you would be interested in talking about:</h1>
		<form action="thanks.php" method="post">';
		$formBegin = "<input type='hidden' name='email' value='".$_POST['email']."'>";
		$htmlToDisplay = file_get_contents("interests_form.html");
		$htmlToDisplay = $begin.$formBegin.$htmlToDisplay;
		echo $htmlToDisplay;
	} else {
		$htmlToDisplay = readfile("interests_error.html");
	}
	?>
</div>

</body>
</html>