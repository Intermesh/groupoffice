GO.notes.writableCategoriesStore = new GO.data.JsonStore({
	url: GO.url('notes/category/store'),
	baseParams: {
		permissionLevel:GO.permissionLevels.write
	},	
	fields: ['id', 'name', 'user_name']	
});

GO.notes.writableAdminCategoriesStore = new GO.data.JsonStore({
	url: GO.url('notes/category/store'),
	baseParams: {
		permissionLevel:GO.permissionLevels.write
	},	
	fields: ['id', 'name', 'user_name']
});


GO.notes.readableCategoriesStore = new GO.data.JsonStore({
	url: GO.url('notes/category/store'),
	baseParams: {
		limit:GO.settings.config.nav_page_size
	},
	fields: ['id','user_name','acl_id','name','checked']
});
