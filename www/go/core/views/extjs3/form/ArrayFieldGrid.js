Ext.define('go.form.ArrayFieldGrid',{
	extend: go.grid.EditorGridPanel,

	/**
	 * The name of the field in the linking table that holds the id of the entities you want to select
	 * @property {string}
	 */
	idField: null,

	/**
	 * If set to true then the field will expect and return id's as value. Otherwise it will use full record data.
	 * @property {boolean}
	 */
	valueIsId: false,

	/**
	 * The entity property to display in the grids
	 * @property {string}
	 */
	displayField: "name",

	/**
	 * The entity store of the items
	 * @property {go.data.EntityStore}
	 */
	entityStore: null,

	/**
	 * Base params of store in select window
	 */
	storeBaseParams: null,

	autoHeight: true,

	constructor: function (config) {

		config = config || {};

		if(!config.viewConfig) {
			config.viewConfig = {
				scrollOffset: 0,
				emptyText: t("Empty"),
				deferEmptyText: false
			};
		}

		var actions = this.initRowActions();

		var fields = [config.idField,config.displayField];
		var columns = [
			{
				id: 'name',
				header: t('Name'),
				sortable: false,
				hideable: false,
				draggable: false,
				menuDisabled: true,
				dataIndex: config.idField,
				renderer: function (id) {
					//must be preloaded... but why?
					return me.entityStore.data[id][me.displayField];
				}
			}
		];

		if(config.extraColumns) {
			config.extraColumns.forEach(function(c) {
				columns.push(c);
			});
		}

		if(config.extraFields) {
			config.extraFields.forEach(function(c) {
				fields.push(c);
			});
		}

		columns.push(actions);

		var me = this;

		Ext.apply(config, {

			tbar: {
				items: [
					{xtype: "tbtitle", text: config.title},
					this.addCombo = new go.form.ComboBox({
						//iconCls: "ic-add",
						allowBlank: true,
						hiddenName: null,
						emptyText: t('Add'),
						triggerAction: 'all',
						displayField: config.displayField,
						editable: true,
						selectOnFocus: true,
						forceSelection: true,
						store: {
							xtype: 'gostore',
							fields: ['id', config.displayField],
							entityStore: config.entityStore,
							baseParams: config.storeBaseParams,
							listeners: {
								load: function() {
									var ids = this.getIds();
									this.addCombo.store.filterBy(function(r) {
										return ids.indexOf(r.id) === -1;
									});
								},
								scope: this
							}
						},
						listeners: {
							select: function(me, record) {
								// add row and set fields with record
								var r = {};
								r[this.idField] = record.id;
								this.store.loadData({records: [r]}, true);
								this._isDirty = true;
								//filter store?
							},
							scope:this
						}
					})
				]
			},
			store: new go.data.Store({
				fields: fields
			}),
			columns: columns,
			autoExpandColumn: "name"
		});

		config.plugins = config.plugins || [];
		config.plugins.push(actions);


//		delete config.title;
		this.callParent([config]);
	},

	isFormField: true,

	getName: function() {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	},

	setValue: function (records) {

		if(GO.util.empty(records)) {
			return;
		}

		this._isDirty = false; //todo this is not right but works for our use case
		var ids;
		if(this.valueIsId) {
			ids = records;

			records = [];
			ids.forEach(function (id) {
				var record = {};
				record[this.idField] = id;
				records.push(record);
			}, this);
		} else
		{
			ids = [];
			records.forEach(function (n) {
				ids.push(n[this.idField]);
			}, this);
		}

		//we must preload the notebooks so notebook select can use it in a renderer
		this.entityStore.get(ids, function () {
			this.store.loadData({records: records});
		}, this);
	},

	getValue: function () {
		var records = this.store.getRange(), v = [];
		for(var i = 0, l = records.length; i < l; i++) {
			if(this.valueIsId) {
				v.push(records[i].data[this.idField]);
			} else {
				v.push(records[i].data);
			}
		}
		return v;
	},

	markInvalid: function (msg) {
		this.getEl().addClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.mark(this, msg);
	},
	clearInvalid: function () {
		this.getEl().removeClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.clear(this);
	},

	validate : function() {
		return true;
	},

	getIds : function() {
		var records = this.store.getRange(), v = [];
		for(var i = 0, l = records.length; i < l; i++) {
			v.push(records[i].data[this.idField]);
		}
		return v;
	},

	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,

			actions: [{
				iconCls: 'ic-delete'
			}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				this.store.removeAt(row);
				this._isDirty = true;
			},
			scope: this
		});

		return actions;

	}
});
