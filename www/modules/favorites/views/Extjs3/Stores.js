go.Modules.register("legacy", "favorites");

GO.favorites.favoritesCalendarStore = new GO.data.JsonStore({
	url: GO.url('favorites/favorites/calendarStore'),		
	root: 'results',
	id: 'id',
	totalProperty:'total',
	fields: ['user_id','calendar_id','sort','id','name','comment','user_name','group_id','group_name','project_id','checked','tooltip'],
	remoteSort: true,
	model:"GO\\Favorites\\Model\\Calendar"
});

GO.favorites.favoritesTasklistStore = new GO.data.JsonStore({
	url: GO.url('favorites/favorites/tasklistStore'),		
	root: 'results',
	id: 'id',
	totalProperty:'total',
	fields: ['user_id', 'tasklist_id', 'sort', 'id', 'name', 'acl_id', 'files_folder_id','checked'],
	remoteSort: true,
	model:"GO\\Favorites\\Model\\Tasklist"
});

GO.favorites.favoritesAddressbookStore = new GO.data.JsonStore({
	url: GO.url('favorites/favorites/addressbookStore'),		
	root: 'results',
	id: 'id',
	totalProperty:'total',
	fields: ['user_id', 'id','name','files_folder_id','users','default_salutation','shared_acl','acl_id','checked'],
	remoteSort: true,
	model:"GO\\Favorites\\Model\\Addressbook"
});




