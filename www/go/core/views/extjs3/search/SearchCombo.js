/* global GO, Ext, go */

go.search.SearchCombo = Ext.extend(go.form.ComboBox, {	
	emptyText: t("Search..."),	
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: true,
	spellCheck: false,
	initComponent: function () {
		
		this.tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="x-combo-list-item"><i class="entity {iconCls}"></i> {name}</div>',
				'</tpl>'
		 );
		 
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: [
						'id', 
						'entityId', 
						'entity', 
						'name', 
						'description', 
						{
							name: 'modifiedAt', 
							type: 'date'
						},
						 
						{
							name:"iconCls", 
							convert: function(v, data){
								return go.Entities.getLinkIcon(data.entity, data.filter);
							}
						}],
				entityStore: "Search",
				baseParams: {
					filter: {
							permissionLevel: go.permissionLevels.write
					},
					limit: 20
				}
			})
		});
		
		go.search.SearchCombo .superclass.initComponent.call(this);

	}
});

