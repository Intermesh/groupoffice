/* global go, Ext, dp, t, GO */

/**
 * Create/edit dialog for a marketplace Customer. `userId` is a real entity
 * property (the user picker submits under hiddenName 'userId'), so managers can
 * provision a customer for any GO user — this is the manual-creation path used
 * when self-registration is disabled.
 *
 * `enabled` is a virtual entity property (Customer::getEnabled/setEnabled): it
 * mirrors the linked core_user's enabled flag, so the "Active" xcheckbox is a
 * plain saved form field — checking it and pressing Save toggles the account
 * (the entity applies it to the user server-side, stamping verifiedAt on the
 * first enable). New customers default to Active.
 */
go.modules.community.marketplaceserver.CustomerDialog = Ext.extend(go.form.Dialog, {
	entityStore: "MarketplaceServerCustomer",
	title: t("Customer", "marketplaceserver", "community"),
	titleField: 'companyName',
	redirectOnSave: false, // not routable from a main NavGrid — managed from the marketplace admin tab
	width: dp(600),
	height: dp(560),

	initFormItems: function () {
		var me = this;

		return [{
			xtype: 'fieldset',
			defaults: {anchor: '100%'},
			items: [
				me.userCombo = new go.form.ComboBox({
					// userId is a real Customer property; the picker lists GO users
					// so a manager can attach the customer to any account. Disabled
					// on edit (reassigning the user isn't supported — delete +
					// recreate instead).
					hiddenName: 'userId',
					fieldLabel: t("User"),
					store: {
						xtype: 'gostore',
						fields: ['id', 'displayName', 'email'],
						entityStore: "User",
						sortInfo: {field: 'displayName', direction: 'ASC'}
					},
					valueField: 'id',
					displayField: 'displayName',
					triggerAction: 'all',
					forceSelection: true,
					allowBlank: false
				}),
				{
					xtype: 'textfield',
					name: 'companyName',
					fieldLabel: t("Company", "marketplaceserver", "community")
				},
				{
					xtype: 'numberfield',
					name: 'maxInstances',
					fieldLabel: t("Instance seats", "marketplaceserver", "community"),
					allowDecimals: false,
					allowNegative: false,
					value: 1
				},
				{
					xtype: 'box',
					style: 'padding:0 0 6px',
					html: '<small>' + Ext.util.Format.htmlEncode(
						t("Max concurrent instances that may hold seat-mode licenses. 0 = unlimited. Hostname-pinned entitlements don't use seats.", "marketplaceserver", "community")
					) + '</small>'
				},
				{
					xtype: 'textfield',
					name: 'stripeCustomerId',
					fieldLabel: t("Stripe customer ID", "marketplaceserver", "community")
				},
				{
					xtype: 'textarea',
					name: 'notes',
					fieldLabel: t("Notes", "marketplaceserver", "community"),
					height: dp(80)
				},
				{
					// Virtual entity property (Customer::getEnabled/setEnabled) —
					// a plain saved field: on load it reflects the linked user's
					// state, on save it toggles the account. New customers default
					// Active (checked); on edit the form load overwrites this.
					xtype: 'xcheckbox',
					name: 'enabled',
					fieldLabel: t("Active", "marketplaceserver", "community"),
					checked: true
				}
			]
		}];
	},

	onLoad: function (entityValues) {
		go.modules.community.marketplaceserver.CustomerDialog.superclass.onLoad.call(this, entityValues);

		// Existing customer: the linked user can't be reassigned.
		this.userCombo.disable();
	}
});
