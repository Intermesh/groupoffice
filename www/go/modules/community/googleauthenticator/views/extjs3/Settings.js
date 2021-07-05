Ext.ns("go.googleauthenticator");

Ext.onReady(function () {
	Ext.override(go.usersettings.AccountSettingsPanel, {
		initComponent: go.usersettings.AccountSettingsPanel.prototype.initComponent.createSequence(function () {
			this.googleAuthenticatorFieldset = new go.googleauthenticator.AuthenticatorSettingsFieldset();
			this.insert(3, this.googleAuthenticatorFieldset);
			})
		});
	});
	
	go.googleauthenticator.AuthenticatorSettingsFieldset = Ext.extend(Ext.form.FieldSet, {
		entityStore:"User",
		currentUser: null,
		labelWidth: dp(152),
		title: t('Google authenticator'),

		initComponent: function() {
			this.enableAuthenticatorBtn = new Ext.Button({
				text:t('Enable google authenticator'),
				hidden:false,
				handler:function(){	
					var me = this;
					me.requestSecret(me.currentUser, function(userId){						
						var enableDialog = new go.googleauthenticator.EnableAuthenticatorDialog();
						enableDialog.load(userId).show();
					});
				},
				scope: this
			});
			
			this.disableAuthenticatorBtn = new Ext.Button({
				text:t('Disable google authenticator'),
				hidden:true,
				handler:function(){
					var me = this;
					me.disableAuthenticator(me.currentUser, function(userId){
						// When this is called all went well and the authenticator is disabled
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
		
		onLoad : function(user){
			
			var isActive = (user.googleauthenticator && user.googleauthenticator.isEnabled);
			
			this.enableAuthenticatorBtn.setVisible(!isActive);
			this.disableAuthenticatorBtn.setVisible(isActive);
			this.currentUser = user;
		},
		
		disableAuthenticator : function(user, callback){
			var me = this;
			
			function execute(currentPassword){
				var data = {
						googleauthenticator: null
					};
				if(currentPassword) {
					data.currentPassword = currentPassword;
				}
				go.Db.store("User").save(data, user.id).then(function() {
					callback.call(this,user.id);
				}).catch(function(error) {
					if(error.message && !error.response) {
						GO.errorDialog.show(error.message);
					}

					// When the password is not correct, call itself again to try again
					me.disableAuthenticator(user, callback);
				});
			}
			
			// If the user is an admin then no password needs to be given (Except when the admin is changing it's own account
			if (go.User.isAdmin && user.id != go.User.id) {
				execute.call(this);
				return;
			} else {
				var passwordPrompt = new go.PasswordPrompt({
					width: dp(450),
					text: t("When disabling Google autenticator this step will be removed from the login process.") + "<br><br>" + t("Provide your current password to disable Google authenticator."),
					title: t('Disable Google authenticator'),
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

		requestSecret : function(user, callback){
			var me = this;

			function execute(currentPassword){
				var data = {
					googleauthenticator: {
						requestSecret:true
					}
				};
				if(currentPassword) {
					data.currentPassword = currentPassword;
				}
				go.Db.store("User").save(data ,user.id).then( function (options, success, response) {
					// set timeout so that dialog loads after changes event
					setTimeout(() => {
						callback.call(me, user.id);
					});
				}).catch(function(error) {

					// When the password is not correct, call itself again to try again
					me.requestSecret(user, callback);

					if(error.message && !error.response) {
						GO.errorDialog.show(error.message);
					}
				})
			}

			// If the user is an admin then no password needs to be given (Except when the admin is changing it's own account
			if (go.User.isAdmin && user.id != go.User.id) {
				execute.call(this);
				return;
			} else {
				var passwordPrompt = new go.PasswordPrompt({
					width: dp(450),
					text: t("Provide your current password before you can enable Google authenticator."),
					title: t('Enable Google authenticator'),
					iconCls: 'ic-security',
					listeners: {
						'ok': function (password) {
							execute(password);
						},
						'cancel': function () {
							return false;
						},
						scope: this
					}
				});
				passwordPrompt.show();
			}
		}
	});
	