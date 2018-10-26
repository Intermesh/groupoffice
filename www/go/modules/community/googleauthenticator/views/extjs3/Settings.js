Ext.ns("go.googleauthenticator");

Ext.onReady(function () {
	Ext.override(go.usersettings.AccountSettingsPanel, {

		initComponent: go.usersettings.AccountSettingsPanel.prototype.initComponent.createSequence(function () {
			
			this.googleAuthenticatorFieldset = new go.googleauthenticator.AuthenticatorSettingsFieldset();
			this.add(this.googleAuthenticatorFieldset);
			})
			
		});
	});
	
	
	go.googleauthenticator.AuthenticatorSettingsFieldset = Ext.extend(Ext.form.FieldSet, {
		currentUserId: null,
		labelWidth: dp(152),
		title: t('Google authenticator', 'googleauthenticator'),
		
		initComponent: function() {
			this.enableAuthenticatorBtn = new Ext.Button({
				text:t('Enable google authenticator', 'googleauthenticator'),
				hidden:false,
				handler:function(){	
					var me = this;
					this.requestSecret(this.currentUserId, function(userId){						
						var enableDialog = new go.googleauthenticator.EnableAuthenticatorDialog();
						enableDialog.load(userId).show();
						
						// TODO: THIS IS TEMPORARY CODE BECAUSE THE ACCOUNT WINDOW IS NOT LISTENING TO THE USER CHANGES CORRECTY
						// THE CODE BELOW SHOULD NOT BE NECCESARY WHEN THE ACCOUNT WINDOW NOTICES THE CHANGE ON THE googleauthenticator PROPERTY 
						enableDialog.on('save', function(dialog,entity){
							me.updateButtons(userId);
						});
						
					});
				},
				scope: this
			});
			
			this.disableAuthenticatorBtn = new Ext.Button({
				text:t('Disable google authenticator', 'googleauthenticator'),
				hidden:true,
				handler:function(){
					var me = this;
					this.disableAuthenticator(this.currentUserId, function(userId){
						// When this is called all went well and the authenticator is disabled
						
						// TODO: THIS IS TEMPORARY CODE BECAUSE THE ACCOUNT WINDOW IS NOT LISTENING TO THE USER CHANGES CORRECTY
						// THE CODE BELOW SHOULD NOT BE NECCESARY WHEN THE ACCOUNT WINDOW NOTICES THE CHANGE ON THE googleauthenticator PROPERTY 
						me.updateButtons(userId);
					});
				},
				scope: this
			});
			
			this.items = [
				this.enableAuthenticatorBtn,
				this.disableAuthenticatorBtn
			];
			
			go.googleauthenticator.AuthenticatorSettingsFieldset.superclass.initComponent.call(this);
		},
		
		onLoadComplete : function(data){
			this.enableAuthenticatorBtn.setVisible(!data.googleauthenticator);
			this.disableAuthenticatorBtn.setVisible(data.googleauthenticator);
			this.currentUserId = data.id;
		},
		
		disableAuthenticator : function(userId, callback){
			var me = this;
			
			function execute(currentPassword){
				var params = {"update": {}},
					data = {
						googleauthenticator: null
					};
				if(currentPassword) {
					data.currentPassword = currentPassword;
				}
				params.update[userId] = data;

				go.Stores.get("User").set(params, function (options, success, response) {
					if (success && !GO.util.empty(response.updated)) {
						callback.call(this,userId);
					} else {
						// When the password is not correct, call itself again to try again
						me.disableAuthenticator(userId, callback);
					}
				});
			}
			
			// If the user is an admin then no password needs to be given (Except when the admin is changing it's own account
			if (go.User.isAdmin && userId != go.User.id) {
				execute.call(this);
				return;
			} else {
				var passwordPrompt = new go.PasswordPrompt({
					width: dp(450),
					text: t("When disabling Google autenticator this step will be removed from the login process.", 'googleauthenticator') + "<br><br>" + t("Provide your current password to disable Google authenticator.", 'googleauthenticator'),
					title: t('Disable Google authenticator', 'googleauthenticator'),
					listeners: {
						'ok': function(value){
							execute.call(this,value);
						},
						'cancel': function () {
							return false;
						},
						scope: this
					}
				});

				passwordPrompt.show();
			}
		},

		requestSecret : function(userId, callback){
				var me = this;
			
				var passwordPrompt = new go.PasswordPrompt({
					width: dp(450),
					text: t("Provide your current password before you can enable Google authenticator.", 'googleauthenticator'),
					title: t('Enable Google authenticator', 'googleauthenticator'),
					iconCls: 'ic-security',
					listeners: {
						'ok': function(value){
							var params = {"update": {}};
							params.update[userId] = {
								currentPassword: value,
								googleauthenticator: {
									requestSecret:true
								}
							};

							go.Stores.get("User").set(params, function (options, success, response) {
								if (success && !GO.util.empty(response.updated)) {
									// When password is checked successfully, then show the QR dialog
									callback.call(this,userId);
								} else {
									// When the password is not correct, call itself again to try again
									me.requestSecret(userId, callback);
								}
							});
						},
						'cancel': function () {
							return false;
						},
						scope: this
					}
				});

				passwordPrompt.show();
		},
		
		// TODO: THIS IS TEMPORARY CODE BECAUSE THE ACCOUNT WINDOW IS NOT LISTENING TO THE USER CHANGES CORRECTY
		// THE CODE BELOW SHOULD NOT BE NECCESARY WHEN THE ACCOUNT WINDOW NOTICES THE CHANGE ON THE googleauthenticator PROPERTY 
		updateButtons: function(userId){
			var me = this;
			go.Stores.get("User").get([userId], function(entities){
				
				if(entities[0]){
					var enabled = entities[0].googleauthenticator && entities[0].googleauthenticator.isEnabled;
					me.disableAuthenticatorBtn.setVisible(enabled);
					me.enableAuthenticatorBtn.setVisible(!enabled);
				}

			});
		}
	});