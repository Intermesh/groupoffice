Ext.onReady(function () {
	Ext.override(go.usersettings.AccountSettingsPanel, {
		initComponent: go.usersettings.AccountSettingsPanel.prototype.initComponent.createSequence(function () {
			if(!go.Modules.isAvailable("community", "googleauthenticator")) {
				return;
			}
			this.googleAuthenticatorFieldset = new go.modules.community.googleauthenticator.AuthenticatorSettingsFieldset();
			this.insert(3, this.googleAuthenticatorFieldset);
			})
		});
	});
	
	go.modules.community.googleauthenticator.AuthenticatorSettingsFieldset = Ext.extend(Ext.form.FieldSet, {
		entityStore:"User",
		currentUser: null,
		labelWidth: dp(152),
		title: t('Google authenticator'),
		
		onChanges : function(entityStore, added, changed, destroyed) {
			if(this.currentUser && changed[this.currentUser.id] && ("googleauthenticator" in changed[this.currentUser.id])){
				this.onLoad(changed[this.currentUser.id]);
			}
		},
		
		initComponent: function() {
			this.enableAuthenticatorBtn = new Ext.Button({
				text:t('Enable google authenticator'),
				hidden:false,
				handler:function(){

					go.modules.community.googleauthenticator.enable(this.currentUser);

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
			
			go.modules.community.googleauthenticator.AuthenticatorSettingsFieldset.superclass.initComponent.call(this);
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

				go.AuthenticationManager.passwordPrompt(
					t('Disable Google authenticator'),
					t("When disabling Google autenticator this step will be removed from the login process.") + "<br><br>" + t("Provide your current password to disable Google authenticator.")
				). then((password) => {
					execute.call(this,password);
				});

			}
		}
	});
	