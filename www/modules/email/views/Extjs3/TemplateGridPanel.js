GO.email.TemplateGridPanel = Ext.extend(GO.grid.GridPanel,{
	
	store: new Ext.data.Store(),
	paging: true,
	
	initComponent : function(config){
		
		
		this.searchField = new GO.form.SearchField({
			store: this.store,
			width:250
		});
		
		Ext.apply(this,{
			noDelete: true,
			cls:'go-white-bg',
			tbar: [
				this.searchField
			],
			border: false,
//			paging:true,
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{ 
					header: 'name', 
					dataIndex: 'text' 
				},
				new GO.grid.RadioColumn({
					id:'checked',
					header: t("Default"),
					dataIndex: 'checked',
					width: 90,
					isDisabled:function(record){
						return record.get('checked');
					}
				})
				]
			})
		})
		
		GO.email.TemplateGridPanel.superclass.initComponent.call(this);		
	}
	
	
})
