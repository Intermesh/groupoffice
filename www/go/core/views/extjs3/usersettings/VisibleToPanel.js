go.usersettings.VisibleToPanel = Ext.extend(Ext.Panel, {
	title: t("Visible to"),
	iconCls: 'ic-visibility',
	layout: "fit",
	initComponent: function() {

		this.items = [
			this.sharePanel = new go.permissions.SharePanel({
			header: false,
			name: "personalGroup.acl",
			showLevels: false,
			disabled: !go.Modules.get("core", "core").userRights.mayChangeUsers,
		})];

		this.supr().initComponent.call(this);
	},

	onLoad : function(user) {

		this.sharePanel.store.setFilter("inAcl", {inAcl: {entity: "Group", id: user.personalGroup.id}});

	}
});