Ext.onReady(function () {
	Ext.override(go.systemsettings.AuthenticationPanel, {
		initComponent: go.systemsettings.AuthenticationPanel.prototype.initComponent.createSequence(function () {
			this.googleAuthenticatorFieldset = new go.modules.community.googleauthenticator.AuthenticatorSystemSettingsFieldset();
			this.insert(1, this.googleAuthenticatorFieldset);
		})
	});
});

go.modules.community.googleauthenticator.AuthenticatorSystemSettingsFieldset = Ext.extend(Ext.form.FieldSet, {
	labelWidth: dp(152),
	title: t('Google authenticator'),

	initComponent: function() {

		this.items = [
			{
				xtype:"box",
				autoEl: "p",
				html: t("Enforce two factor authentication for users in a specific group")
			},
			this.enforceForGroup = new go.groups.GroupComboReset({
				submit: false,
				xtype: "groupcomboreset",
				name: "googleauthenticator.enforceForGroupId",
				value: go.Modules.get("community", "googleauthenticator").settings.enforceForGroupId
			}),

			this.blockField = new Ext.form.Checkbox({
				submit: false,
				xtype: "checkbox",
				boxLabel: t("Block Group-Office usage until setup is done"),
				name: "block",
				checked: go.Modules.get("community", "googleauthenticator").settings.block,
				listeners: {
					check: (cb, checked) => {
						this.countDown.setDisabled(checked);
					}
				}
			}),
			this.countDown = new go.form.NumberField({
				submit: false,
				disabled: go.Modules.get("community", "googleauthenticator").settings.block,
				xtype: "numberfield",
				decimals: 0,
				name: "countDown",
				value: go.Modules.get("community", "googleauthenticator").settings.countDown,
				fieldLabel: t("Count down"),
				hint: t("Count down this number of seconds until the user can cancel the setup")
			})
		];

		go.modules.community.googleauthenticator.AuthenticatorSystemSettingsFieldset.superclass.initComponent.call(this);

		this.on("added", () => {
			const panel = this.findParentByType("systemsettingspanel");
			panel.gaOnSubmit = panel.onSubmit;

			panel.onSubmit = (cb, scope) => {

				const mod = go.Modules.get("community", "googleauthenticator");

				go.Db.store("Module").save({
					settings: {
						enforceForGroupId: this.enforceForGroup.getValue(),
						block: this.blockField.getValue(),
						countDown: this.countDown.getValue()
					}
					}, mod.id).then(() => {
					panel.gaOnSubmit.call(panel, cb, scope);
				})
			};
		});
	}
});
