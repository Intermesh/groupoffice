Ext.onReady(function () {
	Ext.override(go.usersettings.AccountSettingsPanel, {
		initComponent: go.usersettings.AccountSettingsPanel.prototype.initComponent.createSequence(function () {
			if(!go.Modules.isAvailable("community", "otp")) {
				return;
			}
			this.otpFieldset = new go.modules.community.otp.AuthenticatorSettingsFieldset();
			this.insert(3, this.otpFieldset);
			})
		});
	});
	
	go.modules.community.otp.AuthenticatorSettingsFieldset = Ext.extend(Ext.form.FieldSet, {
		entityStore:"User",
		currentUser: null,
		labelWidth: dp(152),
		title: t('Two Factor Authentication', "otp", "community"),

		initComponent: function() {
			this.enableAuthenticatorBtn = new Ext.Button({
				text:t('Enable OTP Authenticator', "otp", "community"),
				hidden:false,
				handler:function(){

					go.modules.community.otp.enable(this.currentUser);

				},
				scope: this
			});
			
			this.disableAuthenticatorBtn = new Ext.Button({
				text:t('Disable OTP Authenticator'),
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
				{
					xtype: "box",
					autoEl: "p",
					html: t("Setup one-time password authentication using an OTP application which generates a unique PIN for each login.", "otp", "community")
				},
				this.enableAuthenticatorBtn,
				this.disableAuthenticatorBtn
			];
			
			go.modules.community.otp.AuthenticatorSettingsFieldset.superclass.initComponent.call(this);
		},
		
		onLoad : function(user){

			const isActive = (user.otp && user.otp.isEnabled);

			this.enableAuthenticatorBtn.setVisible(!isActive);
			this.disableAuthenticatorBtn.setVisible(isActive);
			this.currentUser = user;
		},
		
		disableAuthenticator : function(user, callback){
			const me = this;

			function execute(currentPassword){
				const data = {
					otp: null
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
					t('Disable OTP Authenticator', "otp", "community"),
					t("When disabling OTP Authenticator this step will be removed from the login process.", "otp", "community") +
					"<br><br>" + t("Provide your current password to disable OTP Authenticator.", "otp", "community")
				). then((password) => {
					execute.call(this,password);
				});

			}
		}
	});
	