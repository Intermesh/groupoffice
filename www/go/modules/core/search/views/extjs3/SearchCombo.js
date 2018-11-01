/* global GO, Ext, go */

go.modules.core.search.SearchCombo = Ext.extend(go.form.ComboBox, {	
	emptyText: t("Search..."),	
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: true,
	initComponent: function () {
		
		this.tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="x-combo-list-item"><i class="entity {entity}"></i> {name}</div>',
				'</tpl>'
		 );
		 
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'entityId', 'entity', 'name', 'description', {name: 'modifiedAt', type: 'date'}],
				entityStore: "Search",
				baseParams: {
					filter: {
							permissionLevel: GO.permissionLevels.write
					},
					limit: 20
				}
			})
		});
		
		go.modules.core.search.SearchCombo .superclass.initComponent.call(this);

	}
});

