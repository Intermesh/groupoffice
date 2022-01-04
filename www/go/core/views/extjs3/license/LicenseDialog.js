/* global go, Ext */

go.license.LicenseDialog = Ext.extend(go.Window, {
	width: dp(600),
	height: dp(460),
	layout:'fit',
	title: t("Register"),
	maximized: false,
	modal: true,
	cls: "go-license-dialog",
	minWidth : 40,
	minHeight: 40,
	buttonAlign: "left",

	initComponent: function() {

		const coreMod = go.Modules.get("core", "core");

		this.formPanel = new Ext.form.FormPanel({
			labelAlign: "top",
			layout: "fit",
			items: [{
				xtype: "fieldset",
				items:[
					{
						xtype: "box",
						autoEl: "p",
						html: t('Try the extra features for free and obtain a 60 day trial license from <a target="_blank" class="normal-link" href="https://www.group-office.com">www.group-office.com</a>. Register for an account and get your license now. By purchasing a license you will get support and extra features. Find out more on our website.')
					},
					{
						xtype: "button",
						text: t("Get license now"),
						cls: "primary",
						handler: function() {
							window.open('https://www.group-office.com/30-day-trial?hostname=' + document.domain + '&version=' + go.User.session.version ,'_blank');
						}
					},

					this.licenseKeyField = new Ext.form.TextArea({

						anchor: "100% -" + dp(160),
						xtype: "textarea",
						fieldLabel: t("License key"),
						allowBlank: false,
						value: coreMod.settings.license
					})]
				}
			]
		});

		this.items = [this.formPanel];

		this.buttons = [
			{
				text: t("Later"),
				handler: function() {
					this.close();
				},
				scope: this
			}, {
				text: t("No thanks"),
				handler: function() {

					var coreModule = go.Modules.get("core", "core");
					this.getEl().mask(t("Saving..."));
					go.Db.store("Module").save({
						settings: {
							licenseDenied: true,
							license: null
						}
					}, coreModule.id).then(() => {
						this.close();
						go.reload();
					}).finally(() => {
						this.getEl().unmask();
					});
				},
				scope: this
			}, '->', {
				cls: "primary",
				text: t("Save"),
				handler: function() {

					if(!this.formPanel.form.isValid()) {
						Ext.MessageBox.alert(t("Error"), t("Please enter the license key"))
						return;
					}

					var coreModule = go.Modules.get("core", "core");
					this.getEl().mask(t("Saving..."));
					go.Db.store("Module").save({
						settings: {
							licenseDenied: false,
							license: this.licenseKeyField.getValue()
						}
					}, coreModule.id).then(() => {

						return go.Jmap.request({method: "Module/installLicensed"}).then(() => {
							this.close();
							go.reload();
						})

					}).finally(() => {
						this.getEl().unmask();
					});
				},
				scope: this
			}
		]

		go.license.LicenseDialog.superclass.initComponent.call(this);
	}
});
