var idOne;
			var idTwo;
			var instance;
			var state = 0;  
			var ding = new Audio("ding.mp3");
			function connectToChat() {
				console.log("Connecting to chat");
				var emailOne = document.getElementById("emailOne").value;
				var emailTwo = document.getElementById("emailTwo").value;
				$.ajax({
					type: "POST",
					url: "backendDB.php",
					data: {
						'function': 'connect',
						'emailOne': emailOne, 
						'emailTwo': emailTwo
					},
					dataType: "json",
					success: function(data) {
						console.log(data);
						if(data.success == "true") {
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
						data: {'function': 'send','idOne': idOne, 'idTwo': idTwo, 'message': message,'name': name,'file': 'stuff'},
						dataType: "json",
						success: function(data){
							document.getElementById("message").value = "";
							updateChat();
						}
					});
			}
			function updateChat() {
				console.log("Retrieving messages for ids " + idOne + ", " + idTwo);
				if(!instance){
					instance = true;
					$.ajax({
						type: "POST",
						url: "backendDB.php",
						data: {  
							'state' : state,
							'function': 'retrieve',
							'idOne': idOne,
							'idTwo': idTwo,
						},
						dataType: "json",
						success: function(data){
							console.log("Recieved a response");
							var name = document.getElementById("name").value;
							if(data.success) {
								console.log("Got messages:");
								if(data.text){
									var j = 0;
									for (var i = 0; i < data.text.length; i++) {
										console.log(data.text[i].substring(0,6+name.length));
										$('#chat-area').append($("<p>"+ data.text[i] +"</p>"));
										j = j + 1;
									}							
									state = j;
								}
								ding.play();
							}
						document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
						instance = false;
						state = data.state;
						updateChat();
						},
					});
				}
			}		