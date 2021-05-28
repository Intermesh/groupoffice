go.googleauthenticator.EnableAuthenticatorDialog = Ext.extend(go.form.Dialog, {
	title:t('Enable Google authenticator'),
	iconCls: 'ic-security',
	modal:true,
	entityStore:"User",
	width: 400,
	height: 500,
	showCustomfields:false,

	initComponent: function () {

		go.googleauthenticator.EnableAuthenticatorDialog.superclass.initComponent.call(this);		
		var me = this;
		this.formPanel.on('beforesubmit', function(pnl,values){
			values.googleauthenticator.secret = me.secretField.getValue();
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
		go.googleauthenticator.EnableAuthenticatorDialog.superclass.onLoad.call(this);
	}
});
