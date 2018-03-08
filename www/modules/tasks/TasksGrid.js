GO.tasks.TasksPanel = function(config)
	{
		if(!config)
		{
			config = {};
		}


		this.checkColumn = new GO.grid.CheckColumn({
			id:'completed',
			dataIndex: 'completed',
			width: 30,
			hideInExport:true,
			header: '<div class="tasks-complete-icon"></div>',
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
			fields:['id', 'icon', 'name','completed','due_time','is_active', 'late', 'description', 'status', 'ctime', 'mtime', 'start_time', 'completion_time','disabled','tasklist_name','category_name','priority','project_name','percentage_complete','user_name'],
			columns:[this.checkColumn,{
				id:'icon',
				header:"&nbsp;",
				width:23,
				dataIndex: 'icon',
				renderer: this.renderIcon,
				hideable:false,
				fixed: true,
				sortable:false,
				groupable:false
			},{
				id:'name',
				width:200,
				header:GO.lang['strName'],
				dataIndex: 'name'
//				renderer:function(value, p, record){
//					if(!GO.util.empty(record.data.description))
//					{
//						p.attr = 'ext:qtip="'+Ext.util.Format.htmlEncode(record.data.description)+'"';
//					}
//					return value;
//				}
			},{
				header:GO.tasks.lang.tasklist,
				dataIndex: 'tasklist_name',
				width:60,
				hidden:true,
				groupable:true
			},{
				header:GO.tasks.lang.category,
				dataIndex: 'category_name',
				width:150,
				sortable:true,
				groupable:true
			},
			{
				header:GO.lang.priority,
				dataIndex: 'priority',
				width:70,
				hidden:false,
				renderer : function(value, cell, record) {
					var str = '';
					switch(value)
					{
						case 0:
							str = GO.lang.priority_low;
							break;
						case 1:
							str = GO.lang.priority_normal;
							break;
						case 2:
							str = GO.lang.priority_high;
							break;
					}
					return str;
				}
			},
			{
				header:GO.tasks.lang.dueDate,
				dataIndex: 'due_time',
				width:100
			},{
				header: GO.tasks.lang.startsAt,
				dataIndex: 'start_time',
				hidden:true,
				width:110
			},{
				header: GO.tasks.lang.completedAt,
				dataIndex: 'completion_time',
				hidden:true,
				width:110
			},{
				header: GO.lang.strStatus,
				dataIndex: 'status',
				width:110,
				groupable:true,
				renderer:function(value, p, record){
					return GO.tasks.lang.statuses[value];
				}
			},{
				header: GO.tasks.lang.taskPercentage_complete,
				dataIndex: 'percentage_complete',
				width:60,
				renderer:function(value, p, record){
					return value+"%";
				}
			},{
				id:'user_name',
				header: GO.lang.createdBy,
				dataIndex: 'user_name',
				hidden:true,
				width:150,
				sortable:false
			},{
				header: GO.lang.strCtime,
				dataIndex: 'ctime',
				hidden:true,
				width:110
			},{
				header: GO.lang.strMtime,
				dataIndex: 'mtime',
				hidden:true,
				width:110
			},{
				id:'id',
				width:200,
				header: 'ID',
				dataIndex: 'id',
				hidden: true
			}]
		};

		if (GO.projects2){
			fields.columns.push({
				header: GO.projects2.lang.project,
				dataIndex: 'project_name',
				hidden:true,
				width:150
			});
		} else if(GO.projects){
			fields.columns.push({
				header: GO.projects.lang.project,
				dataIndex: 'project_name',
				hidden:true,
				width:150
			});
		}

		if(GO.customfields)
		{
			GO.customfields.addColumns("GO\\Tasks\\Model\\Task", fields);
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
			scrollOffset: 2,
			//forceFit:true,
			hideGroupedColumn:true,
			emptyText: GO.tasks.lang.noTask,
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
		
		config.tbar = [GO.lang['strSearch'] + ':', this.searchField];

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

