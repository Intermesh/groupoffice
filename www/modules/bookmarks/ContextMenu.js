GO.bookmarks.BookmarkContextMenu = function(config)
{
	if(!config)
	{
		config = {};
	}
	config['shadow']='frame';
	config['minWidth']=180;
	

				
	this.deleteButton = new Ext.menu.Item({
		iconCls: 'btn-delete',
		text: t("Delete"),
		cls: 'x-btn-text-icon',
		handler: function(){
			GO.bookmarks.removeBookmark(this.record);						
		},
		scope:this
	});
	
	this.editButton = new Ext.menu.Item({
		iconCls: 'btn-edit',
		text: t("Edit"),
		cls: 'x-btn-text-icon',
		handler: function(){

			GO.bookmarks.showBookmarksDialog({
				record:this.record,
				edit:1
			})
					
		},
		scope:this
	});
				

				
	config.items=[this.deleteButton,
	this.editButton];
	


	GO.bookmarks.BookmarkContextMenu.superclass.constructor.call(this, config);	
}

Ext.extend(GO.bookmarks.BookmarkContextMenu, Ext.menu.Menu,{

	setRecord : function (record){
		this.record = record;
		
		this.editButton.setDisabled(record.data.permissionLevel<GO.permissionLevels.write);
		this.deleteButton.setDisabled(record.data.permissionLevel<GO.permissionLevels.writeAndDelete);
	}
	
			
});
