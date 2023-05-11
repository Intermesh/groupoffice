go.systemsettings.AppearancePanel = Ext.extend(go.systemsettings.Panel, {
	hasPermission: function() {
		return go.User.isAdmin;
	},
	initComponent: function () {
		var me = this;
		Ext.apply(this, {
			title: t('Appearance'),
			itemId: "appearance", //makes it routable
			autoScroll: true,
			iconCls: 'ic-palette',
			items: [{
					xtype: "fieldset",
					items: [
						{
							xtype: "label",
							html: t("Logo")
						},
						{

							xtype: 'compositefield',
							items: [

								this.logoField = new go.form.FileField({
									hideLabel: true,
									buttonOnly: true,
									name: 'logoId',
									height: dp(72),
									cls: "go-settings-logo",
									autoUpload: true,
									buttonCfg: {
										text: '',
										width: dp(272)
									},
									setValue: function (val) {
										if (this.rendered) {
											me.resetLogoButton.setDisabled(Ext.isEmpty(val));
											if(!Ext.isEmpty(val)) {
												this.wrap.setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
												Ext.get('go-logo').setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
											} else
											{
												this.wrap.dom.style.removeProperty('background-image');
												Ext.get('go-logo').dom.style.removeProperty('background-image');
											}
											
										}
										go.form.FileField.prototype.setValue.call(this, val);
									},
									accept: 'image/*'
								}),
								this.resetLogoButton = new Ext.Button({
									iconCls: 'ic-delete',
									disabled: true,
									tooltip: t("Reset"),
									handler: function() {
										this.logoField.setValue(null);
									},
									scope: this
								})
							]
						},

						this.primaryColorField = new GO.form.ColorField({
							listeners: {
								scope: this,
								change: function (field, color) {
									if (!color) {
										color = "1652A1"; //default color
									}
									document.body.style.setProperty('--c-primary', '#' + color);
								}
							},
							fieldLabel: t("Primary color"),
							showHexValue: true,
							value: null,
							width: 200,
							name: 'primaryColor',
							dark: true

						}),

						this.secondaryColorField = new GO.form.ColorField({
							listeners: {
								scope: this,
								change: function (field, color) {
									if (!color) {
										color = "00B0AD"; //default color
									}
									document.body.style.setProperty('--c-secondary', '#' + color);
								}
							},
							fieldLabel: t("Secondary color"),
							showHexValue: true,
							value: null,
							width: 200,
							name: 'secondaryColor',
							dark: true

						}),

						this.tertiaryColorField = new GO.form.ColorField({
							listeners: {
								scope: this,
								change: function (field, color) {
									if (!color) {
										color = "F3DB00";
									}
									document.body.style.setProperty('--c-tertiary', '#' + color);

								}
							},
							fieldLabel: t("Tertiary color"),
							showHexValue: true,
							value: null,
							width: 200,
							name: 'tertiaryColor',
							dark: true

						}),

						this.accentColorField = new GO.form.ColorField({
							listeners: {
								scope: this,
								change: function (field, color) {
									if (!color) {
										color = "FF7200"; //default color
									}
									document.body.style.setProperty('--c-accent', '#' + color);
								}
							},
							fieldLabel: t("Accent color"),
							showHexValue: true,
							value: null,
							width: 200,
							name: 'accentColor',
							dark: true

						})
					]
				}]
		});

		go.systemsettings.AppearancePanel.superclass.initComponent.call(this);
	}
});


