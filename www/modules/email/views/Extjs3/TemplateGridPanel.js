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
			view: new Ext.grid.GroupingView({
				showGroupName: false,
				enableNoGroups:false, // REQUIRED!
				hideGroupedColumn: true,
				emptyText: t("No items to display"),
				autoFill: true,
				forceFit: true
			}),
//			paging:true,
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{ 
					header: 'name', 
					dataIndex: 'text' 
				},{
					header: t('Group'),
					dataIndex: 'group_id',
					renderer: (v,meta,rec) => rec.data.group_name
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
