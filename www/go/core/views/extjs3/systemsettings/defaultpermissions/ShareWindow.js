/**
 * var aclId = 1;
 * var dlg = new go.permissions.ShareWindow();
 * dlg.load(aclId).show();
 */
go.systemsettings.defaultpermissions.ShareWindow = Ext.extend(go.permissions.ShareWindow, {
	title: t('Set default permissions'),
	entityStore: "Acl",
	height: dp(600),
	width: dp(800),
	entity: null,

	initComponent: function () {
		go.systemsettings.defaultpermissions.ShareWindow.superclass.initComponent.call(this);

		this.getFooterToolbar().insert(0, new Ext.Button({
			text: t('Add to all'),
			handler: function () {
				Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to add the default groups to all items? WARNING: This can't be undone."), function (btn) {

					if (btn !== "yes") {
						return;
					}

					this.getEl().mask();

					this.submit(function (success, serverId) {

						if (!success) {
							this.getEl().unmask();
							Ext.MessageBox.alert(t("Error"), t("Failed to save default permissions"));
						}

						go.Jmap.request({
							method: "Acl/reset",
							params: {
								add: true,
								entity: this.entity
							},
							callback: function (options, success, response) {
								this.getEl().unmask();
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

					this.getEl().mask();

					this.submit(function (success, serverId) {

						if (!success) {
							this.getEl().unmask();
							Ext.MessageBox.alert(t("Error"), t("Failed to save default permissions"));
						}


						go.Jmap.request({
							method: "Acl/reset",
							params: {
								add: false,
								entity: this.entity
							},
							callback: function (options, success, response) {
								this.getEl().unmask();
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
