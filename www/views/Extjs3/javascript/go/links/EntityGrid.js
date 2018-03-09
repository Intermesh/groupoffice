go.links.EntityGrid = Ext.extend(go.grid.GridPanel, {
	viewConfig: {
		forceFit: true,
		autoFill: true
	},
	cls: 'go-grid3-hide-headers',

	constructor: function (config) {
	
		var selModel = new Ext.grid.CheckboxSelectionModel();
	
		var data = [];
		
		for(entity in go.entities) {
			if(go.ModuleManager.isAvailable(go.entities[entity].module)) {
				data.push([entity, t(entity, go.entities[entity].module)]);
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
					menuDisabled: true
				}
			]
		});
		
		config.store.sort('name', 'ASC');

		go.links.EntityGrid.superclass.constructor.call(this, config);
	}
});
