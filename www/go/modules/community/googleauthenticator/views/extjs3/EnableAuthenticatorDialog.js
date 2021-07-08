go.modules.community.googleauthenticator.EnableAuthenticatorDialog = Ext.extend(go.form.Dialog, {
	title:t('Enable Google authenticator'),
	iconCls: 'ic-security',
	modal:true,
	entityStore:"User",
	width: 400,
	height: 500,
	showCustomfields:false,
	initComponent: function () {

		go.modules.community.googleauthenticator.EnableAuthenticatorDialog.superclass.initComponent.call(this);		

		this.formPanel.on('beforesubmit', (pnl,values) => {
			values.googleauthenticator.secret = this.secretField.getValue();
		});
	},
	focus: function () {		
		this.verifyField.focus();
	},
	initFormItems: function () {
		
		this.QRcomponent = new go.QRCodeComponent({
			name:'googleauthenticator.qrBlobId',
			cls: "googleauthenticator-qr",
			width: 200,
			height: 200
		});
		
		this.secretField = new Ext.form.TextField({
			readOnly:true,
			name:'googleauthenticator.secret',
			fieldLabel: t('Secret'),
			hint: t('Secret key for manual input')
		});
			
		this.verifyField = new Ext.form.TextField({
			fieldLabel: t('Verify','googleauthenticator'),
			name: 'googleauthenticator.verify',
			allowBlank:false
		});
		
		var items = [{
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: dp(64),
				items: [
					new Ext.Container({
						html: t('Scan the QR code below with the Google authenticator app on your mobile device, after that fill in the field below with the code generated in the app.')
					}),
					this.QRcomponent,
					this.secretField,
					this.verifyField
				]
		}];

		return items;
	},
	
	onLoad : function() {
		this.QRcomponent.setQrBlobId(this.formPanel.entity.googleauthenticator.qrBlobId);
		go.modules.community.googleauthenticator.EnableAuthenticatorDialog.superclass.onLoad.call(this);

		const enforceForGroupId = go.Modules.get("community", "googleauthenticator").settings.enforceForGroupId;

		const user =  this.getValues()
		if(enforceForGroupId && user.groups.indexOf(enforceForGroupId) > -1) {
			this.formPanel.items.first().insert(0, {
				xtype: 'box',
				autoEl: 'p',
				cls: 'info',
				html: "<i class='icon'>info</i> " + t("Your system administrator requires you to setup two factor authentication")
			});

			this.doLayout();
		}
	}
});
