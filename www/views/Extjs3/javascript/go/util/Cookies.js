/**
 * Cookie utitilies
 */
go.util.Cookies = {
	/**
	 * Set a cookie
	 * 
	 * @param {string} name
	 * @param {string} value
	 * @param {Date} expires Leave empty to clear on browser close
	 * @returns {void}
	 */
	set: function (name, value, expires) {		
		if (expires) {			
			expires = "; expires=" + expires.toUTCString();
		} else
		{
			expires = "";
		}
		
		document.cookie = name + "=" + (value || "") + expires + "; path=/";
	},
	
	/**
	 * Get a cookie
	 * 
	 * @param {string} name
	 * @returns {string}
	 */
	get: function (name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ')
				c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0)
				return c.substring(nameEQ.length, c.length);
		}
		return null;
	},
	
	/**
	 * Unset a cookie
	 * 
	 * @param {string} name
	 * @returns {void}
	 */
	unset: function (name) {
		document.cookie = name + '=; Max-Age=-99999999;';
	}
}
