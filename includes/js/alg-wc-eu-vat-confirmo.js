/**
 * alg-wc-eu-vat-confirmo.js
 *
 * @version 2.10.0
 **/
var confirmoinit = document.createElement('div');
confirmoinit.className = 'confirmo-backdrop';
confirmoinit.id = 'confirmo-modal';
document.body.addEventListener('keydown', function(event) {
	if (event.keyCode == 27) {
		closeconfirmomodal();
	}
});
document.body.appendChild(confirmoinit);

document.getElementById('confirmo-modal').innerHTML = '<center><div class="confirmo-modal"><p align="right"><button class="confirmo-close" onclick="closeconfirmomodal()">&times;</button></p><div id="confirmo-content"></div><div class="confirmo-controls"><button id="confirmo-left" class="confirmo-btn">&#10004; Yes</button><button id="confirmo-right" class="confirmo-btn" onclick="closeconfirmomodal()">&times; No</button></div></div></center>';
var confirmomodalstate = false;
function showconfirmomodal(msg) {
	var confirmomodalstate = true;
	document.getElementById("confirmo-modal").style.display = "block";
	document.getElementById("confirmo-content").innerHTML = msg;
	document.getElementById("confirmo-modal").focus();
}
function closeconfirmomodal() {
	var confirmomodalstate = false;
	document.getElementById("confirmo-modal").style.display = "none";
	
}
var confirmo = {
	init:function(props={}) {
		var confirmoelements = document.querySelectorAll("*[confirmo-message]");
		for (let i = 0; i < confirmoelements.length; i++) {
			var confirmo_message = confirmoelements[i].getAttribute("confirmo-message");
			var confirmobool = false;
			if (confirmo_message != "") {
				if (confirmoelements[i].getAttribute("confirmo-func")) {
					confirmobool = true;
				}
			}
			if (confirmobool == false) {
				console.error("Some element(s) is/are not configured properly. Please check the docs at https://google.com for more details on configuration");
			}
			else {
				confirmoelements[i].onclick = function() {
					var confirmofunctionname = this.getAttribute("confirmo-func") + "()";
					showconfirmomodal(this.getAttribute('confirmo-message'));
					document.getElementById("confirmo-left").setAttribute('onclick', confirmofunctionname+';closeconfirmomodal()');
				
				}
			}
		}
		for (i in props) {
			if (i == "yesBg") {
				document.getElementById("confirmo-left").style.backgroundColor = props[i];
			}
			else if (i == "noBg") {
				document.getElementById("confirmo-right").style.backgroundColor = props[i];
			}
			else if (i == "leftText") {
				document.getElementById("confirmo-left").innerHTML  = '&#10004; ' + props[i];
			}
			else if (i == "rightText") {
				document.getElementById("confirmo-right").innerHTML  = '&times; ' + props[i];
			}
			else if (i == "backColor") {
				document.querySelector(".confirmo-modal").style.backgroundColor = props[i];
			}
			else if (i == "textColor") {
				document.querySelector(".confirmo-modal").style.color = "white";
			}
		}
	},
	show:function(props) {
		for (let i in props) {
			if (i == "msg") {
				showconfirmomodal(props.msg);
			}
			else if (i == "callback_yes") {
				document.getElementById("confirmo-left").onclick = function() {
					if (typeof(props.callback_yes) == "function") {
						props.callback_yes();
					}
					closeconfirmomodal();
				}
			}
			else if (i == "callback_no") {
				document.getElementById("confirmo-right").onclick = function() {
					if (typeof(props.callback_no) == "function") {
						props.callback_no();
					}
					closeconfirmomodal();
				}
			}
		}
	}

};