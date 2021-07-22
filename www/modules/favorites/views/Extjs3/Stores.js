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




