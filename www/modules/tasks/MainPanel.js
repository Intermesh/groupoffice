GO.tasks.MainPanel = function(config){
	
	if(!config)
	{
		config = {};
	}
		
	GO.tasks.taskListsStore = this.taskListsStore = new GO.data.JsonStore({
		url: GO.url('tasks/tasklist/store'),
		baseParams: {
			limit:GO.settings.config.nav_page_size
		},
		root: 'results',
		totalProperty: 'total',
		id: 'id',
		fields:['id','name','checked']		
	});

	this.gridPanel = new GO.tasks.TasksPanel( {		
		id:'ta-tasks-grid',
		loadMask:true,
		region:'center'
	});
	
	this.taskListsPanel = new GO.tasks.TaskListsGrid({
		id:'ta-taskslists',
		region:'center',
		loadMask:true,
		store: this.taskListsStore,
		title: t("Tasklists", "tasks"),	
		relatedStore: this.gridPanel.store,
		autoLoadRelatedStore:false,
		split:true
	});

	this.taskListsPanel.on('drop', function(type)
	{
	    this.taskListsPanel.store.reload();
			this.gridPanel.store.reload();
	}, this);


this.gridPanel.store.on('load', function(store, records, options)
	{    
		var lists = store.reader.jsonData.selectable_tasklists;
				
		if(lists && lists.length){
			this.addTaskPanel.populateComboBox(lists);
			this.tasklist_id = lists[0].data.id;
			this.tasklist_name = lists[0].data.name;
		}

	}, this);
	
	var filterPanel = new go.NavMenu({
		region:'north',
		store: new Ext.data.ArrayStore({
			fields: ['name', 'icon', 'inputValue'],
			data: [
				[t("Active", "tasks"), 'content_paste', 'active'],
				[t("Due in seven days", "tasks"), 'filter_7', 'sevendays'],
				[t("Overdue", "tasks"), 'schedule', 'overdue'],
				[t("Incomplete tasks", "tasks"), 'assignment_late', 'incomplete'],
				[t("Completed", "tasks"), 'assignment_turned_in', 'completed'],
				[t("Future tasks", "tasks"), 'assignment_return', 'future'],
				[t("All", "tasks"), 'assignment', 'all'],
			]
		}),
		listeners: {
			selectionchange: function(view, nodes) {	
				var record = view.store.getAt(nodes[0].viewIndex);
				this.gridPanel.store.baseParams['show']=record.data.inputValue;
				this.gridPanel.store.load();
			},
			scope: this
		}
	});
      
	this.categoriesPanel= new GO.grid.MultiSelectGrid({
		id:'ta-categories-grid',
		title:t("Categories", "tasks"),
		region:'south',
		loadMask:true,
		height:150,
		allowNoSelection:true,
		store:GO.tasks.categoriesStore,
		split:true
	});
	this.categoriesPanel.on('change', function(grid, categories, records)
	{
		this.gridPanel.store.baseParams.categories = Ext.encode(categories);
		this.gridPanel.store.reload();
                
		//delete this.gridPanel.store.baseParams.categories;
	}, this);
	
	this.gridPanel.on("delayedrowselect",function(grid, rowIndex, r){
		this.taskPanel.load(r.data.id);
	}, this);

	this.gridPanel.on('rowdblclick', function(grid, rowIndex){
		this.taskPanel.editHandler();
	}, this);

	this.gridPanel.on('checked', function(grid, task_id){
		if(this.taskPanel.data && this.taskPanel.data.id==task_id)
			this.taskPanel.reload();
			
	}, this);
			
	this.taskPanel = this.taskDetail = new GO.tasks.TaskPanel({
		region:'east',
		width:dp(504)
	});

	this.accordionPanel = new Ext.Panel({
		region:'center',
		autoScroll:false,
		resizable:true,
		layoutConfig:{hideCollapseTool:true},
		layout:'accordion',
		items:[
			this.taskListsPanel,
			this.categoriesPanel
		]
	});

	config.layout='border';
	config.items=[
		new Ext.Panel({
			region:'west',
			autoScroll:false,
			closeOnTab: true,
			width: dp(240),
			resizable:true,
			cls: 'go-sidenav',
			layout:'border',
			split: true,
			id: 'ta-west-panel',
			items:[
				filterPanel,
				this.accordionPanel
			]
		}),
		{
			region:'center',
			layout:'fit',
			tbar: {  // configured using the anchor layout
				xtype : 'container',
				items :[new Ext.Toolbar({
					items: [{
						grid: this.gridPanel,
						xtype:'addbutton',
						handler: function(b){
							this.taskPanel.reset();
							GO.tasks.showTaskDialog({
								tasklist_id: b.buttonParams.id,
								tasklist_name: b.buttonParams.name
							});
						},
						scope: this
					},
					{
						grid: this.gridPanel,
						xtype:'deletebutton',
						handler: function(b){
							this.gridPanel.deleteSelected({
								callback : this.taskPanel.gridDeleteCallback,
								scope: this.taskPanel
							});
						},
						scope: this
					},{
						iconCls: 'ic-settings',
						tooltip: t("Administration"),
						handler: function(){
							this.showAdminDialog();
						},
						scope: this
					},{
						iconCls: 'ic-refresh',
						tooltip: t("Refresh"),
						handler: function(){
							this.taskListsStore.load();
							this.gridPanel.store.load();
						},
						scope: this
					},
					this.exportMenu = new GO.base.ExportMenu({className:'GO\\Tasks\\Export\\CurrentGrid'}),
					'->',{
						xtype: 'tbsearch',
						store: this.gridPanel.store,
						onSearch: function(v) {
							console.log(this);
							this.store.baseParams['query']=v;
							this.store.load();
						}
					}]
				}),
				this.addTaskPanel = new GO.tasks.AddTaskBar({
					layout:'hbox',
					layoutConfig: {
						align: 'middle',
						defaultMargins: {left: dp(4), right: dp(4),bottom:0,top:0}
					}
				})
			]},
			items:[this.gridPanel]
		},
		this.taskPanel
	];
	
	

	this.exportMenu.setColumnModel(this.gridPanel.getColumnModel());
	
	GO.tasks.MainPanel.superclass.constructor.call(this, config);
	
}
 
Ext.extend(GO.tasks.MainPanel, Ext.Panel,{

	tasklist_ids: [],
	afterRender : function()
	{
		GO.tasks.MainPanel.superclass.afterRender.call(this);


		GO.tasks.tasksObservable.on('save',function(tasksObservable, task_id, loadedStore){
			if(this.gridPanel.store!=loadedStore)
				this.gridPanel.store.reload();
		}, this);
                
		this.taskListsStore.on('load', function(){
			var records = [];
			for(var i=0; i<this.taskListsStore.data.length; i++)
			{
				var item = this.taskListsStore.data.items[i];
				if(item.data.checked)
				{
					records.push(item);
				}
			}

			this.addTaskPanel.populateComboBox(records);

			if(records.length)
			{
				this.tasklist_id = records[0].data.id;
				this.tasklist_name = records[0].data.name;
			}
                                             
		},this);

		var requests = {
			tasklists:{r:"tasks/tasklist/store", limit: GO.settings.config.nav_page_size},				
			categories:{r:"tasks/category/store"}
		}

		if (!this.gridPanel.storeLoaded) {
			var groupState = this.gridPanel.store.multiSortInfo.sorters[0];//this.gridPanel.store.getSortState();
			var sortState = this.gridPanel.store.getSortState();
			requests['tasks'] = {
														r:"tasks/task/store",
														groupBy: groupState.field,
														groupDir: groupState.dir,
														sort: sortState.field,
														dir: sortState.direction														
													};
		}

		GO.request({
			maskEl:this.getEl(),
			url: "core/multiRequest",
			params:{
				requests:Ext.encode(requests)
			},
			success: function(options, response, result)
			{
				GO.tasks.categoriesStore.loadData(result.categories);
				this.taskListsStore.loadData(result.tasklists);
				if (!GO.util.empty(result.tasks)){
					if(result.tasks.success) {
							this.gridPanel.store.loadData(result.tasks);
					} else {
							Ext.Msg.alert(result.tasks.feedback);
					}
				}
			},
			scope:this
		});               
		
		GO.mainLayout.on('linksDeleted', function(deleteConfig, link_types){
			GO.mainLayout.onLinksDeletedHandler(link_types["GO\\Tasks\\Model\\Task"], this, this.gridPanel.store);
		}, this);    
	},
  
	showAdminDialog : function() {
		
		if(!this.adminDialog)
		{
			this.tasklistDialog = new GO.tasks.TasklistDialog();
			this.categoryDialog = new GO.tasks.CategoryDialog();
			
			this.tasklistDialog.on('save', function(){
				GO.tasks.writableTasklistsStore.load();
				this.taskListsStore.load();
			}, this);

			this.categoryDialog.on('save', function(){
				GO.tasks.categoriesStore.load();
			},this);
			
			this.tasklistsGrid = new GO.grid.GridPanel( {
				paging:true,
				border:false,
				title: t("Tasklists", "tasks"),
				store: GO.tasks.writableTasklistsStore,
				deleteConfig: {
					callback:function(){
						this.taskListsStore.load();
					},
					scope:this
				},
				columns:[{
					header:t("ID", "tasks"),
					dataIndex: 'id',
					sortable:true,
					hidden:true,
					width:20
				},{
					header:t("Name"),
					dataIndex: 'name',
					sortable:true
				},{
					header:t("Owner"),
					dataIndex: 'user_name'
				}],
				view:new  Ext.grid.GridView({
					autoFill:true
				}),
				sm: new Ext.grid.RowSelectionModel(),
				loadMask: true,
				tbar: [{
					iconCls: 'btn-add',
					text: t("Add"),
					cls: 'x-btn-text-icon',
					handler: function(){						
						this.tasklistDialog.show();
					},
					disabled: !GO.settings.modules.tasks.write_permission,
					scope: this
				},{
					iconCls: 'btn-delete',
					text: t("Delete"),
					cls: 'x-btn-text-icon',
					disabled: !GO.settings.modules.tasks.write_permission,
					handler: function(){
						this.tasklistsGrid.deleteSelected();
					},
					scope:this
				},'-',new GO.form.SearchField({
					store: GO.tasks.writableTasklistsStore,
					width:150
				})]
			});

			this.deleteCategoryButton = new Ext.Button({
				iconCls: 'btn-delete',
				text: t("Delete"),
				cls: 'x-btn-text-icon',
				//disabled: !GO.settings.modules.tasks.write_permission,
				handler: function(){
					this.categoriesGrid.deleteSelected();
				},
				scope:this
			});


			this.categoriesGrid = new GO.grid.GridPanel( {
				paging:true,
				border:false,
				title: t("Categories", "tasks"),
				store: GO.tasks.categoriesStore,
				deleteConfig: {
					callback:function(){
						GO.tasks.categoriesStore.load();
					},
					scope:this
				},
				columns:[{
					header:t("Name"),
					dataIndex: 'name',
					sortable:true
				},{
					header:t("Owner"),
					dataIndex: 'user_name'
				}],
				view:new  Ext.grid.GridView({
					autoFill:true
				}),
				sm: new Ext.grid.RowSelectionModel(),
				loadMask: true,
				tbar: [{
					iconCls: 'btn-add',
					text: t("Add"),
					cls: 'x-btn-text-icon',
					handler: function(){
						this.categoryDialog.show();
					},
					//disabled: !GO.settings.modules.tasks.write_permission,
					scope: this
				},
				this.deleteCategoryButton
			]
			});
			
			this.categoriesGridSelectionModel = this.categoriesGrid.getSelectionModel();
			this.categoriesGridSelectionModel.on("selectionchange", function(selModel){
				var disabled = false;
				if(!GO.settings.modules.tasks.write_permission){
					var selectedRows = selModel.getSelections();
					for (var i = 0; i < selectedRows.length; i++) {
						if(selectedRows[i].data.user_id != GO.settings.user_id && disabled !=true)
							disabled = true;
					}
				}
				this.deleteCategoryButton.setDisabled(disabled);
			}, this);

			this.tasklistsGrid.on("rowdblclick", function(grid, rowClicked, e){

				this.tasklistDialog.show(grid.selModel.selections.keys[0]);
			}, this);

			this.categoriesGrid.on('rowdblclick', function(grid, rowIndex)
			{
				var record = grid.getStore().getAt(rowIndex);

				if(GO.settings.has_admin_permission || (record.data.user_id > 0))
				{
					this.categoryDialog.show(record);
				}				

			}, this);

			this.tabPanel = new Ext.TabPanel({
				activeTab:0,
				border:false,
				items:[this.tasklistsGrid,this.categoriesGrid]
			})

			this.adminDialog = new Ext.Window({
				title: t("Settings"),
				layout:'fit',
				modal:false,
				minWidth:dp(440),
				minHeight:dp(616),
				height:dp(616),
				width:dp(784),
				closeAction:'hide',				
				items: this.tabPanel
			});
			
		}
		
		if(!GO.tasks.writableTasklistsStore.loaded){
			GO.tasks.writableTasklistsStore.load();
		}

		if(!GO.tasks.categoriesStore.loaded){
			GO.tasks.categoriesStore.load();
		}
	
		this.adminDialog.show();
	}
	
});


GO.tasks.showTaskDialog = function(config){

	//if(!GO.tasks.taskDialog)
		GO.tasks.taskDialog = new GO.tasks.TaskDialog();

	GO.tasks.taskDialog.show(config);
}



GO.tasks.writableTasklistsStore = new GO.data.JsonStore({
	url: GO.url('tasks/tasklist/store'),
	baseParams: {
		'task': 'tasklists',
		'auth_type':'write'
	},
	root: 'results',
	totalProperty: 'total',
	id: 'id',
	fields:['id','name','user_name'],
	remoteSort:true,
	sortInfo: {
		field: 'name',
		direction: 'ASC'
	}
});

GO.tasks.categoriesStore = new GO.data.JsonStore({
	url: GO.url('tasks/category/store'),
	baseParams: {
		'task': 'categories'
	},
	root: 'results',
	totalProperty: 'total',
	id: 'id',
	fields:['id','name','user_name','checked','user_id'],
	remoteSort:true,
	sortInfo: {
		field: 'name',
		direction: 'ASC'
	}
});



go.Modules.register("legacy", 'tasks', {
	mainPanel: GO.tasks.MainPanel,
	title: t("Tasks", "tasks"),
	iconCls: 'go-tab-icon-tasks',
	entities: [{
			name: 'Task',
			links: [{
					iconCls: "entity Task blue",
					linkWindow: function() {
						var win = new GO.tasks.TaskDialog();
						win.win.closeAction = "close";
						return win;
					},
					linkDetail: function() {
						return new GO.tasks.TaskPanel();
					},
					linkDetailCards: function() {
						var forth = new go.links.DetailPanel({
							link: {
								title: t("Incomplete tasks"),
								iconCls: 'icon ic-check blue',
								entity: "Task",
								filter: null
							}
						});
	
						forth.store.setFilter('incomplete', {incompleteTasks: true});
	
						var past = new go.links.DetailPanel({						
							link: {
								title: t("Completed tasks"),
								iconCls: 'icon ic-check blue',
								entity: "Task",
								filter: null
							}
						});
	
						past.store.setFilter('completed', {completedTasks: true});
	
						return [forth, past];
					}	
			}]
	}],
	
	userSettingsPanels: ["GO.tasks.SettingsPanel"]
});
