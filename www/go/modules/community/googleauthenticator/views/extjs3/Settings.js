Ext.ns("go.googleauthenticator");

Ext.override(go.usersettings.AccountSettingsPanel, {
	
	initComponent : go.usersettings.AccountSettingsPanel.prototype.initComponent.createSequence(function() {

	var me = this;
		this.googleAuthenticatorFieldset = new Ext.form.FieldSet({
			QRcodeUrl:Ext.BLANK_IMAGE_URL,
			labelWidth:dp(152),
			title: t('Google authenticator','googleauthenticator'),
			items:[
				new Ext.ux.form.XCheckbox({
					itemId: 'enableGoogleAuthenticatorCheck',
					xtype: 'checkbox',
					boxLabel: t('Enable google authenticator','googleauthenticator'),
					hideLabel:true,
					anchor: '100%',
					listeners:{
						check: function(cbx,checked){
							
							// check to not fire the functions below when loading the form
							if(!go.userSettingsDialog.isVisible()){
								return;
							}
							
							if(checked){
								this.enableGoogleAuthenticator();
							} else {
								this.disableGoogleAuthenticator();
							}
						},
						scope:this
					}
				}),
				new Ext.form.FieldSet({
					itemId: 'googleAuthenticatorQRFieldSet',
					collapsed:true,
					items:[
						new Ext.Container({
							itemId: 'googleAuthenticatorText',
							html:t('Scan the QR code below to enable Google authenticator for your account.','googleauthenticator'),
							style: {
								marginBottom: dp(8)+'px'
							}
						}),
						new Ext.BoxComponent({
							itemId: 'googleAuthenticatorQRcodeField',
							qrUrl: Ext.BLANK_IMAGE_URL,
							onRender : function(ct, position){
								this.el = ct.createChild({
									tag: 'img',
									cls: "googleauthenticator-qr",
									src: this.qrUrl
								});
							},
							setQRUrl: function(url){
								this.qrUrl = url;
								if(this.rendered) {
									this.getEl().dom.src = url;
								}
							},
							clearQRUrl: function(){								
								this.setQRUrl(Ext.BLANK_IMAGE_URL);
							}
						}),
						new GO.form.PlainField({
							itemId: 'googleAuthenticatorSecret',
							fieldLabel: t('Secret key for manual input','googleauthenticator')
						})
					]
				})
			],
			
			onLoadComplete : function(data){

				// Google authenticator is already configured for this user.
				me.setEnabled(!!data.googleauthenticator);
			}
			
		});

		this.add(this.googleAuthenticatorFieldset);		
	}),
	
	setEnabled : function(enabled) {
		var checkBox = this.googleAuthenticatorFieldset.getComponent('enableGoogleAuthenticatorCheck');			
		// Suspend the event on form load
		checkBox.suspendEvents(false);	
		checkBox.setValue(enabled);
		checkBox.originalValue = checkBox.getValue();

		// Resume the event
		checkBox.resumeEvents();
	},
	
	setQr : function(enable, secret, url){
		var qrFieldSet = this.googleAuthenticatorFieldset.getComponent('googleAuthenticatorQRFieldSet');
		var qrCodeField = qrFieldSet.getComponent('googleAuthenticatorQRcodeField');
		var secretField = qrFieldSet.getComponent('googleAuthenticatorSecret');
		
		// Suspend the event on form load
		this.setEnabled(enable);
		
		if(enable){
			qrFieldSet.expand();
			
			if(url){ // url is not set when only the checkbox checked state needs to be changed
				qrCodeField.setQRUrl(url);
			}
			
			if(secret){ // secret is not set when only the checkbox checked state needs to be changed
				secretField.setValue(secret);
			}
			
			secretField.focus();
		} else {
			qrFieldSet.collapse();
			qrCodeField.setQRUrl(Ext.BLANK_IMAGE_URL);
			secretField.setValue(t('No secret available'));
		}
		
		// Set the isDirty() check to false. The form doesn't need to check this
		secretField.originalValue = secretField.getValue();	
	},
	
	enableGoogleAuthenticator : function(){
		
		var passwordPrompt = new go.PasswordPrompt({
			width: dp(450),
			text: t("When enabling Google autenticator you'll need to scan the QR code with the Google authenticator app otherwise you cannot login to Group-Office anymore.",'googleauthenticator')+"<br><br>"+t("Provide your current password to enable Google authenticator.",'googleauthenticator'),
			title: t('Enable Google authenticator','googleauthenticator'),
			listeners:{
				'ok': function(value){
					
					var params = {"update": {}};
					params.update[GO.settings.user_id] = {
						currentPassword: value,
						googleauthenticator: {}
					};					
					
					go.Stores.get("community", "get")("community", "User").set(params, function (options,success,response) {								
						if(!success || GO.util.empty(response.updated)) {
							return this.enableGoogleAuthenticator();
						}
						
						var user = response.updated[GO.settings.user_id];
						if(user.googleauthenticator){							
							this.setQr(true, user.googleauthenticator.secret ,user.googleauthenticator.qrUrl);
						}
					}, this);
				},
				'cancel': function(){
					this.setEnabled(false);
				},
				scope:this
			}
		});

		passwordPrompt.show();
	},
	
	disableGoogleAuthenticator : function(){
		
		var passwordPrompt = new go.PasswordPrompt({
			width: dp(450),
			text: t("When disabling Google autenticator this step will be removed from the login process.",'googleauthenticator')+"<br><br>"+t("Provide your current password to disable Google authenticator.",'googleauthenticator'),
			title: t('Disable Google authenticator','googleauthenticator'),
			listeners:{
				'ok': function(value){
					
				
					var params = {"update": {}};
					params.update[GO.settings.user_id] = {
						currentPassword: value,
						googleauthenticator:null
					};					
					
					go.Stores.get("community", "get")("community", "User").set(params, function (options,success,response) {								
						if(success && !GO.util.empty(response.updated)) {
							this.setQr(false);						
						} else
						{
							this.disableGoogleAuthenticator();				
						}
						
					}, this);
				},
				'cancel': function(){
					this.setEnabled(true);
				},
				scope:this
			}
		});

		passwordPrompt.show();
	}
});