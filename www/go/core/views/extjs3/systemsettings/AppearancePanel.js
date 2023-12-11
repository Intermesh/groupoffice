go.systemsettings.AppearancePanel = Ext.extend(go.systemsettings.Panel, {
	hasPermission() {
		return go.User.isAdmin;
	},
	initComponent() {
		const resetLogoButton = new Ext.Button({iconCls: 'ic-delete', disabled: true, tooltip: t("Reset"), handler: () => {
			this.logoField.setValue(null);
		}}),
			resetLogoButtonD = new Ext.Button({iconCls: 'ic-delete', disabled: true, tooltip: t("Reset"), handler: () => {
			this.logoField.setValue(null);
		}});

		Ext.apply(this, {
			title: t('Appearance'),
			itemId: "appearance", //makes it routable
			autoScroll: true,
			iconCls: 'ic-palette',
			layout:'hbox',
			items: [{
				xtype: "fieldset",
				items: [
					{xtype: "label",html: t("Light")},
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
								buttonCfg: {text: '',width: dp(272)},
								setValue(val) {
									if (this.rendered) {
										resetLogoButton.setDisabled(Ext.isEmpty(val));
										if(!Ext.isEmpty(val)) {
											this.wrap.setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
											Ext.get('go-logo').setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
										} else {
											this.wrap.dom.style.removeProperty('background-image');
											Ext.get('go-logo').dom.style.removeProperty('background-image');
										}
									}
									go.form.FileField.prototype.setValue.call(this, val);
								},
								accept: 'image/*'
							}),
							resetLogoButton
						]
					},
					this.createColorField('primaryColor', "Primary color", '--c-primary', "1652A1"),
					this.createColorField('secondaryColor', "Secondary color", '--c-secondary', "00B0AD"),
					this.createColorField('tertiaryColor', "Tertiary color", '--c-tertiary', "F3DB00"),
					this.createColorField('accentColor', "Accent color", '--c-accent', "FF7200")
				]
			},{
				xtype: "fieldset",
				items: [
					{xtype: "label",html: t("Dark")},
					{
						xtype: 'compositefield',
						items: [
							this.logoField = new go.form.FileField({
								hideLabel: true,
								buttonOnly: true,
								name: 'logoIdDark',
								height: dp(72),
								cls: "go-settings-logo",
								autoUpload: true,
								buttonCfg: {text: '',width: dp(272)},
								setValue(val) {
									if (this.rendered) {
										resetLogoButtonD.setDisabled(Ext.isEmpty(val));
										if(!Ext.isEmpty(val)) {
											this.wrap.setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
											Ext.get('go-logo').setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
										} else {
											this.wrap.dom.style.removeProperty('background-image');
											Ext.get('go-logo').dom.style.removeProperty('background-image');
										}
									}
									go.form.FileField.prototype.setValue.call(this, val);
								},
								accept: 'image/*'
							}),
							resetLogoButtonD
						]
					},
					this.createColorField('primaryDark', "Primary color", '--c-primary', "1652A1"),
					this.createColorField('secondaryDark', "Secondary color", '--c-secondary', "00B0AD"),
					this.createColorField('tertiaryDark', "Tertiary color", '--c-tertiary', "F3DB00"),
					this.createColorField('accentDark', "Accent color", '--c-accent', "FF7200")
				]
			}]
		});

		this.supr().initComponent.call(this);
	},

	createColorField(name, label, property, defaultColor) {
		return new GO.form.ColorField({
			fieldLabel: t(label),
			showHexValue: true,
			value: null,
			width: 180,
			name: name,
			dark: true,
			listeners: {
				'change': (field, color) => {
					document.body.style.setProperty(property, '#' + (color || defaultColor));
				}
			}
		})
	}
});