go.Modules.register("core", 'core', {
	title: t("Core"),
	entities: [
		{
			name: 'Alert',
			relations: {
				user: {store: "Principal", fk:'userId'}
			}
		},
		{
			name: 'Group',
			relations: {
				users: {store: "Principal", fk: "users"},
				user: {store: "Principal", fk:'isUserGroupFor'}
			}
		},

		{
			name: 'User',
			filters: [
				{
					wildcards: false,
					name: 'text',
					type: "string",
					multiple: false,
					title: t("Query")
				},
				{
					title: t("Comment"),
					name: 'comment',
					multiple: true,
					type: 'string'
				},
				{
					title: t("Commented at"),
					name: 'commentedat',
					multiple: false,
					type: 'date'
				}, {
					title: t("Modified at"),
					name: 'modifiedat',
					multiple: false,
					type: 'date'
				}, {
					title: t("Modified by"),
					name: 'modifiedBy',
					multiple: true,
					type: 'string'
				}, {
					title: t("Created at"),
					name: 'createdat',
					multiple: false,
					type: 'date'
				}, {
					title: t("Created by"),
					name: 'createdby',
					multiple: true,
					type: 'string'
				},
				{
					title: t("Username"),
					name: 'username',
					multiple: true,
					type: 'string'
				},{
					title: t("Display name"),
					name: 'displayName',
					multiple: true,
					type: 'string'
				},{
					title: t("E-mail"),
					name: 'email',
					multiple: true,
					type: 'string'
				},
			]

		},
		'Principal',
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
		'PdfTemplate',
		'ImportMapping',
		'CronJobSchedule',
		{
			name: 'AuthAllowGroup',
			relations: {
				group: {store: "Group", fk:'groupId'}
			}
		},
		'OauthClient',
		'SpreadSheetExport'
	],

	userSettingsPanels: [
		"go.users.UserGroupGrid"
	],
	selectDialogPanels: [
		"go.users.SelectDialogPanel"
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
		"go.customfields.type.Attachments",
		"go.customfields.type.Notes",
		"go.customfields.type.Number",
		"go.customfields.type.Select",
		"go.customfields.type.Text",
		"go.customfields.type.TextArea",
		"go.customfields.type.Data",
		"go.customfields.type.User",
		"go.customfields.type.YesNo",
		"go.customfields.type.TemplateField"
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
		if(!e.dataTransfer || !e.dataTransfer.items.length || e.dataTransfer.items[0].kind != 'file') {
			return;
		}
		e.dataTransfer.dropEffect = "none";
		e.preventDefault();
	},false);
	document.addEventListener("drop",function(e){
		if(!e.dataTransfer || !e.dataTransfer.items.length || e.dataTransfer.items[0].kind != 'file') {
			return;
		}
		e.preventDefault();
	},false);


	document.addEventListener("keydown", ev => {

		if((ev.metaKey || ev.ctrlKey) && ev.key == "p") {

			ev.preventDefault();

			const modPanel = GO.mainLayout.tabPanel.getActiveTab();

			if(!modPanel) {
				return;
			}
			if(modPanel.print) {
				modPanel.print();

			} else {
				const pnl = modPanel.findByType("displaypanel");
				if(pnl.length) {
					pnl[0].print();
				}
			}


		}
	})


	// if(!GO.settings.config.debug) {
	// window.onerror = function (message, source, lineno, colno, error) {
	// 	go.Jmap.request({
	// 		method: "core/System/logClientError",
	// 		params: {
	// 			message: message + (error && error.stack ? " - " + error.stack : "")
	// 		}
	// 	}).catch(e=>console.error(e))
	// }
	// // }
	//
	// window.addEventListener('unhandledrejection', function (event) {
	//
	// 	if(Ext.isObject(event.reason)) {
	// 		var txt = event.reason.message || event.reason.detail || event.reason.description || t("Unknown error");
	// 	} else if(Ext.isString(event.reason)) {
	// 		var txt = event.reason;
	// 	} else
	// 	{
	// 		var txt = t("Unknown error");
	// 	}
	//
	// 	go.Jmap.request({
	// 		method: "core/System/logClientError",
	// 		params: {
	// 			message: txt + " - " + ((new Error()).stack)
	// 		}
	// 	}).catch(e=>console.error(e))
	// });

});
