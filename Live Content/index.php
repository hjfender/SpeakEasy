<!DOCTYPE html>
<html>

<?php
	if(isset($_POST['firstNameErr']) and isset($_POST['lastNameErr']) and isset($_POST['emailErr'])){
		$firstNameErr = $_POST['firstNameErr'];
		$lastNameErr = $_POST['lastNameErr'];
		$emailErr = $_POST['emailErr'];
	} else{
		$firstNameErr = "";
		$lastNameErr = "";
		$emailErr = "";
	}

?>

<head lang="en">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="main.css"/>
    <meta charset="UTF-8">
    <title>SpeakEasy</title>
	<link rel="icon" href="favicon.png" type="image/png">
	
</head>
<body>

<div class = "page-header"><img src="Pictures/LASTLOGO.JPG"/></div>

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
                <li><a href="#home">Home <span class="sr-only">(current)</span></a></li>
                <li><a href="#about">About</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
               <!-- <li><a href="#">Register</a></li>
                <li><a href="#">Login</a></li>-->
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<div class = "jumbotron">
    <div class="container">
        <h1>Welcome to SpeakEasy!</h1>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <p class="center-text">If you'd like to get involved with SpeakEasy, then join us!</p>
            </div>
        </div>
        <!--<p><a class="btn btn-primary btn-lg" href="#" role="button"> Sign Up!</a></p>-->
        <div class="signup">
            <form action="submitted.php" method="post">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <div class="input-group input-group-lg" id="firstName">
                            <input type="text" class="form-control" placeholder="First Name" name = "firstName">
							<span class = "error" style = "color:red"> <?php echo $firstNameErr; ?></span> <!--error message -->
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <div>
                            <div class="input-group input-group-lg" id="lastName">
                                <input type="text" class="form-control" placeholder="Last Name" name = "lastName">
								<span class = "error" style = "color:red"> <?php echo $lastNameErr; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="separator"></div>

                <div class="row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <div class="input-group input-group-lg" id="email">
                            <input type="text" class="form-control" placeholder="E-mail Address" name = "email">
							<span class = "error" style = "color:red"> <?php echo $emailErr; ?></span>
                        </div>
                    </div>
                </div>

                <div class="separator"></div>
                <div class="separator"></div>
                <div class="separator"></div>

                <button class="btn btn-primary btn-lg" href="#" role="button">Sign Me Up!</button>

            </div>
            </form>
        </div>
    </div>
</div>

<div class="about">
    <div class="jumbotron">
        <div class="container">
            <div class="row text-center">
                <h1>About Us</h1>
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <h3>We think there's a better way to connect to people than just profile pictures and a swipe. If you agree and want to join our experiment, sign up above!</h3>
                    </div>
                </div>
            </div>
            <div class="separator"></div>
            <div class="separator"></div>
            <div class="separator"></div>
            <div class="row">
                <div class="col-md-4">
                    <div class="aboutBox">
                        <p>Do you sometimes get the feeling that there are plenty of potential friends on your campus just waiting to be found? Here at SpeakEasy, we feel that your closest friends may be the ones you haven't met yet.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="aboutBox">
                        <p>We know it can be difficult to introduce yourself to new potential friends because let's face it: college students are busy, their social scenes can be cliquey, and sometimes the 'right' moment just never presents itself.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="aboutBox">
                        <p>With our model, SpeakEasy links you to possible new friends on your campus and takes the first few awkward steps out of the equation. These are friendships made easy. SpeakEasy. </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




</body>
</html>
