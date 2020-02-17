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
		'EmailTemplate',
		{
			name: 'AuthAllowGroup',
			relations: {
				group: {store: "Group", fk:'groupId'}
			}
		}
	],

	userSettingsPanels: [
		"go.users.UserGroupGrid",
		"go.users.UserSettingsWorkingWeek"
	],
	selectDialogPanels: [
		"go.users.SelectDialogPanel",
	],

	customFieldTypes: [
		"go.customfields.type.Checkbox",
		"go.customfields.type.Date",
		"go.customfields.type.DateTime",
		"go.customfields.type.EncryptedText",
		"go.customfields.type.FunctionField",
		"go.customfields.type.Group",
		"go.customfields.type.Html",
		"go.customfields.type.MultiSelect",
		"go.customfields.type.Notes",
		"go.customfields.type.Number",
		"go.customfields.type.Select",
		"go.customfields.type.Text",
		"go.customfields.type.TextArea",
		"go.customfields.type.User",
		"go.customfields.type.YesNo"
	]
});


GO.mainLayout.on('render', function () {

	var container, searchField, searchContainer, panel, searchButton;

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
		items: [searchButton = new Ext.Button({
				xtype: 'button',
				iconCls: 'ic-search',
				tooltip: t("Search") + " (" + (Ext.isMac ? '⌘ + ⇧' : 'CTRL + SHIFT') + ' + F)',
				
				handler: function () {
					enableSearch();
				},
				scope: this
			})		
		],
		renderTo: "search_query"
	});

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
		],
		renderTo: Ext.getBody()
	});


	new Ext.KeyMap(document, {
		stopEvent:true,
		key:Ext.EventObject.F,
		ctrl:true,
		shift: true,
		fn:function(){
				searchButton.handler();
		}
	});


	//Global accessor to search with go.searchField.setValue("test");
	go.util.search = function (query) {
		enableSearch();
		searchField.setValue(query);
		searchField.search();
	};

	//Prevent browser nav on file drop.
	document.addEventListener("dragover",function(e){
		e.preventDefault();
	},false);
	document.addEventListener("drop",function(e){
		e.preventDefault();
	},false);


	window.onerror = function(message, source, lineno, colno, error) {
		Ext.MessageBox.alert(t("Error"), t("An error occurred. More details can be found in the console.") + "<br /><br />" + message);
	}

	window.addEventListener('unhandledrejection', function(event) {
		Ext.MessageBox.alert(t("Error"), t("An error occurred. More details can be found in the console."));
	});
});
