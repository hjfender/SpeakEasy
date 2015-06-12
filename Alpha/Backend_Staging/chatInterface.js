var instance;
var token;
var state = 0;
function passToken(token1) {
    token = token1;
}

// <editor-fold defaultstate="collapsed" desc="Normal Functions">
 
// <editor-fold defaultstate="collapsed" desc="Get Chat IDs">
/**
 * Gather data and execute pre-request functions
 * @returns 
 */
function prepGetChatIDs() {
    console.log("getting chat IDs");
    getChatIDs();
}

/**
 * Gets the chat IDs for the user assosiated with the set token
 * @returns 
 */
function getChatIDs() {
    console.log("Getting Chat IDs");
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {
            'token': token,
            'function': 'retrive chat ids'
        },
        dataType: "json",
        success: getChatIDsSuccess
    });
}

/**
 * Handles post response HTML changes and other functions
 * @param {String or Object} json JSON response from the server
 * @returns 
 */
function getChatIDsSuccess(json) {
    console.log(json);
    var data = convertToObject(json);
    if (data.success === "true") {
        console.log("Got Chat IDs");
        for (var i = 0; i < data.chatIDs.length; i++) {
            var item = data.chatIDs[i].replace("\n", "");
            var command = "getMessages(\'" + item + "\')";
            $('#chat-ids-area').append($("<div>" + item + " <button onclick=\"" + command + "\">Open Chat</button></div>"));
        }
    }
}
//</editor-fold>

//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="Sudo Functions">
 
// <editor-fold defaultstate="collapsed" desc="Create Chat">
/**
 * Gathers data for and handles pre-request functions for createChat()
 * @returns 
 */
function prepCreateChat() {
    var emailOne = document.getElementById("emailOneCreate").value;
    var emailTwo = document.getElementById("emailTwoCreate").value;
    console.log("creating chat for " + emailOne + " and " + emailTwo);
    createChat(emailOne, emailTwo);
}
/**
 * Creates a chat in the database between the users of the two given emails
 * Calls createChatSuccess if request successful 
 * @param {String} emailOne Email of one user
 * @param {String} emailTwo Email of other user
 * @returns 
 */
function createChat(emailOne, emailTwo) {
    $.ajax({
        type: "POST",
        url: "sudo.php",
        data: {
            'token': token,
            'function': "create chat by email",
            'emailOne': emailOne,
            'emailTwo': emailTwo
        },
        datatype: "json",
        success: createChatSucccess
    });
}

/**
 * Handles HTML changes and post-response for createChat
 * @param {String or Object} json JSON object or string from request
 * @returns 
 */
function createChatSucccess(json) {
    console.log(json);
    var data = convertToObject(json);
    console.log(data);
    if (data.success === "true") {
        console.log("chat created");
    } else {
        console.log("unable to create chat");
    }
}

//</editor-fold>

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Old Code">
function connectToChat() {
    console.log("Connecting to chat");
    var emailOne = document.getElementById("emailOne").value;
    var emailTwo = document.getElementById("emailTwo").value;
    console.log(token);
    console.log(emailOne);
    console.log(emailTwo);
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {
            'token': token,
            'function': 'connect',
            'emailOne': emailOne,
            'emailTwo': emailTwo
        },
        dataType: "json",
        success: function (json) {
            console.log(json);
            var data = convertToObject(json);
            if (data.success === "true") {
                console.log("Connected to chat");
                idOne = data.idOne;
                idTwo = data.idTwo;
                var instance = false;
                updateChat();
            } else {
                document.getElementById("connectionError").innerHTML = data.error;
            }
        }
    });
}

function sendMessage() {
    var message = document.getElementById("message").value;
    var name = document.getElementById("name").value;
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {'function': 'send',
            'token': token,
            'idOne': idOne,
            'idTwo': idTwo,
            'message': message,
            'name': name,
            'file': 'stuff'},
        dataType: "json",
        success: function (json) {
            console.log(json);
            var data = convertToObject(json);
            document.getElementById("message").value = "";
            updateChat();
        }
    });
}

function updateChat() {
    console.log("Retrieving messages for ids " + idOne + ", " + idTwo);
    if (!instance) {
        instance = true;
        $.ajax({
            type: "POST",
            url: "backendDB.php",
            data: {
                'token': token,
                'state': state,
                'function': 'retrieve',
                'idOne': idOne,
                'idTwo': idTwo
            },
            dataType: "json",
            success: function (json) {
                console.log("Recieved a response");
                console.log(json);
                var data = convertToObject(json);
                var name = document.getElementById("name").value;
                if (data.success) {
                    console.log("Got messages:");
                    if (data.text) {
                        var j = 0;
                        for (var i = 0; i < data.text.length; i++) {
                            console.log(data.text[i].substring(0, 6 + name.length));
                            $('#chat-area').append($("<p>" + data.text[i] + "</p>"));
                            j = j + 1;
                        }
                        state = j;
                    }
                }
                document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
                instance = false;
                state = data.state;
                updateChat();
            },
        });
    }
}

function getMessages(chatID) {
    console.log("getting messages");
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {
            'token': token,
            'state': 0,
            'function': 'getNewMessages',
            'chatID': chatID
        },
        dataType: "json",
        success: function (json) {
            console.log(json);
            var data = convertToObject(json);
            if (data.success === "true") {
                console.log("got messages");
                for (var i = 0; i < data.text.length; i++) {
                    $('#chat-area').append($("<p>" + data.text[i] + "</p>"));
                }
            }
            return;
        }
    });
}
//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="Utility Functions">
function convertToObject(json) {
    if (typeof json === "string") {
        return jQuery.parseJSON(json);
    } else {
        return json;
    }
}
//</editor-fold>
