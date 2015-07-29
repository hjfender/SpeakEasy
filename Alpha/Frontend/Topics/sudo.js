function prepGetActiveObjects(){
	document.getElementById("myFirstResults").innerHTML = "Not implemented";
	getActiveObjects();
}

function getActiveObjects(){
	$.ajax({
		type:"POST", 
        url:"sudo.php", 
        data: {
            'function': 'getActiveObjects', 
        },
        dataType: 'json', 
        success: getActiveObjectsSuccess
	});
}

function getActiveObjectsSuccess(json) {
	if (json["success"]) {
		// Indices to keep track of which element in each row is the next to be filled
		var index_1 = 1;
		var index_2 = 1;
		var index_3 = 1;
		var index; 
		var text = "";
		var image = "";

		for each (topic in json) {
			text = "text";
			image = "image";
			data = convertJsonToObject(topic);

			// Ensure that we stop after we have 6 in each row
			if (index_1 > 6 && index_2 > 6 && index_3 > 6) {
				break;
			} 
			// Skip over full rows
			if ((index_1 > 6 && data.row === 1) || (index_2 > 6 && data.row === 2) || (index_3 > 6 && data.row === 3)) {
				continue;
			}

			// Find the row and index of the topic
			if (data.row === 1) {
				index = index_1;
				index.toString();
				text.concat("1_", index);
				image.concat("1_", index);
				index_1++;
			}
			else if (data.row === 2){
				index = index_2;
				index.toString;
				text.concat("2_", index);
				image.concat("1_", index);
				index_2++;
			}
			else {
				index = index_3;
				index.toString();
				text.concat("3_", index);
				image.concat("3_", index);
				index_3++;
			}
			// Set the description and the image in the html
        	document.getElementById(text).innerHTML = data.description;	// Set the description
        	document.getElementById(image).attr("src", data.image);	// Set the image
    	}
    } else {
        document.getElementById("addResults").innerHTML = data.error;
    }
}

