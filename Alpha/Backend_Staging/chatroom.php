<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script type="text/javascript" src="chatInterface.js"></script>
        <script type="text/javascript">
            function init() {
                console.log("init");
                passToken(<?php echo '"'.$_POST['token'].'"'; ?>);
                getChatIDs();
            }
            window.onload = init;
        </script>
    </head>
    <body>
        <div id="chat-ids-area"> </div>
        <div><form action="javascript:createChat()">
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
                    <div/>
            </form>
            <b><a href="createProfile.html">Create a profile </a></b>
            <form action="javascript:connectToChat()">
                <div>
                    <div>
                        Email One: <input type="text" id="emailOne" /> Name: <input type="text" id="name" />
                    </div>
                    <div>
                        Email Two: <input type = "text" id="emailTwo" />
                    </div>
                    <div>
                        <input type="submit" />
                    </div>
                    <div id="connectionError"></div>
                    <div/>
            </form>
            <div id="chat-area"></div>
            <form id="message-form" action="javascript:sendMessage()">
                <p>Enter your message:</p>
                <input type="text" id="message" maxlength="128">
                <input type="submit">
            </form>
    </body>
</html>