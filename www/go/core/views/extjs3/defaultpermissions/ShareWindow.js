/**
 * var aclId = 1;
 * var dlg = new go.permissions.ShareWindow();
 * dlg.load(aclId).show();
 */
go.defaultpermissions.ShareWindow = Ext.extend(go.form.Dialog, {
	title: t('Set default permissions'),
	entityStore: "Module",
	height: dp(600),
	width: dp(1000),
	formPanelLayout: "fit",

	forEntityStore: null,

	initFormItems : function() {
		return new go.permissions.SharePanel({
			title: null,
			hideLabel: true,
			name: 'entities.' + this.forEntityStore + '.defaultAcl'
		});
	},

	initComponent: function () {
		go.defaultpermissions.ShareWindow.superclass.initComponent.call(this);

		this.getFooterToolbar().insert(0, new Ext.Button({
			text: t('Add to all'),
			handler: function () {
				Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to add the default groups to all items? WARNING: This can't be undone."), function (btn) {

					if (btn !== "yes") {
						return;
					}

					Ext.getBody().mask(t("Changing permissions..."));

					this.submit(function (success, serverId) {

						if (!success) {
							
							tExt.getBody().unmask();
							Ext.MessageBox.alert(t("Error"), t("Failed to save default permissions"));
						}

						go.Jmap.request({
							method: "Acl/reset",
							params: {
								add: true,
								entity: this.forEntityStore
							},
							callback: function (options, success, response) {
								Ext.getBody().unmask();
								if (!success) {
									Ext.MessageBox.alert(t("Error"), t("Failed to reset permissions"));
								}
							},
							scope: this
						});
					}, this);

				}, this);
			},
			scope: this
		}));

		this.getFooterToolbar().insert(0, new Ext.Button({
			text: t('Reset all'),
			handler: function () {
				Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to reset all permissions? WARNING: This can't be undone."), function (btn) {

					if (btn !== "yes") {
						return;
					}

					Ext.getBody().mask("Changing permissions...");

					this.submit(function (success, serverId) {

						if (!success) {
							Ext.getBody().unmask();
							Ext.MessageBox.alert(t("Error"), t("Failed to save default permissions"));
						}


						go.Jmap.request({
							method: "Acl/reset",
							params: {
								add: false,
								entity: this.forEntityStore
							},
							callback: function (options, success, response) {
								Ext.getBody().unmask();
								if (!success) {
									Ext.MessageBox.alert(t("Error"), t("Failed to reset permissions"));
								}
							},
							scope: this
						});
					}, this);
				}, this);
			},
			scope: this
		}));
	}

});
