go.usersettings.AppPasswordDialog = Ext.extend(go.form.Dialog, {
	title: t('New app password', 'users', 'core'),
	entityStore: "AppPassword",
	width: dp(400),
	height: dp(350),
	closeOnSubmit: false,
	showCustomfields: false,
	showLinks: false,
	titleField: null,

	initFormItems: function () {

		this.labelField = new Ext.form.TextField({
			fieldLabel: t('Label', 'users', 'core'),
			name: 'label',
			allowBlank: false,
			anchor: '100%'
		});

		this.scopesField = new go.form.Chips({
			fieldLabel: t('Protocols', 'users', 'core'),
			name: 'scopes',
			valueField: 'value',
			displayField: 'text',
			anchor: '100%',
			comboStore: new Ext.data.JsonStore({
				data: [
					{value: 'dav', text: t('File mount (dav)', 'users', 'core')},
					{value: 'caldav', text: t('CalDAV', 'users', 'core')},
					{value: 'carddav', text: t('CardDAV', 'users', 'core')},
					{value: 'activesync', text: t('ActiveSync', 'users', 'core')}
				],
				id: 'value',
				fields: ['value', 'text']
			}),
			getValue: function () {
				return go.form.Chips.prototype.getValue.call(this).map(function (v) {
					return {protocol: v};
				});
			},
			setValue: function (values) {
				values = (values || []).map(function (v) {
					return v.protocol;
				});
				go.form.Chips.prototype.setValue.call(this, values);
			}
		});

		return [
			{
				xtype: "fieldset",
				items: [
					this.labelField, this.scopesField
				]
			}
		];
	},

	onBeforeSubmit: function () {
		this.secret = window.GOUI.secrets.appPassword();

		this.setValues({passwordHash: this.secret});
		return true;
	},

	onSubmit: function (success, serverId) {
		if (!success) {
			return;
		}

		this.closeWithModifications = true;
		this.close();

		new go.usersettings.AppPasswordSecretWindow({
			secret: this.secret
		}).show();
	}
});

go.usersettings.AppPasswordSecretWindow = Ext.extend(Ext.Window, {

	initComponent: function () {

		this.secretField = new Ext.form.TextField({
			value: this.secret,
			readOnly: true,
			selectOnFocus: true,
			anchor: '100%',
			style: 'font-family:monospace;font-size:16px;text-align:center;'
		});

		Ext.apply(this, {
			title: t('App password created', 'users', 'core'),
			modal: true,
			closable: false,
			width: dp(420),
			height: dp(250),
			layout: 'form',
			bodyStyle: 'padding:10px;',
			labelAlign: 'top',
			items: [
				{
					xtype: 'label',
					html: t("Copy this password now. You won't be able to see it again.", 'users', 'core'),
					style: 'display:block;margin-bottom:10px;'
				},
				this.secretField
			],
			buttons: [
				{text: t('Copy', 'users', 'core'), handler: this.onCopyClick, scope: this},
				{text: t('Close'), cls: 'x-btn-primary', handler: this.close, scope: this}
			]
		});

		go.usersettings.AppPasswordSecretWindow.superclass.initComponent.call(this);
	},

	onCopyClick: function () {
		this.secretField.selectText();
		navigator.clipboard.writeText(this.secret);
	}
});
