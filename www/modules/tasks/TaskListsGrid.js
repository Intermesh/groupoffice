GO.tasks.TaskListsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
   
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.ddGroup='TasklistsDD';
	config.enableDD=true;

	Ext.apply(config, {
		allowNoSelection:true,
		bbar: new GO.SmallPagingToolbar({
			store:config.store,			
			pageSize:GO.settings.config.nav_page_size
		})
	});
	
	GO.tasks.TaskListsGrid.superclass.constructor.call(this, config);

	this.getBottomToolbar().add('->',{
		xtype: 'tbsearch',
		store: this.calendarsStore
	})
};


Ext.extend(GO.tasks.TaskListsGrid, GO.grid.MultiSelectGrid, {
	
	afterRender : function()
	{
		GO.tasks.TaskListsGrid.superclass.afterRender.call(this);

		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody, {
			ddGroup : 'TasklistsDD',
			notifyDrop : this.onNotifyDrop.createDelegate(this)
		});
	},
	onNotifyDrop : function(source, e, data)
	{
		var selections = source.dragData.selections;
		var dropRowIndex = this.getView().findRowIndex(e.target);
		var tasklist_id = this.getView().grid.store.data.items[dropRowIndex].id;

		var move_items = [];
		for(var i=0; i<selections.length; i++)
		{
			move_items.push(selections[i].id);	    
		}
						
		if(tasklist_id > 0)
		{
			GO.request({
				url:'tasks/task/move',
				params:{
					//task:'move_tasks',
					tasklist_id:tasklist_id,
					items:Ext.encode(move_items)
				},
				success: function(options, response, result){
					if(result.reload_store)
						{
							this.fireEvent('drop');
						}
				},
				scope:this
			});
//			
//			
//			Ext.Ajax.request({
//				url: GO.settings.modules.tasks.url+'action.php',
//				params: {
//					task:'move_tasks',
//					tasklist_id:tasklist_id,
//					items:Ext.encode(move_items)
//				},
//				callback: function(options, success, response)
//				{
//					var data = Ext.decode(response.responseText);
//
//					if(!data.success)
//					{
//						GO.errorDialog.show(data.feedback)
//					}else
//					{
//						if(data.reload_store)
//						{
//							this.fireEvent('drop');
//						}
//					}
//				},
//				scope:this
//			});		
			
			return true;
		}else
		{
			return false;
		}
	}
	
});
