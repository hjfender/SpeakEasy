//<editor-fold desc="My First Request">
function prepMyFirstRequest() {
    document.getElementById("myFirstResults").innerHTML = "Not implemented";
    //myFirstRequest();
}
function myFirstRequest() {
    //TODO

}
function myFirstRequestSuccess(json) {
    data = convertJsonToObject(json);
    console.log(data.success);
    if (data.success) {
        document.getElementById("myFirstResults").innerHTML = data.response;
    } else {
        document.getElementById("myFirstResults").innerHTML = data.error;
    }
}
//</editor-fold>

//<editor-fold desc="Request with input">
//ID of name text field: "nameTextField"
function prepRequestWithInput() {
    document.getElementById("withInputResults").innerHTML = "Not implemented";
    //requestWithInput(parameters);
}
function requestWithInput() {
    //TODO Make Request
}
function requestWithInputSuccess(json) {
    data = convertJsonToObject(json);
    if (data.success) {
        document.getElementById("withInputResults").innerHTML = data.response;
    } else {
        document.getElementById("withInputResults").innerHTML = data.error;
    }
}
//</editor-fold>

//<editor-fold desc="Add Request">
//ID of first number: "firstNumber", ID of second number: "secondNumber"
function prepAddRequest() {
    document.getElementById("addResults").innerHTML = "Not implemented";
    //addRequest(parameters);
}
function addRequest() {

}
function addRequestSuccess(json) {
    data = convertJsonToObject(json);
    if (data.success) {
        document.getElementById("addResults").innerHTML = data.equals;
        //TODO Finish Success
    } else {
        document.getElementById("addResults").innerHTML = data.error;
    }
}
//</editor-fold>

function convertJsonToObject(json) {
    if (typeof json === "string") {
        return jQuery.parseJSON(json);
    } else {
        return json;
    }
}
