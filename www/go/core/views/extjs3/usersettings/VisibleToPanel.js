go.usersettings.VisibleToPanel = Ext.extend(Ext.Panel, {
	title: t("Visible to"),
	iconCls: 'ic-visibility',
	layout: "fit",
	initComponent: function() {

		this.items = [this.sharePanel = new go.permissions.SharePanel({
			header: false,
			name: "personalGroup.acl",
			showLevels: false,
			disabled: !go.User.isAdmin
		})];

		this.supr().initComponent.call(this);
	}
});