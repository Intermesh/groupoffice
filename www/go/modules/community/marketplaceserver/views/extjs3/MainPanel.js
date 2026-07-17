/* global go, Ext, dp, t */

/**
 * Marketplace server ADMIN panel (manager-only). Products/Releases/Customers/
 * Entitlements + Settings. There is deliberately no customer-facing surface —
 * customers never log into the server; they self-register and manage their
 * account through the community/marketplace client over the public API.
 *
 * Extends go.modules.ModulePanel (the module main-panel convention) with a
 * nested Ext.TabPanel — NOT a raw Ext.TabPanel. The module launcher icon comes
 * from Module.js (iconCls: 'ic-store'); the panel/tabs carry no iconCls (a
 * material-icon class on a bare panel/tab header renders as its ligature text,
 * e.g. literal "store", because that element isn't given the icon font).
 */
go.modules.community.marketplaceserver.MainPanel = Ext.extend(go.modules.ModulePanel, {
	// Panel id == module name (the notes convention): the module route is
	// registered as ^marketplaceserver$ (Modules.js), and the main layout mirrors
	// the active panel's id into the URL hash. Keeping them equal makes the hash a
	// clean #marketplaceserver that actually matches the route (so reload/bookmark
	// re-opens the module). A package-prefixed id gave #community-marketplaceserver,
	// which no route matched.
	id: "marketplaceserver",
	title: t("Marketplace server", "marketplaceserver", "community"),
	layout: 'fit',

	initComponent: function () {
		var items = [];

		var isManager = go.Modules.isAvailable("community", "marketplaceserver", go.permissionLevels.manage);

		// Admin-only module: catalog/customers/entitlements + config. There is
		// deliberately NO customer-facing surface here — self-registered customers
		// never access this module; they act through the community/marketplace client
		// over the public API.
		if (isManager) {
			items.push(new go.modules.community.marketplaceserver.ProductGrid({title: t("Products", "marketplaceserver", "community")}));
			items.push(new go.modules.community.marketplaceserver.ReleaseGrid({title: t("Releases", "marketplaceserver", "community")}));
			items.push(new go.modules.community.marketplaceserver.CustomerGrid({title: t("Customers", "marketplaceserver", "community")}));
			items.push(new go.modules.community.marketplaceserver.EntitlementPanel({title: t("Entitlements", "marketplaceserver", "community")}));
			items.push(new go.modules.community.marketplaceserver.ActivityPanel({title: t("Activity", "marketplaceserver", "community")}));
			items.push(new go.modules.community.marketplaceserver.SettingsPanel());
		}

		this.tabPanel = new Ext.TabPanel({
			activeTab: 0,
			border: false,
			items: items
		});

		Ext.apply(this, {items: [this.tabPanel]});

		go.modules.community.marketplaceserver.MainPanel.superclass.initComponent.call(this);
	}
});
