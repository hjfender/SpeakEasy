<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script type="text/javascript" src="chatInterface.js"></script>
        <script type="text/javascript">
            function init() {
                console.log("init");
                passToken(<?php echo '"' . $_POST['token'] . '"'; ?>);
                prepGetProfileInformation();
                getChatIDs();
            }
            window.onload = init;
        </script>
    </head>
    <body>
        <div> <div id="firstNameInfo">First Name</div> <div id="lastNameInfo">Last Name</div> <div id="emailInfo">Email Info</div></div>
        <div id="chat-ids-area"> </div>
        <div><form action="javascript:prepCreateChat()">
                <div>
                    <div>
                        Email One: <input type="text" id="emailOneCreate" /> 
                    </div>
                    <div>
                        Email Two: <input type = "text" id="emailTwoCreate" />
                    </div>
                    <div>
                        <input type="submit" />
                    </div>
                    <div id="connectionError"></div>
                </div>
            </form> 
        </div>
        <b><a href="createProfile.html">Create a profile </a></b>
        <div><button onclick="prepUpdateChat()">Update Chat</button></div>
        <div id="chat-area"></div>
        <form id="message-form" action="javascript:prepSendMessage()">
            <p>Enter your message:</p>
            <input type="text" id="message" maxlength="128">
            <input type="submit">
        </form>
    </body>
</html>