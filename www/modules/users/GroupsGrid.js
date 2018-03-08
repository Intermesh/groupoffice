GO.users.GroupsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = t("User groups");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.cls = 'go-sidenav';
	if(!config.store)
		config.store = new GO.data.JsonStore({
			url: GO.url('users/group/store'),
			baseParams: {
				'permissionLevel' : GO.permissionLevels.read,
				limit:parseInt(GO.settings.config.nav_page_size)
			},
			root: 'results', 
			totalProperty: 'total', 
			id: 'id',
			fields: ['id','name','checked'],
			remoteSort: true
		});

	Ext.apply(config, {
		allowNoSelection:true,
		bbar: new GO.SmallPagingToolbar({
			items:[this.searchField = new GO.form.SearchField({
				store: config.store,
				width:120,
				emptyText: t("Search")
			})],
			store:config.store,
			pageSize:GO.settings.config.nav_page_size
		})
	});
	
	GO.users.GroupsGrid.superclass.constructor.call(this, config);
};


Ext.extend(GO.users.GroupsGrid, GO.grid.MultiSelectGrid, {});