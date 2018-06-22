go.systemsettings.GeneralPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('General'),
			autoScroll: true,
			iconCls: 'ic-description',
			items: [{
					xtype: "fieldset",
					defaults: {
						width: dp(240)
					},
					items: [
						{
							xtype: 'textfield',
							name: 'title',
							fieldLabel: t('Title'),
							hint: t("Used as page title and sender name for notifications")
						},
						this.languageCombo = new Ext.form.ComboBox({
							fieldLabel: t('Language'),
							name: 'language',
							store: new Ext.data.SimpleStore({
								fields: ['id', 'language'],
								data: GO.Languages
							}),
							displayField: 'language',
							valueField: 'id',
							hiddenName: 'language',
							mode: 'local',
							triggerAction: 'all',
							editable: false,
							selectOnFocus: true,
							forceSelection: true,
							hint: t("The language is automatically detected from the browser. If the language is not available then this language will be used.")
						}), {
							xtype: 'textfield',
							name: 'URL',
							fieldLabel: t('URL'),
							hint: t("The full URL to Group-Office.")
						}
					]
				}, {
					xtype: "fieldset",
					items: [
						{
							xtype: 'xcheckbox',
							name: 'maintenanceMode',
							hideLabel: true,
							boxLabel: t('Enable maintenance mode'),
							hint: t("When maintenance mode is enabled only administrators can login")
						}, {
							xtype: "xhtmleditor",
							anchor: "100%",
							height: dp(200),
							name: 'loginMessage',
							fieldLabel: t("Login message"),
							hint: t("This message will show on the login screen")
						}
					]
				}, {
					xtype: "fieldset",
					title: t("Appearance"),
					items: [
						this.logoField = new go.form.FileField({
							fieldLabel: t("Logo"),
							buttonOnly: true,
							name: 'logoId',
							height:dp(72),
							cls: "go-settings-logo",
							autoUpload: true,
							buttonCfg: {
								text: '',
								width: dp(272)
							},
							setValue: function(val) {
								if(this.rendered && !Ext.isEmpty(val)) {
									this.wrap.setStyle('background-image', 'url('+go.Jmap.downloadUrl(val)+')');
								}
								go.form.FileField.prototype.setValue.call(this,val);
							},
							accept: 'image/*'
						}),
						
						this.colorField = new GO.form.ColorField({
							listeners: {
								scope: this,
								change: function(field, color) {
									document.body.style.setProperty('--c-primary', '#' + color);
								}
							},
							fieldLabel: t("Primary color"),
							showHexValue: true,
							value: null,
							width: 200,
							name: 'primaryColor',
							colors: [
								'009BC9', //Group-Office blue
								'0E3B83', //Intermesh blue
								
								'C62828', //al 800 variants of Material design
								'AD1457',
								'6A1B9A',
								'4527A0',
								'283593',
								'1565C0',
								'0277BD',
								'00838F',
								'00695C',
								'2E7D32',
								'558B2F',
								'9E9D24',
								'F9A825',
								'FF8F00',
								'EF6C00',
								'D84315',
								'4E342E',
								'424242',
								'37474F',
								'000000'
							]


						})
					]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);

		this.on('render', function () {
			go.Jmap.request({
				method: "core/core/Settings/get",
				callback: function (options, success, response) {
					this.getForm().setValues(response);
				},
				scope: this
			});
		}, this);
	},

	onSubmit: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, this, success);
			},
			scop: scope
		});
	}


});

