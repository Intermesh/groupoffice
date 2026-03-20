GO.email.TemplatesSettingsDialog = Ext.extend(go.Window, {
	width: dp(800),
	height: dp(500),
	title: t("E-mail templates", "email"),
	autoHeight: true,
	autoScroll: true,
	initComponent: function () {
		this.templateCombo = new GO.email.TemplateCombo({
			hiddenName: "defaultTemplateId",
			fieldLabel: t("Default template"),
			hideLabel: false,
			anchor: "60%",
			value: go.User.emailSettings.defaultTemplateId
		});

		this.templatesGrid = new GO.email.TemplatesGrid({
			anchor: "100%",
			autoHeight: true
		});

		this.form = new Ext.form.FormPanel({
			layout: 'anchor',
			anchor: '100%',
			border: false,
			items: [
				{
					xtype: 'fieldset',
					items: [
						this.templateCombo,
						this.templatesGrid
					]
				}
			],
			bbar: [
				'->',
				{
					xtype: 'button',
					text: t("Save"),
					handler: this.submit,
					scope: this
				}
			]
		});

		this.templateCombo.store.baseParams.permissionLevel = go.permissionLevels.read;

		this.items = this.form;

		GO.email.TemplatesSettingsDialog.superclass.initComponent.call(this);
	},
	submit: function () {
		var me = this;

		go.Db.store("User").single(go.User.id).then(function (user) {
			var update = {};

			update[go.User.id] = {
				emailSettings: {
					defaultTemplateId: me.templateCombo.getValue()
				}
			};

			go.Db.store("User").set({
				update: update
			});

			me.close();
		});
	}
});