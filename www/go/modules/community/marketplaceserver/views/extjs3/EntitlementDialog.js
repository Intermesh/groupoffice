/* global go, Ext, dp, t */

/**
 * Grant a Product to a customer. The customer is chosen by CONTEXT, not a picker:
 * the Entitlements panel selects a customer on the left and opens this dialog
 * with `formValues:{customerId}` (+ a `customerLabel` for the title); on edit the
 * loaded entity already carries `customerId`. `customerId` is submitted via a
 * hidden field, so no combo is needed — which also sidesteps the unlabelled
 * customer-combo problem (customers often have an empty denormalised companyName).
 */
go.modules.community.marketplaceserver.EntitlementDialog = Ext.extend(go.form.Dialog, {
	entityStore: "MarketplaceServerEntitlement",
	title: t("Entitlement", "marketplaceserver", "community"),
	titleField: null, // Entitlement has no name-like field — title is set from the customer
	redirectOnSave: false, // not routable from a main NavGrid — managed from the marketplace admin tab
	width: dp(800),
	height: dp(460),

	/**
	 * @cfg {String} customerLabel display name of the pre-selected customer (create
	 *      flow) — shown in the dialog title.
	 */

	initFormItems: function () {
		var me = this;

		return [{
			xtype: 'fieldset',
			defaults: {anchor: '100%'},
			items: [
				// customerId is a real Entitlement property; seeded via formValues
				// (create, from the selected customer) or by the base onLoad (edit).
				{xtype: 'hidden', name: 'customerId'},
				// go.form.ComboBox (not a plain Ext 'combo'): on edit it resolves the
				// stored productId back to the product title via the entity store —
				// a plain combo would just display the raw id ("1"). Same pattern as
				// notes' NoteBookCombo.
				new go.form.ComboBox({
					hiddenName: 'productId',
					fieldLabel: t("Product", "marketplaceserver", "community"),
					anchor: '100%',
					emptyText: t("Please select..."),
					pageSize: 50,
					store: {
						xtype: 'gostore',
						fields: ['id', 'title'],
						entityStore: "MarketplaceServerProduct",
						sortInfo: {field: 'sortOrder'}
					},
					valueField: 'id',
					displayField: 'title',
					triggerAction: 'all',
					forceSelection: true,
					allowBlank: false
				}),
				{
					xtype: 'datefield',
					name: 'expiresAt',
					fieldLabel: t("Expires at", "marketplaceserver", "community"),
					emptyText: t("Never", "marketplaceserver", "community"),
					allowBlank: true
				},
				{
					xtype: 'combo',
					hiddenName: 'bindingMode',
					fieldLabel: t("Instance binding", "marketplaceserver", "community"),
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					forceSelection: true,
					value: 'seats',
					valueField: 'value',
					displayField: 'label',
					store: new Ext.data.SimpleStore({
						fields: ['value', 'label'],
						data: [
							['seats', t("Seats (customer instance limit)", "marketplaceserver", "community")],
							['hostname', t("Hostname (pinned to one host)", "marketplaceserver", "community")]
						]
					})
				},
				{
					xtype: 'textfield',
					name: 'boundHostname',
					fieldLabel: t("Bound hostname", "marketplaceserver", "community"),
					emptyText: t("Pinned automatically on first use", "marketplaceserver", "community"),
					allowBlank: true
				},
				{
					xtype: 'box',
					style: 'padding:4px 0 0',
					html: '<small>' + Ext.util.Format.htmlEncode(
						t("Hostname mode licenses the module only on this host (pinned on the first license check; clear it to re-pin to a new host). Seats mode ignores this and uses the customer's instance limit.", "marketplaceserver", "community")
					) + '</small>'
				}
			]
		}];
	},

	initComponent: function () {
		go.modules.community.marketplaceserver.EntitlementDialog.superclass.initComponent.call(this);

		// Create flow: the caller passes the pre-selected customer's name.
		if (this.customerLabel) {
			this.setTitle(t("Entitlement", "marketplaceserver", "community") + ': ' +
				Ext.util.Format.htmlEncode(this.customerLabel));
		}
	},

	onLoad: function (entityValues) {
		go.modules.community.marketplaceserver.EntitlementDialog.superclass.onLoad.call(this, entityValues);

		// Edit flow: resolve the customer for the title (customerId is already
		// seeded into the hidden field by the base onLoad).
		var me = this;
		if (entityValues.customerId) {
			go.Db.store("MarketplaceServerCustomer").single(entityValues.customerId).then(function (customer) {
				if (!customer) {
					return;
				}
				var label = customer.companyName;
				if (label) {
					me.setTitle(t("Entitlement", "marketplaceserver", "community") + ': ' + Ext.util.Format.htmlEncode(label));
					return;
				}
				go.Db.store("User").single(customer.userId).then(function (user) {
					me.setTitle(t("Entitlement", "marketplaceserver", "community") + ': ' +
						Ext.util.Format.htmlEncode((user && (user.displayName || user.email)) || ('#' + customer.id)));
				});
			});
		}
	}
});
