GO.tasks.TasksPanel = function(config)
	{
		if(!config)
		{
			config = {};
		}


		this.checkColumn = new GO.grid.CheckColumn({
			id:'completed',
			dataIndex: 'completed',			
			hideInExport:true,
			header: '<i class="icon ic-check"></i>',
			width: dp(56),
			hideable:false,
			menuDisabled: true,
			sortable:false,
			groupable:false
		});

		this.checkColumn.on('change', function(record, checked){
			this.store.baseParams['completed_task_id']=record.data.id;
			this.store.baseParams['checked']=checked;

			//dirty, but it works for updating all the grids
			this.store.reload({
				callback:function(){					
					GO.tasks.tasksObservable.fireEvent('save', this, this.task_id, this.store);
				},
				scope:this
			});
			
			delete this.store.baseParams['completed_task_id'];
			delete this.store.baseParams['checked'];

		}, this);

		var fields ={
			fields:[
				'id', 'icon', 'name','completed','due_time','is_active', 'late', 'description', 'status', 'ctime', 'mtime', 'start_time', 'completion_time','disabled','tasklist_name','category_name','priority','project_name','percentage_complete','user_name'
			].concat(go.customfields.CustomFields.getFieldDefinitions("Task")),
			columns:[this.checkColumn,{
				id:'icon',
				header:"&nbsp;",
				width:dp(40),
				dataIndex: 'icon',
				renderer: this.renderIcon,
				hideable:false,
				fixed: true,
				sortable:false,
				groupable:false
			},{
				id:'name',
				width:200,
				header:t("Name"),
				dataIndex: 'name'
//				renderer:function(value, p, record){
//					if(!GO.util.empty(record.data.description))
//					{
//						p.attr = 'ext:qtip="'+Ext.util.Format.htmlEncode(record.data.description)+'"';
//					}
//					return value;
//				}
			},{
				header:t("Tasklist", "tasks"),
				dataIndex: 'tasklist_name',
				width:60,
				hidden:true,
				groupable:true
			},{
				header:t("Category", "tasks"),
				dataIndex: 'category_name',
				width:150,
				sortable:true,
				groupable:true,
				hidden:true,
			},
			{
				header:t("Priority"),
				dataIndex: 'priority',
				width:70,
				hidden:true,
				renderer : function(value, cell, record) {
					var str = '';
					switch(value)
					{
						case 0:
							str = t("Low");
							break;
						case 1:
							str = t("Normal");
							break;
						case 2:
							str = t("High");
							break;
					}
					return str;
				}
			},
			{
				header:t("Due date", "tasks"),
				dataIndex: 'due_time',	
				xtype: "datecolumn",
				dateOnly: true
			},{
				xtype: "datecolumn",
				dateOnly: true,
				header: t("Starts at", "tasks"),
				dataIndex: 'start_time',
				hidden:true,
				hidden:true
			},{
				xtype: "datecolumn",
				header: t("Completed at", "tasks"),
				dataIndex: 'completion_time',
				hidden:true,
			},{
				header: t("Status"),
				dataIndex: 'status',
				width: dp(140),
				groupable:true,
				renderer:function(value, p, record){
					return t("statuses", "tasks")[value];
				},
				hidden:true
			},{
				header: t("Percentage complete", "tasks"),
				dataIndex: 'percentage_complete',
				width:60,
				renderer:function(value, p, record){
					return value+"%";
				},
				hidden:true
			},{
				id:'user_name',
				header: t("Created by"),
				dataIndex: 'user_name',
				hidden:true,
				width:150,
				sortable:false
			},{
				xtype: "datecolumn",
				header: t("Created at"),
				dataIndex: 'ctime',
				hidden:true
			},{
				xtype: "datecolumn",
				header: t("Modified at"),
				dataIndex: 'mtime',
				hidden:true
			},{
				id:'id',
				width:200,
				header: 'ID',
				dataIndex: 'id',
				hidden: true
			}].concat(go.customfields.CustomFields.getColumns("Task"))
		};

		if(go.Modules.isAvailable("legacy", "projects2")){
			fields.columns.push({
				header: t("Project", "projects2"),
				dataIndex: 'project_name',
				hidden:true,
				width:150
			});
		} else if(go.Modules.isAvailable("legacy", "projects")){
			fields.columns.push({
				header: t("project", "projects"),
				dataIndex: 'project_name',
				hidden:true,
				width:150
			});
		}

		var reader = new Ext.data.JsonReader({
			root: 'results',
			totalProperty: 'total',
			fields: fields.fields,
			id: 'id'
		});

		config.store = new GO.data.GroupingStore({
			url: GO.url('tasks/task/store'),
//			baseParams: {
//				'show': 'all'
//			},
			reader: reader,
			sortInfo: {
				field: 'due_time',
				direction: 'ASC'
			},
			groupField: 'tasklist_name',
			remoteGroup:true,
			remoteSort:true
		});
		
		config.store.on('load', function()
		{
			if(config.store.reader.jsonData.buttonParams) {
				if(config.store.reader.jsonData.buttonParams.permissionLevel < 40)
					this.deleteSelected = function(){ /*nop*/ }; //disable grid delete action when no permissions
				else
					this.deleteSelected = GO.grid.GridPanel.prototype.deleteSelected;
			} else
				this.deleteSelected = function(){ /*nop*/ };
			
			if(config.store.reader.jsonData.feedback)
			{
				alert(config.store.reader.jsonData.feedback);
			}
			this.storeLoaded = true;
		},this)

		config.view=new Ext.grid.GroupingView({
			hideGroupedColumn:true,
			
			emptyText: t("No Tasks to display", "tasks"),
			getRowClass : function(record, rowIndex, p, store){
				if(record.data.late && !record.data.completed){
					return 'tasks-late';
				}
				if(record.data.completed){
					return 'tasks-completed';
				}
				if(record.data.is_active) {
					
					return 'tasks-active';
				}
			}
		}),
		config.sm=new Ext.grid.RowSelectionModel();

		var columnModel =  new Ext.grid.ColumnModel({
			defaults:{
				sortable:true,
				groupable:false
			},
			columns:fields.columns
		});

		config.cm=columnModel;

		config.paging=true,
		config.plugins=this.checkColumn;

		this.searchField = new GO.form.SearchField({
			store: config.store,
			width:320
		});

		config.enableDragDrop=true;
		config.ddGroup='TasklistsDD';
		config.autoExpandColumn = "name";
		//config.tbar = [t("Search") + ':', this.searchField];

		GO.tasks.TasksPanel.superclass.constructor.call(this, config);

		this.addEvents({
			checked : true
		});
		
	};


Ext.extend(GO.tasks.TasksPanel, GO.grid.GridPanel, {

	saveListenerAdded : true,
	
	storeLoaded : false,
	
	renderIcon : function(src, p, record) {
		if(typeof(record.data['priority'])!='undefined')
		{
			if(record.data['priority'] > 1)
				return '<div class="email-grid-icon btn-high-priority"></div>';

			if(record.data['priority'] < 1)
				return '<div class="email-grid-icon btn-low-priority"></div>';
		}
	}
});

