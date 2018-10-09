go.links.EntityGrid = Ext.extend(go.grid.GridPanel, {
	viewConfig: {
		forceFit: true,
		autoFill: true
	},
	cls: 'go-grid3-hide-headers',

	constructor: function (config) {
	
		var selModel = new Ext.grid.CheckboxSelectionModel();
	
		var data = [], allEntities = go.Entities.getAll();
		
		for(entity in allEntities) {
			if(allEntities[entity].linkable && go.Modules.isAvailable(allEntities[entity].package, allEntities[entity].module)) {
				data.push([allEntities[entity].name, allEntities[entity].title]);
			}
		};
		

		Ext.apply(config, {			
			tbar: [{xtype: "selectallcheckbox"}],			
			store: new Ext.data.ArrayStore({
				fields: ['entity', 'name'],
				data: data
			}),
			selModel: selModel,
			columns: [
				selModel,
				{
					id: 'name',
					header: t('Name'),
					sortable: false,
					dataIndex: 'name',
					hideable: false,
					draggable: false,
					menuDisabled: true,
					renderer: function(v, meta, record) {
						return '<i class="label entity '+record.data.entity+'"></i> ' + v;
					}
				}
			]
		});
		
		config.store.sort('name', 'ASC');

		go.links.EntityGrid.superclass.constructor.call(this, config);
	}
});
