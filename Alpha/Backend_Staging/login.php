<html>    
    <head>
        <script type="text/javascript">
            //Our form submission function.
            function submitForm() {
                setTimeout(function () {
                    document.getElementById('redirectForm').submit();
                });
            }
            //Call the function submitForm() as soon as the page has loaded.
            window.onload = submitForm;
        </script>
    </head>
    <body>
        <?php
        include 'utils.php';

        echo "Logging in...";
        if (!(isset($_POST['email']) || isset($_POST['password']))) {
            echo "No email or password given";
            exit();
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        //Connect to database
        $conn = connectToDatabase("Speakeasy");

        //Get id or exit if invalid email or password
        $query = "SELECT `id` FROM `profiles` WHERE `email` = '$email' AND `password` = '$password'";
        $results = $conn->query($query);
        if ($results->num_rows == 0) {
            echo "Invalid Email or Password";
            exit();
        }
        $userID = $results->fetch_array()[0];

        //If already logged in destroy that token
        $query = "DELETE FROM `sessions` WHERE `user_id` = '$userID'";
        $conn->query($query);

        //Generate UUID
        $uuid = "";
        $foundUnique = FALSE;
        while (!$foundUnique) {
            $uuid = generateUUID();
            $query = "SELECT `token` FROM `sessions` WHERE `token` = '$uuid' LIMIT 1";
            $results = $conn->query($query);
            if ($results->num_rows == 0) {
                $foundUnique = TRUE;
            }
        }

        //Get user IP Address
        $ipAddress = getClientIP();
        
        //Get the current time
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        //Add the token
        $query = "INSERT INTO `sessions` (`token`,`ip_address`,`user_id`,`created`,`updated`) VALUES ('$uuid','$ipAddress','$userID','$now','$now')";
        $conn->query($query);
        echo "<br/>Login Successful";
        ?>
        <form id="redirectForm" action="chatroom.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $uuid; ?>">
        </form>
    </body>
</html>
