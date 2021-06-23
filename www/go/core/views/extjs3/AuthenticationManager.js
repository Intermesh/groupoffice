go.AuthenticationManager = (function () {
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
		userAuthenticators: [],
		
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
		
		getAuthUrl : function() {
			return BaseHref + 'api/auth.php';
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
				password: password,
				rememberLogin: this.rememberLogin
			};

			Ext.Ajax.request({
				url: this.getAuthUrl(),
				jsonData: clientData,
				callback: function (options, success, response) {
					var result = Ext.decode(response.responseText);
					if(result.debug) {
						go.Jmap.processDebugResponse(result.debug, 'auth');
					}
          
          this.userAuthenticators = result.authenticators || [];
					this.loginToken = result.loginToken;
					this.username = result.username;
					
					cb.call(scope || this, this, success, result);
					
					if(!success) {
						switch(response.status) {
							case 503:
								Ext.MessageBox.alert(t("Maintenance mode"), t("Sorry, maintenance mode is enabled and you can't login right now. Please come back later or contact your system administrator"));
								break;
								
							case 403:
								// Not allowed by IP filter AllowGroup or user not enabled.
								Ext.MessageBox.alert(t("Account disabled"), response.statusText);
								break;
								
							case 401: //Bad login
								//handled by form
							break;
								
							default: 
								Ext.MessageBox.alert(t("Error"), t("Sorry, an error occurred") + ": " +  response.statusText);
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
				go.browserStorage.deleteDatabase().then(function() {
					Ext.Ajax.request({
						url: go.AuthenticationManager.getAuthUrl(),
						method: "DELETE",
						callback: function() {
							go.User.clearAccessToken();
							go.reload();
						}
					});
				});
			}
		},

		doAuthentication: function (authenticators, cb, scope) {

			var loginData = {
				loginToken: this.loginToken, //while the user is authenticating only loginToken is set 
				accessToken: this.accessToken, //after authentication the access token is retrieved. It can be stored for remembering the login when a user closes the browser.
				rememberLogin: this.rememberLogin,
				authenticators: authenticators
			};

			Ext.Ajax.request({
				url: this.getAuthUrl(),
				jsonData: loginData,
				callback: function (options, success, response) {
					var result = response.responseText ? Ext.decode(response.responseText) : {}, me = this;
					
					if(!success) {
						switch(response.status) {
							case 503:
								Ext.MessageBox.alert(t("Maintenance mode"), t("Sorry, maintenance mode is enabled and you can't login right now. Please come back later or contact your system administrator."));
								break;								
							case 500:
								Ext.MessageBox.alert(t("Error"), t("Sorry, an error occurred") + ": " +  response.statusText);
							break;
						}
						cb.call(scope || me, me, success, result);
						return;
					}

					if (result.accessToken) {
						this.onAuthenticated(result).then(function() {
							cb.call(scope || me, me, success, result);	
						});
					} else
					{
						cb.call(scope || me, me, success, result);
					}

				},
				scope: this
			});

		},

		login : function() {
			this.loginPanel = new go.login.LoginPanel();

			this.loginPanel.render(document.body);
		},

		onAuthenticated: function (result) {
			
			go.User.setAccessToken(result.accessToken, go.AuthenticationManager.rememberLogin);

			if(this.loginPanel) {
				this.loginPanel.destroy();
				this.loginPanel = null;
			}

			var me = this;

			return go.User.onLoad(result).then(function() {
				me.fireEvent("authenticated", me, result);

				if(go.User.theme != GO.settings.config.theme) {
					go.reload();
					return;
				}
			
				GO.mainLayout.onAuthentication();
			});		
		}
	});

	return new AuthMan();
})();
