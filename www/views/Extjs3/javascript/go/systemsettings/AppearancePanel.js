go.systemsettings.AppearancePanel = Ext.extend(go.systemsettings.Panel, {
	initComponent: function () {
		var me = this;
		Ext.apply(this, {
			title: t('Appearance'),
			autoScroll: true,
			iconCls: 'ic-palette',
			items: [{
					xtype: "fieldset",
					items: [
						{
							xtype: 'compositefield',
							items: [
								this.logoField = new go.form.FileField({
									fieldLabel: t("Logo"),
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

						this.colorField = new GO.form.ColorField({
							listeners: {
								scope: this,
								change: function (field, color) {
									if (!color) {
										color = "009BC9"; //default color
									}
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

		go.systemsettings.AppearancePanel.superclass.initComponent.call(this);
	}
});


