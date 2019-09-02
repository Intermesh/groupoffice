go.Modules.register("core", 'core', {
	title: t("Core"),
	entities: [
		{
			name: 'Group',
			relations: {
				users: {store: "User", fk: "users"},
				user: {store: "User", fk:'isUserGroupFor'}
			}
		},
		'User', 
		'Field', 
		{
			name: 'FieldSet', 
			title: t("Custom field set")
		}, 
		'Module', 
		{
			name: 'Link',
			relations: {
				to: {store: "Search", fk: "toSearchId"}
			}
		}, 
		'Search', 
		'EntityFilter',
		'SmtpAccount',
		'EmailTemplate'
	],

	userSettingsPanels: [
		"go.users.UserGroupGrid",
		"go.users.UserSettingsWorkingWeek"
	],
	selectDialogPanels: [
		"go.users.SelectDialogPanel",
	]
});


GO.mainLayout.on('render', function () {

	var container, searchField, searchContainer, panel;

	var enableSearch = function () {
		searchContainer.show();
		searchField.focus(true);

		searchField.panel.on("collapse", function() {
			searchContainer.hide();
		});

		searchField.on("blur", function() {
			if(searchField.panel.collapsed) {
				searchContainer.hide();
			}
		}, this);
		
		if(searchField.getValue()) {
			searchField.panel.expand();
		}
			
	};


	container = new Ext.Container({
		id: 'global-search-panel',
		items: [{
				xtype: 'button',
				iconCls: 'ic-search',
				tooltip: t("Search"),
				handler: function () {
					enableSearch();
				},
				scope: this
			},
			searchContainer = new Ext.Container({
				hidden: true,
				cls: 'search-field-wrap',
				items: [
					searchField = new go.search.SearchField({
						listeners: {
							select: function(field, record) {
								go.Entities.get(record.data.entity).goto(record.data.entityId);
							}
						}
					})
				]
			})
		],
		renderTo: "search_query"
	});




	//Global accessor to search with go.searchField.setValue("test");
	go.util.search = function (query) {
		enableSearch();
		searchField.setValue(query);
		searchField.search();
	};
});
