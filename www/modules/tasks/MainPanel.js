GO.tasks.MainPanel = function(config){
	
	if(!config)
	{
		config = {};
	}
		
	this.taskListsStore = new GO.data.JsonStore({
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
		title: GO.tasks.lang.tasklists,	
		relatedStore: this.gridPanel.store,
		autoLoadRelatedStore:false,
		split:true
	});

	this.taskListsPanel.on('drop', function(type)
	{
	    this.taskListsPanel.store.reload();
			this.gridPanel.store.reload();
	}, this);

//	this.taskListsPanel.on('change', function(grid, tasklists, records)
//	{                		                
////		this.gridPanel.store.baseParams.tasks_tasklist_filter = Ext.encode(tasklists);
////		this.gridPanel.store.load();
//		this.tasklist_ids = tasklists;
//
//		if(records.length)
//		{
//			this.addTaskPanel.populateComboBox(records);
//
//			this.tasklist_id = records[0].data.id;
//			this.tasklist_name = records[0].data.name;
//		}
//
//		// this.gridPanel.store.baseParams.tasklists;
//	}, this);

this.gridPanel.store.on('load', function(store, records, options)
	{    
		var lists = store.reader.jsonData.selectable_tasklists;
				
		if(lists && lists.length){
			this.addTaskPanel.populateComboBox(lists);
			this.tasklist_id = lists[0].data.id;
			this.tasklist_name = lists[0].data.name;
		}
//		
//		if(records.length)
//		{
//			this.addTaskPanel.populateComboBox(records);
//
//			this.tasklist_id = records[0].data.id;
//			this.tasklist_name = records[0].data.name;
//		}
	}, this);
	
	var filterPanel = new Ext.form.FormPanel({
		title:GO.tasks.lang.filter,
		height:180,
//		id:'ta-filter-form',
//		stateId:'ta-filter-form',
//		cls:'go-form-panel',
		waitMsgTarget:true,
		region:'north',
		border:true,
		split:true,
		items: [{
				hideLabel:true,
				anchor:'100%',
				xtype:'radiogroup',
				value:GO.tasks.show,
				columns: 1,
				listeners:{
					change:function(radiogroup, checkedbox){
						this.gridPanel.store.baseParams['show']=checkedbox.inputValue;
						this.gridPanel.store.load();
						//delete this.gridPanel.store.baseParams['show'];
					},
					scope:this
				},
				items: [{
					boxLabel:  GO.tasks.lang.active,
					name: 'show',
					inputValue: 'active'
				},{
					boxLabel: GO.tasks.lang.dueInSevenDays,
					name: 'show',
					inputValue: 'sevendays'
				},{
					boxLabel: GO.tasks.lang.overDue,
					name: 'show',
					inputValue: 'overdue'
				},{
					boxLabel: GO.tasks.lang.incompleteTasks,
					name: 'show',
					inputValue: 'incomplete'
				},{
					boxLabel: GO.tasks.lang.completed,
					name: 'show',
					inputValue: 'completed'
				},{
					boxLabel: GO.tasks.lang.futureTasks,
					name: 'show',
					inputValue: 'future'
				},{
					boxLabel: GO.tasks.lang.all,
					name: 'show',
					inputValue: 'all'
				}]
			}]
	});
      
	this.categoriesPanel= new GO.grid.MultiSelectGrid({
		id:'ta-categories-grid',
		title:GO.tasks.lang.categories,
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


	this.addTaskPanel = new GO.tasks.AddTaskPanel({
		region:'north'
	});

	
			
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
			
	this.taskPanel = new GO.tasks.TaskPanel({
		//title:GO.tasks.lang.task,
		region:'east',
		width:400,
		border:true
	});

	this.accordionPanel = new Ext.Panel({
		region:'center',
		titlebar: false,
		autoScroll:false,
//		closeOnTab: true,
		resizable:true,
//		layout:'border',
		layoutConfig:{hideCollapseTool:true},
		layout:'accordion',
		baseCls: 'x-plain',
		items:[
			this.taskListsPanel,
			this.categoriesPanel
		]
	});

	config.layout='border';
	config.items=[
		new Ext.Panel({
			region:'west',
			titlebar: false,
			autoScroll:false,
			closeOnTab: true,
			width: 230,
			split:true,
			resizable:true,
			layout:'border',
			baseCls: 'x-plain',
			items:[
				filterPanel,
				this.accordionPanel
			]
		}),
		{
			//title:GO.tasks.lang.tasks,
			region:'center',
			border:false,
			layout:'border',
			items:[ this.addTaskPanel,this.gridPanel]
		},
	this.taskPanel
	];
	
	config.tbar=new Ext.Toolbar({
			cls:'go-head-tb',
			items: [{
		      	 	xtype:'htmlcomponent',
				html:GO.tasks.lang.name,
				cls:'go-module-title-tbar'
			},{
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
			},
//			this.addButton = new Ext.Button({
//				iconCls: 'btn-add',
//				text: GO.lang['cmdAdd'],
//				cls: 'x-btn-text-icon',
//				handler: function(){
//					this.taskPanel.reset();
//					GO.tasks.showTaskDialog({
//						tasklist_id: this.tasklist_id,
//						tasklist_name: this.tasklist_name
//					});
//
//				},
//				scope: this
//			}),this.deleteButton = new Ext.Button({
//				iconCls: 'btn-delete',
//				text: GO.lang['cmdDelete'],
//				cls: 'x-btn-text-icon',
//				handler: function(){
//					this.gridPanel.deleteSelected({
//						callback : this.taskPanel.gridDeleteCallback,
//						scope: this.taskPanel
//					});
//				},
//				scope: this
//			})
			{
				iconCls: 'btn-settings',
				text: GO.lang.administration,
				cls: 'x-btn-text-icon',
				handler: function(){
					this.showAdminDialog();
				},
				scope: this
			},
//			{
//				iconCls: 'btn-export',
//				text: GO.lang.cmdExport,
//				cls: 'x-btn-text-icon',
//				handler:function(){
////					var config = {};
////					config.colModel = this.gridPanel.getColumnModel();
////					config.title = GO.tasks.lang.tasks;
////
////					var query = this.gridPanel.searchField.getValue();
////					if(!GO.util.empty(query))
////					{
////						config.subtitle= GO.lang.searchQuery+': '+query;
////					}else
////					{
////						config.subtitle='';
////					}
////
////					if(!this.exportDialog)
////					{
////						this.exportDialog = new GO.ExportQueryDialog({
////							query:'get_tasks'
////						});
////					}
////					this.exportDialog.show(config);
//
//				
//				if(!this.exportDialog)
//				{
//					this.exportDialog = new GO.ExportGridDialog({
//						url: 'tasks/task/export',
//						name: 'tasks',
//						documentTitle:'ExportTask',
//						colModel: this.gridPanel.getColumnModel()
//					});
//				}
//				
//				this.exportDialog.show();
//
//				},
//				scope: this
//			},
			this.exportMenu = new GO.base.ExportMenu({className:'GO\\Tasks\\Export\\CurrentGrid'}),
			{
				iconCls: 'btn-refresh',
				text: GO.lang['cmdRefresh'],
				cls: 'x-btn-text-icon',
				handler: function(){
					this.taskListsStore.load();
					this.gridPanel.store.load();
				},
				scope: this
			}
			]
		});
	
	this.exportMenu.setColumnModel(this.gridPanel.getColumnModel());
	
	GO.tasks.MainPanel.superclass.constructor.call(this, config);
	
	this.on('show', function(){
		//GO.tasks.notificationEl.setDisplayed(false);
	},this);
	
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
			tasklists:{r:"tasks/tasklist/store"},				
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

//			GO.tasks.writableTasklistsStore.on('load', function(){
//				if(GO.tasks.writableTasklistsStore.reader.jsonData.new_default_tasklist){
//					GO.tasks.defaultTasklist=GO.tasks.writableTasklistsStore.reader.jsonData.new_default_tasklist;
//				}
//			
//			}, this);
			
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
				title: GO.tasks.lang.tasklists,
				store: GO.tasks.writableTasklistsStore,
				deleteConfig: {
					callback:function(){
						this.taskListsStore.load();
					},
					scope:this
				},
				columns:[{
					header:GO.tasks.lang.id,
					dataIndex: 'id',
					sortable:true,
					hidden:true,
					width:20
				},{
					header:GO.lang['strName'],
					dataIndex: 'name',
					sortable:true
				},{
					header:GO.lang['strOwner'],
					dataIndex: 'user_name'
				}],
				view:new  Ext.grid.GridView({
					autoFill:true
				}),
				sm: new Ext.grid.RowSelectionModel(),
				loadMask: true,
				tbar: [{
					iconCls: 'btn-add',
					text: GO.lang['cmdAdd'],
					cls: 'x-btn-text-icon',
					handler: function(){						
						this.tasklistDialog.show();
					},
					disabled: !GO.settings.modules.tasks.write_permission,
					scope: this
				},{
					iconCls: 'btn-delete',
					text: GO.lang['cmdDelete'],
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
				text: GO.lang['cmdDelete'],
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
				title: GO.tasks.lang.categories,
				store: GO.tasks.categoriesStore,
				deleteConfig: {
					callback:function(){
						GO.tasks.categoriesStore.load();
					},
					scope:this
				},
				columns:[{
					header:GO.lang['strName'],
					dataIndex: 'name',
					sortable:true
				},{
					header:GO.lang['strOwner'],
					dataIndex: 'user_name'
				}],
				view:new  Ext.grid.GridView({
					autoFill:true
				}),
				sm: new Ext.grid.RowSelectionModel(),
				loadMask: true,
				tbar: [{
					iconCls: 'btn-add',
					text: GO.lang['cmdAdd'],
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
				title: GO.lang.cmdSettings,
				layout:'fit',
				modal:false,
				minWidth:300,
				minHeight:300,
				height:400,
				width:600,
				closeAction:'hide',				
				items: this.tabPanel,
				buttons:[{
					text:GO.lang['cmdClose'],
					handler: function(){
						this.adminDialog.hide()
					},
					scope: this
				}]
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

	if(!GO.tasks.taskDialog)
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

/*
 * This will add the module to the main tabpanel filled with all the modules
 */
 
GO.moduleManager.addModule('tasks', GO.tasks.MainPanel, {
	title : GO.tasks.lang.tasks,
	iconCls : 'go-tab-icon-tasks'
});
/*
 * If your module has a linkable item, you should add a link handler like this. 
 * The index (no. 1 in this case) should be a unique identifier of your item.
 * See classes/base/links.class.inc for an overview.
 * 
 * Basically this function opens a task window when a user clicks on it from a 
 * panel with links. 
 */
GO.linkHandlers["GO\\Tasks\\Model\\Task"]=function(id, link_config){

	if(!GO.tasks.taskLinkWindow){
		var taskPanel = new GO.tasks.TaskPanel();
		GO.tasks.taskLinkWindow = new GO.LinkViewWindow({
			title: GO.tasks.lang.task,
			closeAction:'hide',
			items: taskPanel,
			taskPanel: taskPanel
		});
	}
	GO.tasks.taskLinkWindow.taskPanel.load(id);
	GO.tasks.taskLinkWindow.show();
	return GO.tasks.taskLinkWindow;
}

GO.linkPreviewPanels["GO\\Tasks\\Model\\Task"]=function(config){
	config = config || {};
	return new GO.tasks.TaskPanel(config);
}


GO.newMenuItems.push({
	text: GO.tasks.lang.task,
	iconCls: 'go-model-icon-GO\\Tasks\\Model\\Task',
	itemId:'ta-new-task',
	handler:function(item, e){

		var taskShowConfig = item.parentMenu.taskShowConfig || {};
		taskShowConfig.link_config=item.parentMenu.link_config

		GO.tasks.showTaskDialog(taskShowConfig);
	}
});
	
if(GO.addressbook){	
	GO.quickAddPanel.addButton(new Ext.Button({
		iconCls:'img-call-add',
		cls: 'x-btn-icon', 
		tooltip:GO.tasks.lang.scheduleCall,
		handler: function(){
			if(!GO.tasks.scheduleCallDialog)
				GO.tasks.scheduleCallDialog = new GO.tasks.ScheduleCallDialog();
			
			GO.tasks.scheduleCallDialog.show(0,{link_config : this.linkConfig});			
		}, 
		scope: this
	}),0);
}

//GO.mainLayout.onReady(function(){
//
//	//GO.checker is not available in some screens like accept invitation from calendar
//	if(GO.checker){
//		//create notify icon
//		var notificationArea = Ext.get('notification-area');
//		if(notificationArea)
//		{
//			GO.tasks.notificationEl = notificationArea.createChild({
//				id: 'ta-notify',
//				tag:'a',
//				href:'#',
//				style:'display:none'
//			});
//			GO.tasks.notificationEl.on('click', function(){
//				GO.mainLayout.openModule('tasks');
//			}, this);
//		}
//
//		GO.checker.on('check', function(checker, data){
//			var tp = GO.mainLayout.getModulePanel('tasks');
//
//			if(data.tasks.active!=GO.tasks.last_active && data.tasks.active>0)
//			{
//				
//				if(!tp || !tp.isVisible())
//					GO.tasks.notificationEl.setDisplayed(true);
//			}
//
//			GO.tasks.notificationEl.update(data.tasks.active);			
//			GO.tasks.last_active=data.tasks.active;			
//		});
//	}
//});
