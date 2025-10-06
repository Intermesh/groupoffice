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
		 * Contains the username of the user that is logging in
		 */
		username: null,

		/**
		 * Only during login, this contains the password of the user logging in. Needed for force password change.
		 */
		password: null,

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

					this.password = password;
					
					cb.call(scope || this, this, success, result);
					
					if(!success) {
						switch(response.status) {
							case 503:
								Ext.MessageBox.alert(t("Maintenance mode"), t("Sorry, maintenance mode is enabled and you can't login right now. Please come back later or contact your system administrator"));
								break;
								
							case 403:
								// Not allowed by IP filter AllowGroup or user not enabled.
								Ext.MessageBox.alert(t("Account disabled"), t(response.statusText));
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
						this.onAuthenticated(result, username, password);
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
				window.GOUI.browserStoreConnection.deleteDatabase().then(function() {
					Ext.Ajax.request({
						url: go.AuthenticationManager.getAuthUrl(),
						method: "DELETE",
						callback: function() {
							go.reload();
						}
					});
				});
			}
		},

		doAuthentication: function (authenticators, cb, scope) {

			var loginData = {
				loginToken: this.loginToken, //while the user is authenticating only loginToken is set 
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

		onAuthenticated: function (result, username, password) {
			if(this.loginPanel) {
				this.loginPanel.destroy();
				this.loginPanel = null;
			}



			return go.User.onLoad(result).then(() => {

				if(go.User.theme != GO.settings.config.theme || go.User.language != GO.lang.iso) {
					go.reload();
					return;
				}

				GO.mainLayout.onAuthentication(password).then(() => {
					this.password = null;
					this.fireEvent("authenticated", this, result, username, password);
				})
			});		
		},

		/**
		 * Password prompt
		 *
 		 * @param title
		 * @param message
		 * @return {Promise}
		 */
		passwordPrompt : function(title, message, closable) {

			if (closable == undefined) {
				closable = true;
			}
			return new Promise((resolve, reject) =>
			{
				const passwordPrompt = new go.PasswordPrompt({
					closable: closable,
					width: dp(540),
					text: message,
					title: title,
					iconCls: 'ic-security',
					listeners: {
						'ok': function (password) {
							resolve(password);
						},
						'cancel': function () {
							reject();
						},
						scope: this
					}
				});
				passwordPrompt.show();
			});
		}
	});

	return new AuthMan();
})();
