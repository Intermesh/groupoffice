/* global go */

go.modules.community.wopi.ServiceDialog = Ext.extend(go.form.Dialog, {
	title: t('Service'),
	entityStore: "WopiService",
	width: dp(1000),
	height: dp(800),
	formPanelLayout: "fit",
	resizable: true,
	maximizable: true,
	collapsible: true,
	modal: false,


	onLoad : function(v) {
		this.formPanel.form.findField('useAltWopiClientUri').setValue(!!v.wopiClientUri);
	},

	onBeforeSubmit : function() {
		if(!this.formPanel.form.findField('useAltWopiClientUri').getValue()) {
			this.formPanel.values.wopiClientUri = null;
		}

		return true;
	},

	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel({
			addLevel: go.permissionLevels.write,
			levels: [
				go.permissionLevels.read,
				go.permissionLevels.write
			]
		}));

		return [{
			xtype: 'fieldset',
			layout: "border",
			items: [{
				region: "center",
				xtype: "panel",
				layout: "form",
				defaults: {
					anchor: '100%'
				},
				items: [{
					xtype: 'textfield',
					name: 'url',
					fieldLabel: t("URL"),
					allowBlank: false
				},{
					submit: false,
					name: 'useAltWopiClientUri',
					xtype: 'checkbox',
					hideLabel: true,
					boxLabel: t("Use alternative WOPI client URI (Only for Microsoft Office Online"),
					listeners: {
						scope: this,
						check: function(cb, checked) {
							this.formPanel.form.findField('wopiClientUri').setDisabled(!checked);
						}
					}
				},
				{
					xtype: 'textfield',
					name: 'wopiClientUri',
					fieldLabel: t("WOPI client URI"),
					allowBlank: false,
					disabled: true,
					value: go.Modules.get('core', 'core').settings.URL + "/wopi/",
					hint: t("Only for use with Microsoft Office Online. WOPI traffic must be tunneled through a wopi subdomain.")
				}
				]
			}]
		}
		];
	}

});


