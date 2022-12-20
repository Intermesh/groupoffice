go.modules.community.otp.EnableAuthenticatorDialog = Ext.extend(go.form.Dialog, {
	title:t('Enable OTP Authenticator'),
	iconCls: 'ic-security',
	modal:true,
	entityStore:"User",
	width: dp(580),
	height: dp(680),
	showCustomfields:false,
	closable: false,
	maximizable: false,
	block : false,
	countDown: 0,
	initComponent: function () {

		go.modules.community.otp.EnableAuthenticatorDialog.superclass.initComponent.call(this);

		this.formPanel.on('beforesubmit', (pnl,values) => {
			values.otp.secret = this.secretField.getValue();
		});

		this.formPanel.loadExternalChanges = false;
	},
	focus: function () {		
		this.verifyField.focus();
	},

	initButtons : function() {
		go.modules.community.otp.EnableAuthenticatorDialog.superclass.initButtons.call(this);

		if(!this.block) {
			this.buttons.splice(0,0, this.setupLaterButton = new Ext.Button({
				disabled: this.countDown > 0,
				text: t("Setup later"),
				handler: function() {
					this.close();
				},				scope: this
			}));

			this.countDown = parseInt(this.countDown);

			if(this.countDown) {
				let countDown = this.countDown;

				this.setupLaterButton.setText(t("Setup later") + " (" + countDown-- + ")");

				const interval = setInterval(() => {
					let text = t("Setup later") + " (" + countDown-- + ")"
					if(countDown == -1) {
						text = t("Setup later");
						this.setupLaterButton.setDisabled(false);
						clearInterval(interval);
					}
					this.setupLaterButton.setText(text);
				}, 1000);
			}
		}

	},

	actionComplete : function() {
		go.modules.community.otp.EnableAuthenticatorDialog.superclass.actionComplete.call(this);
		// if(this.setupLaterButton) { //<- Will return an error while still saving authenticator code correctly
		if(this.setupLaterButton && this.setupLaterButton.el.dom) {
			this.setupLaterButton.setDisabled(this.countDown > 0);
		}
	},

	initFormItems: function () {
		
		this.QRcomponent = new go.QRCodeComponent({
			name:'otp.qrBlobId',
			cls: "otp-qr",
			width: 200,
			height: 200
		});
		
		this.secretField = new Ext.form.TextField({
			readOnly:true,
			name:'otp.secret',
			fieldLabel: t('Secret'),
			hint: t('Secret key for manual input'),
			anchor: "100%"

		});
			
		this.verifyField = new go.form.PasteButtonField({
			fieldLabel: t('Verify','otp'),
			name: 'otp.verify',
			allowBlank:false,
			anchor: "100%"
		});
		
		var items = [{
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: dp(64),
				items: [
					new Ext.Container({
						html: t('Scan the QR code below with the OTP Authenticator app on your mobile device, after that fill in the field below with the code generated in the app.')
					}),
					this.QRcomponent,
					this.secretField,
					this.verifyField
				]
		}];

		return items;
	},
	
	onLoad : function() {
		this.QRcomponent.setQrBlobId("community/otp/qr");
		go.modules.community.otp.EnableAuthenticatorDialog.superclass.onLoad.call(this);

		const user =  this.getValues()
		if(go.modules.community.otp.isEnforced(user)) {
			this.formPanel.items.first().insert(0, {
				xtype: 'box',
				autoEl: 'p',
				cls: 'info',
				html: "<i class='icon'>info</i> " + t("Your system administrator requires you to setup two factor authentication")
			});
			if(!this.maximized) {
				this.setHeight(650);

			}
			this.doLayout();

		}
	}
});
