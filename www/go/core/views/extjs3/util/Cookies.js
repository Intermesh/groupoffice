/**
 * Cookie utilities
 */
go.util.Cookies = {
	/**
	 * Set a cookie
	 * 
	 * @param {string} name
	 * @param {string} value
	 * @param {int} maxAge Maximum age in seconds. Leave empty to clear on browser close
	 * @returns {void}
	 */
	set: function (name, value, maxAge) {		
		if (maxAge) {			
			maxAge = ";Max-Age=" + maxAge;
		} else
		{
			maxAge = "";
		}
		var secure = location.protocol === 'https:' ? ';secure' : '';		
		var cookie = name + "=" + encodeURIComponent(value + "") + maxAge + secure + ";path=" + document.location.pathname + ';SameSite=Strict';
		document.cookie = cookie;
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
		document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=' + document.location.pathname + ';SameSite=Strict';
	}
}
