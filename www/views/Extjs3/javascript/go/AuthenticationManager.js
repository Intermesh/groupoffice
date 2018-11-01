(function () {
	var AuthMan = Ext.extend(Ext.util.Observable, {

		/**
		 * Contains all authenticator method panels
		 */
		panels: [],

		/**
		 * Contains the login token for when a user is logging in
		 */
		loginToken: null,

		/**
		 * Contains the access token used for authenticating requests.
		 */

		accessToken: null,

		/**
		 * Contains the username of the user that is logging in
		 */
		username: null,

		/**
		 * The authenticators of the user
		 * 
		 * @type {Array} string
		 */
		userMethods: [],
		
		rememberLogin:false,

		/**
		 * Register new authenticator
		 * This needs to provide the authenticator key and the panel for this authenticator
		 * 
		 * @param string key
		 * @param panel authenticatorPanel
		 * @return {undefined}
		 */
		register: function (key, authenticatorPanel, index) {

			authenticatorPanel.id = key;

			var panel = {
				key: key,
				panel: authenticatorPanel
			};

			if (typeof (index) == "undefined") {
				this.panels.push(panel);
			} else
			{
				this.panels.splice(index, 0, panel);
			}
		},

		/**
		 * Does a call to the server to get all available authenticators for the given username
		 */
		getAvailableMethods: function (username, password, cb, scope) {

			var clientData = {
				clientName: 'Group-Office webclient',
				clientVersion: '>9000',
				deviceName: navigator.userAgent,
				username: username,
				password: password
			};

			Ext.Ajax.request({
				url: BaseHref + 'auth.php',
				jsonData: clientData,
				callback: function (options, success, response) {
					var result = Ext.decode(response.responseText);
          
          this.userMethods = result.methods || [];
					this.loginToken = result.loginToken;
					this.username = result.username;
					
					cb.call(scope || this, this, success, result);
					
					if(!success) {
						switch(response.status) {
							case 503:
								Ext.MessageBox.alert(t("Maintenance mode"), t("Sorry, maintenance mode is enabled and you can't login right now. Please come back later or contact your system administrator"));
								break;
								
							case 401:
								Ext.MessageBox.alert(t("Account disabled"), t("You're account has been disabled"));
								break;
								
							case 403:
								//handled by form
							break;
								
							default: 
								Ext.MessageBox.alert(t("Error"), t("An unknown error has occurred. " + response.statusText));
							break;
						}
						
						return;
					}

					

					if (result.accessToken) {
						this.onAuthenticated(result);
					}

					

				},
				scope: this
			});
		},

		
		
		logout: function (first) {

			if (Ext.Ajax.isLoading())
			{
				if (first) {
					Ext.getBody().mask(t("Loading..."));
				}
				this.logout.defer(500, this, [true]);
			} else
			{
				Ext.Ajax.request({
					url: BaseHref + 'auth.php',
					method: "DELETE",
					callback: function() {
						go.User.clearAccessToken();
						document.location = BaseHref;
					}
				})
				
			}
		},

		doAuthentication: function (methods, cb, scope) {

			var loginData = {
				loginToken: this.loginToken, //while the user is authenticating only loginToken is set 
				accessToken: this.accessToken, //after authentication the access token is retrieved. It can be stored for remembering the login when a user closes the browser.
				methods: methods
			};

			Ext.Ajax.request({
				url: BaseHref + 'auth.php',
				jsonData: loginData,
				callback: function (options, success, response) {
					var result = response.responseText ? Ext.decode(response.responseText) : {};

					cb.call(scope || this, this, success, result);					
					
					if(!success) {
						switch(response.status) {
							case 503:
								Ext.MessageBox.alert(t("Maintenance mode"), t("Sorry, maintenance mode is enabled and you can't login right now. Please come back later or contact your system administrator."));
								break;								
							case 500: 
								Ext.MessageBox.alert(t("Error"), t("An unknown error has occurred. " + reponse.statusText));
							break;
						}
						
						return;
					}

					if (result.accessToken) {
						this.onAuthenticated(result);
					}

				},
				scope: this
			});

		},

		onAuthenticated: function (result) {
			var storage = go.AuthenticationManager.rememberLogin ? 'localStorage' : 'sessionStorage';
			window[storage].setItem('accessToken', result.accessToken);
			
			if (GO.loginDialog) {
				GO.loginDialog.close();
			}
      
      go.User.loadSession(result);
			
			

			Ext.Ajax.defaultHeaders['Authorization'] = 'Bearer ' + result.accessToken;

			this.fireEvent("authenticated", this, result);
			
			
			
			GO.mainLayout.onAuthentication();

		}
	});

	go.AuthenticationManager = new AuthMan();
})();
