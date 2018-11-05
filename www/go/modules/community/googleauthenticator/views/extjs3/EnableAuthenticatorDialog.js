go.googleauthenticator.EnableAuthenticatorDialog = Ext.extend(go.form.Dialog, {
	title:t('Enable Google authenticator', 'googleauthenticator'),
	iconCls: 'ic-security',
	modal:true,
	entityStore:"User",
	width: 400,
	height: 500,

	initFormItems: function () {
		
		this.QRcomponent = new Ext.BoxComponent({
			name:'googleauthenticator.qrUrl',
			qrUrl: Ext.BLANK_IMAGE_URL,
			onRender: function (ct, position) {
				this.el = ct.createChild({
					tag: 'img',
					cls: "googleauthenticator-qr",
					src: go.Jmap.downloadUrl(this.qrUrl)
				});
			},
			setQRUrl: function (url) {
				this.qrUrl = url;
				if (this.rendered) {
					this.getEl().dom.src = go.Jmap.downloadUrl(url);
				}
			},
			clearQRUrl: function () {
				this.setQRUrl(Ext.BLANK_IMAGE_URL);
			}
		});
		
		this.secretField = new GO.form.PlainField({
			name:'googleauthenticator.secret',
			fieldLabel: t('Secret', 'googleauthenticator'),
			hint: t('Secret key for manual input', 'googleauthenticator')
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
						html: t('Scan the QR code below with the Google authenticator app on your mobile device, after that fill in the field below with the code generated in the app.', 'googleauthenticator')
					}),
					this.QRcomponent,
					this.secretField,
					this.verifyField
				]
		}];

		return items;
	},
	onLoad : function() {
		this.QRcomponent.setQRUrl(this.formPanel.entity.googleauthenticator.qrUrl);
		go.googleauthenticator.EnableAuthenticatorDialog.superclass.onLoad.call(this);
	}
});
