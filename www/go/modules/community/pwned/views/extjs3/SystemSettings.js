go.Modules.register("community", "pwned");

Ext.onReady(function () {
	Ext.override(go.systemsettings.AuthenticationPanel, {
		initComponent: go.systemsettings.AuthenticationPanel.prototype.initComponent.createSequence(function () {
			this.pwnedFieldSet = new go.modules.community.pwned.AuthenticatorSystemSettingsFieldset();
			this.insert(1, this.pwnedFieldSet);
		})
	});
});

go.modules.community.pwned.AuthenticatorSystemSettingsFieldset = Ext.extend(Ext.form.FieldSet, {
	labelWidth: dp(152),
	title: t('`;-- Have I been Pwned'),

	initComponent: function() {

		this.items = [
			{
				xtype:"box",
				autoEl: "p",
				html: t("Enable 'Have I Been Pwned' checking for users in a specific group", "pwned", "community")
			},
			this.enableForGroupId = new go.groups.GroupComboReset({
				submit: false,
				xtype: "groupcomboreset",
				name: "pwned.enableForGroup",
				value: go.Modules.get("community", "pwned").settings.enableForGroupId
			})
		];

		go.modules.community.pwned.AuthenticatorSystemSettingsFieldset.superclass.initComponent.call(this);

		this.on("added", () => {
			const panel = this.findParentByType("systemsettingspanel");
			panel.pwnedOnSubmit = panel.onSubmit;

			panel.onSubmit = (cb, scope) => {

				const mod = go.Modules.get("community", "pwned");

				go.Db.store("Module").save({
					settings: {
						enableForGroupId: this.enableForGroupId.getValue()
					}
					}, mod.id).then(() => {
					panel.pwnedOnSubmit.call(panel, cb, scope);
				})
			};
		});
	}
});
