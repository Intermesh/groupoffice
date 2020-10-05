go.filter.VariableFilterPanel = Ext.extend(Ext.Panel, {
	filterStore: null,
	entity: null,
	entityStore: "EntityFilter",
	initComponent: function() {


		this.fieldset = new Ext.form.FieldSet({
			labelAlign: 'top'
		});

		this.items = [this.fieldset];

		this.supr().initComponent.call(this);

		this.on("render", function() {
			this.loadFilters();
		}, this);

	},

	onChanges: function(entityStore, added, changed, destroyed) {
		this.fieldset.removeAll(true);
		this.loadFilters();
	},

	loadFilters : function() {
		var entityStore = go.Db.store("EntityFilter"), me = this;

		entityStore.query({
			filter: {
				entity: this.entity,
				type: "variable"
			}
		}).then(function(response) {
			return entityStore.get(response.ids);
		}).then(function(result) {
			result.entities.forEach(function(f) {
				var filterConfig = go.Entities.get(me.entity).filters[f.name];
				if(!filterConfig) {
					console.warn('No such filter: ' + f.name);
					return;
				}
				var cfg = me.getFilterCmp(filterConfig);
				if(!cfg) {
					return;
				}
				var cmp = Ext.create(cfg);
				cmp.serverId = f.id;

				var chipView = new go.form.ChipsView();
				chipView.filter = f;
				chipView.store.on('add', me.load, me);
				chipView.store.on('remove', me.load, me);
				var event = cmp.events.select ? 'select' : 'change';
				cmp.on(event, function(cmp) {
					var v = cmp.getValue();

					if(v instanceof Date) {
						v = v.serialize();
					}
					chipView.store.loadData({records: [{
							value: v,
							display: cmp.getRawValue()
						}]}, true);

					cmp.reset();


				});

				cmp.on('specialkey' , function(field, e) {
					// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
					// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
					if (e.getKey() == e.ENTER) {
						chipView.store.loadData({records: [{
								value: cmp.getValue(),
								display: cmp.getRawValue()
							}]}, true);

						cmp.reset();
					}
				})

				cmp.fieldLabel = filterConfig.title;
				me.fieldset.add(me.getFilterCmpWrap(cmp));
				me.fieldset.add(chipView);

			});

			me.doLayout();
		});

	},

	getFilterCmpWrap : function(cmp) {
		cmp.flex = 1;
		var wrap = {
			anchor: "100%",
			xtype: "compositefield",
			items: [
				cmp,
				{
					width: dp(24),
					xtype: "button",
					iconCls: 'ic-more-vert',
					menu: [{
						itemId: "edit",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function() {
							var dlg = new go.filter.VariableFilterDialog({
								entity: this.entity
							});
							dlg.load(cmp.serverId).show();
						},
						scope: this
					},{
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function() {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn != "yes") {
									return;
								}
								go.Db.store("EntityFilter").set({destroy: [cmp.serverId]});
							}, this);
						},
						scope: this
					}]

				}
			]
		}
		return wrap;
	},

	load: function() {
		var filter = {
			operator: 'AND',
			conditions: []
		};
		this.findByType('chipsview').forEach(function(cv) {
			if(cv.store.getCount() == 0) {
				return;
			}

			var conditions = [];

			cv.store.getRange().forEach(function(r) {
				var c = {};
				c[cv.filter.name] = r.data.value;
				conditions.push(c)
			});

			filter.conditions.push({
				operator: 'OR',
				conditions: conditions
			})

		});

		this.filterStore.setFilter('customfilters', filter);
		this.filterStore.load();
	},



	getFilterCmp : function(filter) {

		var cls;

		try {
			cls = go.filter.variabletypes[filter.type] || eval(filter.type);
		}catch(e) {
			console.error(e);
			return false;
		}

		if(!filter.typeConfig) {
			filter.typeConfig = {};
		}

		Ext.apply(filter.typeConfig, {
			anchor: '100%',
			filter: filter,
			name: filter.name,
			collapseOnSelect: false,
			hiddenName: filter.name,
			customfield: filter.customfield //Might be null if this is a standard filter.
		});

		return new cls(filter.typeConfig);
	}


});

Ext.reg('variablefilterpanel', go.filter.VariableFilterPanel);