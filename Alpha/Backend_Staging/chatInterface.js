var instance;
var token;
var currentState = 0;
var selectedChatID;
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
            var command = "setSelectedChat(\'" + item + "\')";
            $('#chat-ids-area').append($("<div>" + item + " <button onclick=\"" + command + "\">Open Chat</button></div>"));
        }
    }
}
//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="Send Message">
/**
 * Gathers data and executes pre-request functions for sendMessage
 * @returns
 */
function prepSendMessage() {
    var message = document.getElementById("message").value;
    var chatID = selectedChatID;
    sendMessage(chatID, message);
}

/**
 * Sends a messages to the specified chatID
 * @param {String} chatID Chat to send message to
 * @param {String} message Message to send to chat
 * @returns 
 */
function sendMessage(chatID, message) {
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {'function': 'chat:send:message',
            'token': token,
            'chatID': chatID,
            'message': message
        },
        dataType: "json",
        success: sendMessageSuccess
    });
}

/**
 * Changes HTML and other post-request functions for sendMessage
 * @param {Object or String} json JSON response from server
 * @returns 
 */
function sendMessageSuccess(json) {
    console.log(json);
    var data = convertToObject(json);
    document.getElementById("message").value = "";
}
//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="Update Chat">
/**
 * Gathers data and executes pre-request functions for updateChat
 * @returns 
 */
function prepUpdateChat() {
    console.log("Retrieving new messages for " + selectedChatID);
    updateChat(selectedChatID, currentState);
}

/**
 * Gets the new messages for the specified chat ID. Provided state indicates
 * how many messages are already loaded.
 * @param {String} chatID ID of chat requesting messages for
 * @param {Number} state How many lines have chat the client currently has
 * @returns 
 */
function updateChat(chatID, state) {
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {
            'function': 'chat:retrieve:new',
            'token': token,
            'state': state,
            'chatID': chatID
        },
        dataType: "json",
        success: updateChatSuccess
    });
}

/**
 * Changes HTML and executes post response functions for updateChat
 * @param {Object or String} json JSON response from server
 * @returns 
 */
function updateChatSuccess(json) {
    console.log(json);
    var data = convertToObject(json);

    if (data.success === "true") {
        if (data.messages !== "false") {
            for (var i = 0; i < data.messages.length; i++) {
                var messageData =  jQuery.parseJSON(data.messages[i]);;
                $('#chat-area').append($("<p>" + messageData.sender + ": " + messageData.message + "</p>"));
                currentState = messageData.index;
            }
            document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
            currentState = data.state;
        }
        prepUpdateChat();
    }


}
//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="Get initial messages">
function prepGetInitialMessages() {
    console.log("Getting initial messages");
    getInitialMessages(selectedChatID, 15);
}

function getInitialMessages(chatID, numMessages) {
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {
            'function': 'chat:retrieve:last',
            'token': token,
            'numMessages': numMessages,
            'chatID': chatID
        },
        dataType: "json",
        success: getInitialMessagesSuccess
    });
}

function getInitialMessagesSuccess(json) {
    console.log(json);
    var data = convertToObject(json);

    if (data.success === "true") {
        if (data.messages !== "false") {
            var j = 0;
            for (var i = 0; i < data.messages.length; i++) {
                var messageData =  jQuery.parseJSON(data.messages[i]);;
                $('#chat-area').append($("<p>" + messageData.sender + ": " + messageData.message + "</p>"));
                currentState = messageData.index;
            }
            document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
        }
        prepUpdateChat();
    }
}
//</editor-fold>

//<editor-fold defaultstate="collapsed" desc="Profile Information">
function prepGetProfileInformation() {
    console.log("Getting profile info");
    getProfileInformation();
}

function getProfileInformation() {
    $.ajax({
        type: "POST",
        url: "backendDB.php",
        data: {
            'function': 'profile:info:all',
            'token': token,
        },
        dataType: "json",
        success: getProfileInformationSuccess
    });
}

function getProfileInformationSuccess(json) {
    console.log(json);
    var data = convertToObject(json);
    
    if(data.success === "true") {
        document.getElementById("firstNameInfo").innerHTML = data.firstName;
        document.getElementById("lastNameInfo").innerHTML = data.lastName;
        document.getElementById("emailInfo").innerHTML = data.email;
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

// <editor-fold defaultstate="collapsed" desc="Connect to Chat">
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
//</editor-fold>



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

function setSelectedChat(newChatID) {
    selectedChatID = newChatID;
    console.log("setting selected chat to " + newChatID);
    console.log(selectedChatID);
    prepGetInitialMessages();
}
//</editor-fold>
