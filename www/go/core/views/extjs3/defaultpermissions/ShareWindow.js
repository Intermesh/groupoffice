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

				var me = this;

				Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to add the default groups to all items? WARNING: This can't be undone."), function (btn) {

					if (btn !== "yes") {
						return;
					}

					Ext.getBody().mask(t("Changing permissions..."));

					me.submit().then(function(serverId) {

						return go.Jmap.request({
							method: "Acl/reset",
							params: {
								add: true,
								entity: me.forEntityStore
							}
						});
					}).catch(function(){
						if (!success) {
							Ext.getBody().unmask();
							Ext.MessageBox.alert(t("Error"), t("Failed to save default permissions"));
						}
					}).finally(function(){
						Ext.getBody().unmask();
					});
				});
			},
			scope: this
		}));

		this.getFooterToolbar().insert(0, new Ext.Button({
			text: t('Reset all'),
			handler: function () {

				var me = this;

				Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to reset all permissions? WARNING: This can't be undone."), function (btn) {

					if (btn !== "yes") {
						return;
					}

					Ext.getBody().mask("Changing permissions...");

					me.submit().then(function (serverId) {

						return go.Jmap.request({
							method: "Acl/reset",
							params: {
								add: false,
								entity: me.forEntityStore
							}
						});
					}).catch(function(){
						if (!success) {
							Ext.getBody().unmask();
							Ext.MessageBox.alert(t("Error"), t("Failed to save default permissions"));
						}
					}).finally(function(){
						Ext.getBody().unmask();
					})
				})
			},
			scope: this
		}));
	}

});
