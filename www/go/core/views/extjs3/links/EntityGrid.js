go.links.EntityGrid = Ext.extend(go.grid.GridPanel, {
	
	/**
	 * Provide the entities to show in the list here
	 * When not provided, the list will show all entities
	 */
	entities: null,
	
	viewConfig: {
		forceFit: true,
		autoFill: true
	},
	hideHeaders: true,
	
	savedSelection: false,

	constructor: function (config) {

		config = config || {};
		
		if(config.entities){
			this.entities = config.entities;
		}
		var selModel = new Ext.grid.RowSelectionModel({simpleSelect: true});

		var data = [], allEntities = this.entities ? this.entities : go.Entities.getLinkConfigs(), id;
		
		allEntities.forEach(function(link){			
			id = link.entity;
			if(link.filter) {
				id += "-" + link.filter;
			} else
			{
				link.filter = null;
			}
			data.push([id, link.entity, link.title, link.filter, link.iconCls]);
		});

		Ext.apply(config, {
			tbar: [{xtype: "selectallcheckbox"}],
			store: new Ext.data.ArrayStore({
				fields: ['id', 'entity', 'name', 'filter', 'iconCls'],
				data: data,
				idIndex: 0
			}),
			selModel: selModel,
			columns: [
				{
					id: 'name',
					header: t('Name'),
					sortable: false,
					dataIndex: 'name',
					hideable: false,
					draggable: false,
					menuDisabled: true,
					renderer: function (v, meta, record) {
						return '<i class="label ' + record.data.iconCls + '"></i> ' + v;
					}
				}
			]
		});

		config.store.sort('name', 'ASC');

		go.links.EntityGrid.superclass.constructor.call(this, config);
		
		if (this.savedSelection) {
			this.getSelectionModel().on('selectionchange', function (sm) {
				this.saveSelection(this.savedSelection);
			}, this, {buffer: 1}); //add buffer because it clears selection first	
			
			this.on("viewready", function() {
				this.loadSelection(this.savedSelection);
			}, this);
		}
	},

	saveSelection: function (name) {
		var selected = this.getSelectionModel().getSelections();
		Ext.state.Manager.set("entity-grid-selected-" + name, selected.map(function (r) {
			return r.id;
		}));
	},

	loadSelection: function (name) {
		var ids = Ext.state.Manager.get("entity-grid-selected-" + name);
		if (!ids) {
			return;
		}

		var me = this, records = [], record;
		ids.forEach(function (id) {
			record = me.store.getById(id);
			if (record) {
				records.push(record);
			}
		});
		
		this.getSelectionModel().suspendEvents(false);
		this.getSelectionModel().selectRecords(records, true);
		this.getSelectionModel().resumeEvents();

	}
});
