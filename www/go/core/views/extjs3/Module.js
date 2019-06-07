go.Modules.register("core", 'core', {
	title: t("Core"),
	entities: [
		{
			name: 'Group',
			relations: {
				users: {store: "User", fk: "users.userId"},
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
	systemSettingsPanels: [
		"go.users.SystemSettingsUserGrid",
		"go.groups.SystemSettingsGroupGrid",
		"go.modules.SystemSettingsModuleGrid",		
		"go.tools.SystemSettingsTools",
		"go.cron.SystemSettingsCronGrid"
	],
	userSettingsPanels: [
		"go.users.UserGroupGrid",
		"go.users.UserSettingsWorkingWeek"
	]
});


GO.mainLayout.on('render', function () {

	var container, searchField, searchContainer, panel;

	var search = function () {
		searchField.clearInvalid();
		panel.setWidth(searchField.getWidth());
		panel.setHeight(dp(500));
		panel.getEl().alignTo(searchField.getEl(), "tl-bl");
		panel.search(searchField.getValue());
	}, enableSearch = function () {
		searchContainer.show();
		searchField.focus(true);

		if(searchField.getValue()) {
			panel.expand();
		}

		if (!panel) {
			panel = new go.search.Panel({
				searchContainer: searchContainer,
				listeners: {
					searchexception: function() {
						searchField.markInvalid(t("Invalid search query"));
					}
				}
			});
			panel.render(Ext.getBody());
			panel.on("collapse", function () {
				//searchField.setValue("");
				searchContainer.hide();
			});
		}
	};

	var dqTask = new Ext.util.DelayedTask(search);

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
					searchField = new Ext.form.TriggerField({
						emptyText: t("Search"),
						hideLabel: true,
						anchor: "100%",
						validationEvent: false,
						validateOnBlur: false,
						//trigger1Class: 'x-form-search-trigger',
						triggerClass: 'x-form-clear-trigger',
						enableKeyEvents: true,

						onTriggerClick: function () {
							this.setValue("");
							search();
						},
						listeners: {
							specialkey: function (field, e) {
								switch (e.getKey()) {
									case e.ESC:
										panel.collapse();

									case e.DOWN:
										if (panel.isVisible()) {
											panel.grid.getSelectionModel().selectRow(0);
											panel.grid.getView().focusRow(0);
										}
										break;
								}
							}
						}
					})

				]})
		],
		renderTo: "search_query"
	});


	searchField.getEl().on("input", function () {
		dqTask.delay(500);
	});


	//Global accessor to search with go.searchField.setValue("test");
	go.util.search = function (query) {
		enableSearch();
		searchField.setValue(query);
		search();
	};
});
