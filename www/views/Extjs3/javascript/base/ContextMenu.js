//
//	EXAMPLE USAGE
//
//		var packageContextMnu = new GO.base.model.ContextMenu({
//			scope:this,
//			items:[
//				new Ext.menu.Item({
//					iconCls: 'btn-export',
//					text: GO.licenses.lang.downloadpackage,
//					cls: 'x-btn-text-icon',
//					scope:this,
//					disableOnMultiselect:true, // TAKE SPECIAL ATTENTION TO THIS PARAMETER!
//					handler: function()
//					{
//						var item = packageContextMnu.getSelectedItems();
//					}
//				}),
//				new Ext.menu.Item({
//					iconCls: 'btn-export',
//					text: GO.licenses.lang.downloadlicense,
//					cls: 'x-btn-text-icon',
//					scope:this,
//					disableOnMultiselect:true, // TAKE SPECIAL ATTENTION TO THIS PARAMETER!
//					handler: function()
//					{
//						var item = packageContextMnu.getSelectedItems();
//					}
//				})
//			]
//		});
//		
//		this.licensesPackagesGrid.grid.on("rowcontextmenu", function(grid, index, event){
//			 packageContextMnu.show(grid, index, event);
//		}, this);



GO.base.model.ContextMenu = function(config){

	if(!config)
	{
		config = {};
	}

	config.grid = false;
	config.multiselected = false;
	
	GO.base.model.ContextMenu.superclass.constructor.call(this,config);
}

Ext.extend(GO.base.model.ContextMenu, Ext.menu.Menu, {
	getSelectedRecords : function() {
		return this.grid.getSelectionModel().getSelections();
	},
	getSelectedKeys: function() {
		var keys = [];
		var records = this.grid.getSelectionModel().getSelections();
		for(var i =0;i<records.length;i++)
			keys.push(records[i].id);
		
		return keys;
	},
	show : function(grid,index,event) {
		event.stopEvent();
		
		this.grid = grid;
		this.multiselected = false;
		
		var selections = this.grid.getSelectionModel().getSelections();
		if(selections.length > 1)
			this.multiselected = true;			
		
		this.prepareMenu();
		
		this.showAt(event.xy);
	},
	prepareMenu : function(){
		this.items.each(function(item) {
			if(item.disableOnMultiselect){
				if(this.multiselected)
					item.setDisabled(true);
				else
					item.setDisabled(false);
			}
		},this)
	}
});