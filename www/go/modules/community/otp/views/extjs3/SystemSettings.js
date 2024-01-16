Ext.onReady(function () {
	Ext.override(go.systemsettings.AuthenticationPanel, {
		initComponent: go.systemsettings.AuthenticationPanel.prototype.initComponent.createSequence(function () {
			this.otpFieldset = new go.modules.community.otp.AuthenticatorSystemSettingsFieldset();
			this.insert(1, this.otpFieldset);
		})
	});
});

go.modules.community.otp.AuthenticatorSystemSettingsFieldset = Ext.extend(Ext.form.FieldSet, {
	labelWidth: dp(152),
	title: t('OTP Authenticator'),

	initComponent: function() {

		this.items = [
			{
				xtype:"box",
				autoEl: "p",
				html: t("Enforce two factor authentication for users in a specific group", "otp", "community")
			},
			this.enforceForGroup = new go.groups.GroupComboReset({
				submit: false,
				xtype: "groupcomboreset",
				name: "otp.enforceForGroupId",
				value: go.Modules.get("community", "otp").settings.enforceForGroupId
			}),

			this.blockField = new Ext.form.Checkbox({
				submit: false,
				xtype: "checkbox",
				boxLabel: t("Block Group-Office usage until setup is done", "otp", "community"),
				name: "block",
				checked: go.Modules.get("community", "otp").settings.block,
				listeners: {
					check: (cb, checked) => {
						this.countDown.setDisabled(checked);
					}
				}
			}),
			this.countDown = new go.form.NumberField({
				submit: false,
				disabled: go.Modules.get("community", "otp").settings.block,
				xtype: "numberfield",
				decimals: 0,
				name: "countDown",
				value: go.Modules.get("community", "otp").settings.countDown,
				fieldLabel: t("Count down"),
				hint: t("Count down this number of seconds until the user can cancel the setup")
			})
		];

		go.modules.community.otp.AuthenticatorSystemSettingsFieldset.superclass.initComponent.call(this);

		this.on("added", () => {
			const panel = this.findParentByType("systemsettingspanel");
			panel.gaOnSubmit = panel.onSubmit;

			panel.onSubmit = (cb, scope) => {

				const mod = go.Modules.get("community", "otp");

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
