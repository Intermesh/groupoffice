(function () {
	function fallbackCopyTextToClipboard(text) {
		var textArea = document.createElement("textarea");
		textArea.value = text;
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			var msg = successful ? 'successful' : 'unsuccessful';
			console.log('Fallback: Copying text command was ' + msg);
		} catch (err) {
			console.error('Fallback: Oops, unable to copy', err);
		}

		document.body.removeChild(textArea);
	}

	go.util = {
		copyTextToClipboard: function (text) {
			if (!navigator.clipboard) {
				fallbackCopyTextToClipboard(text);
				return;
			}
			navigator.clipboard.writeText(text).then(function () {
				console.log('Async: Copying to clipboard was successful!');
			}, function (err) {
				console.error('Async: Could not copy text: ', err);
			});
		},
		
		mailto : function(config) {
			var email = config.email;
			
			if(config.name) {
				email = '"' + config.name.replace(/"/g, '\"') + '" <' + config.email + '>'; 
			}
			
			document.location = "mailto:" + email;
		},
		
		callto : function(config) {
			document.location = "tel:" + config.number;
		},
		
		streetAddress : function(config) {
			window.open("https://www.openstreetmap.org/search?query=" + encodeURIComponent(config.street + ", " +config.zipCode + ", " + config.country));
		},
		
		showDate : function(date) {
			console.log("No date handler");
		}

	};
	


})();
