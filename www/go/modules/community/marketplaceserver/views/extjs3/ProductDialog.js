/* global go, Ext, dp, t */

/**
 * Create/edit dialog for a marketplace Product. For a type=collection product
 * the member modules (`modules`, an array of moduleName strings) are edited with
 * a go.form.Chips picker whose options are the module-type products. Because the
 * chips field is a real named form field (name:'modules'), go.form.Dialog submits
 * its value straight into the entity's `modules` scalar array — no manual payload
 * injection or phantom-field scrubbing needed.
 */
go.modules.community.marketplaceserver.ProductDialog = Ext.extend(go.form.Dialog, {
	entityStore: "MarketplaceServerProduct",
	title: t("Product", "marketplaceserver", "community"),
	titleField: 'title',
	redirectOnSave: false, // not routable from a main NavGrid — managed from the marketplace admin tab
	width: dp(800),
	height: dp(800),

	initFormItems: function () {
		var me = this;

		// Local store of selectable member modules ({id: moduleName, title}) for
		// the chips picker below. Populated from the module-type products in
		// loadModuleOptions(); the chips field stores/returns the moduleName
		// strings the `modules` scalar array expects.
		me.modulesStore = new Ext.data.JsonStore({
			fields: ['id', 'title'],
			data: []
		});

		return [{
			xtype: 'fieldset',
			defaults: {anchor: '100%'},
			items: [
				{
					xtype: 'textfield',
					name: 'title',
					fieldLabel: t("Title"),
					allowBlank: false
				},
				{
					// go.form.ImageField hardcodes a 120×120 square in its own
					// initComponent, but the enclosing fieldset's
					// defaults:{anchor:'100%'} would stretch it into a deformed
					// rectangle. Wrapping it in a plain fixed-width container (the
					// fieldset default only reaches this container, not the field
					// inside it) keeps the avatar square — same pattern as the
					// contact photo in addressbook ContactDialog.
					xtype: 'container',
					width: dp(136),
					items: [
						me.logoField = new go.form.ImageField({
							name: 'logoBlobId',
							fieldLabel: t("Logo", "marketplaceserver", "community")
						})
					]
				},
				{
					xtype: 'combo',
					hiddenName: 'type',
					fieldLabel: t("Type", "marketplaceserver", "community"),
					store: new Ext.data.ArrayStore({
						fields: ['value', 'label'],
						data: [
							['module', t("Module", "marketplaceserver", "community")],
							['collection', t("Collection", "marketplaceserver", "community")]
						]
					}),
					valueField: 'value',
					displayField: 'label',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					forceSelection: true,
					allowBlank: false,
					value: 'module',
					listeners: {
						select: function (combo, rec) {
							me.onTypeChange(rec.data.value);
						}
					}
				},
				me.moduleNameField = new Ext.form.TextField({
					name: 'moduleName',
					fieldLabel: t("Module name", "marketplaceserver", "community"),
					anchor: '100%',
					allowBlank: false // required for module type; toggled in onTypeChange
				}),
				me.modulesField = new go.form.Chips({
					// A real named form field: go.form.Dialog submits its value
					// (an array of the selected moduleName strings) straight into
					// the entity's `modules` scalar array — no manual payload
					// injection or phantom-field scrubbing needed. Only shown for
					// type=collection.
					name: 'modules',
					hidden: true,
					anchor: '100%',
					valueField: 'id',
					displayField: 'title',
					comboStore: me.modulesStore,
					fieldLabel: t("Member modules", "marketplaceserver", "community")
				}),
				{
					xtype: 'textarea',
					name: 'description',
					fieldLabel: t("Description"),
					height: dp(96)
				},
				{
					xtype: 'numberfield',
					name: 'price',
					fieldLabel: t("Price", "marketplaceserver", "community"),
					decimalPrecision: 2
				},
				{
					// Currency dropdown defaulting to EUR. hiddenName (not name)
					// so the ISO code — not the display text — is submitted and no
					// phantom ext-comp-* key leaks (see ComboBox rule). editable +
					// forceSelection:false lets an admin type any 3-letter code
					// while offering the common ones. Mirrors amd/catalog CurrencyCombo.
					xtype: 'combo',
					hiddenName: 'currency',
					fieldLabel: t("Currency", "marketplaceserver", "community"),
					store: new Ext.data.ArrayStore({
						fields: ['code'],
						data: [['EUR'], ['USD'], ['GBP'], ['CZK'], ['CHF'], ['PLN']]
					}),
					valueField: 'code',
					displayField: 'code',
					mode: 'local',
					triggerAction: 'all',
					editable: true,
					forceSelection: false,
					value: 'EUR'
				},
				{
					xtype: 'textfield',
					name: 'stripePriceId',
					fieldLabel: t("Stripe price ID", "marketplaceserver", "community")
				},
				{
					xtype: 'numberfield',
					name: 'sortOrder',
					fieldLabel: t("Sort order"),
					allowDecimals: false,
					decimalPrecision: 0,
					value: 0
				},
				{
					xtype: 'xcheckbox',
					name: 'active',
					fieldLabel: t("Active", "marketplaceserver", "community"),
					checked: true
				},
				{
					// Optional retirement date: after this day the product is no
					// longer offered to new customers (hidden from the catalog +
					// download refused); existing owners keep access. Blank = always
					// available while Active.
					xtype: 'datefield',
					name: 'availableUntil',
					fieldLabel: t("Available until", "marketplaceserver", "community"),
					allowBlank: true
				}
			]
		}, this.releasesPanel = new Ext.Panel({
			// Read-only list of this module's releases (version × GO branch),
			// so the Release↔Product relation is visible from the product side.
			// Shown only for an existing module-type product.
			title: t("Releases", "marketplaceserver", "community"),
			hidden: true,
			style: 'margin-top: 8px',
			layout: 'fit',
			height: dp(220),
			items: this.releasesGrid = new go.grid.GridPanel({
				border: false,
				enableColumnHide: false,
				enableColumnMove: false,
				store: this.releasesStore = new go.data.Store({
					fields: ['id', 'version', 'goVersion', {name: 'publishedAt', type: 'date'}, 'active'],
					entityStore: "MarketplaceServerRelease",
					sortInfo: {field: 'publishedAt', direction: 'DESC'}
				}),
				columns: [
					{header: t("Version", "marketplaceserver", "community"), dataIndex: 'version', width: dp(120)},
					{header: t("Group-Office branch", "marketplaceserver", "community"), dataIndex: 'goVersion', width: dp(130)},
					{xtype: 'datecolumn', header: t("Published at", "marketplaceserver", "community"), dataIndex: 'publishedAt', width: dp(140)},
					{
						header: t("Active", "marketplaceserver", "community"), dataIndex: 'active', width: dp(70), align: 'center',
						renderer: function (v) { return v ? '<i class="icon ic-check"></i>' : ''; }
					}
				],
				viewConfig: {
					forceFit: true,
					emptyText: '<p>' + t("No releases yet — add them in the Releases tab.", "marketplaceserver", "community") + '</p>'
				}
			})
		})];
	},

	initComponent: function () {
		go.modules.community.marketplaceserver.ProductDialog.superclass.initComponent.call(this);

		// Populate the member-module picker options (all module-type products).
		this.loadModuleOptions();

		this.onTypeChange('module');
	},

	/**
	 * Load the selectable member modules — every active module-type product
	 * except this one — into the chips picker's local store. If the entity's
	 * modules arrived (edit flow) before the options were ready, re-apply them
	 * once loaded so the chips render.
	 *
	 * @return {void}
	 */
	loadModuleOptions: function () {
		var me = this;
		go.Jmap.request({
			method: "MarketplaceServerProduct/query",
			params: {},
			callback: function (o, success, response) {
				if (!success || !response || !response.ids || !response.ids.length) {
					me._moduleOptionsLoaded = true;
					return;
				}
				go.Db.store("MarketplaceServerProduct").get(response.ids, function (entities) {
					var rows = [];
					(entities || []).forEach(function (p) {
						if (p.type === 'module' && p.moduleName && p.id !== me.currentId) {
							rows.push({id: p.moduleName, title: p.title || p.moduleName});
						}
					});
					me.modulesStore.loadData(rows);
					me._moduleOptionsLoaded = true;
					if (me._pendingModules) {
						me.modulesField.setValue(me._pendingModules);
						me._pendingModules = null;
					}
				}, me);
			},
			scope: me
		});
	},

	/**
	 * Toggle moduleName vs. the modules textarea depending on the selected type.
	 *
	 * @param {string} type
	 * @return {void}
	 */
	onTypeChange: function (type) {
		var isModule = type === 'module';
		this.moduleNameField.setVisible(isModule);
		// Only required for a module product; a hidden allowBlank:false field
		// would otherwise block submit for collections/subscriptions.
		this.moduleNameField.allowBlank = !isModule;
		if (!isModule) {
			this.moduleNameField.clearInvalid();
		}
		var isCollection = type === 'collection';
		this.modulesField.setVisible(isCollection);
		// A non-collection product carries no member modules — clear the picker
		// so switching away from 'collection' submits an empty `modules` array.
		if (!isCollection) {
			this.modulesField.reset();
		}
		// Releases only make sense for a saved module-type product.
		this.releasesPanel.setVisible(isModule && !!this.currentId);
	},

	onLoad: function (entityValues) {
		go.modules.community.marketplaceserver.ProductDialog.superclass.onLoad.call(this, entityValues);

		// The chips picker needs its options loaded before it can render the
		// selected members; if they aren't ready yet, loadModuleOptions applies
		// this once it finishes.
		var mods = entityValues.modules || [];
		if (this._moduleOptionsLoaded) {
			this.modulesField.setValue(mods);
		} else {
			this._pendingModules = mods;
		}
		this.onTypeChange(entityValues.type);

		// Load this product's releases into the read-only panel.
		if (entityValues.type === 'module' && this.currentId) {
			this.releasesStore.setFilter('product', {productId: this.currentId});
			this.releasesStore.load();
		}
	}
});
