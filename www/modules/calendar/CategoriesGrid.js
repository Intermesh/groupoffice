GO.calendar.CategoriesGrid = Ext.extend(GO.grid.GridPanel,{
	changed : false,
	
	initComponent : function(){
		
		Ext.apply(this,{
			standardTbar:true,
			//store: GO.calendar.categoriesStore,
//			new GO.data.JsonStore({
//				url : GO.url('calendar/category/store'),
//				baseParams : {
//					calendar_id:0
//				},
//				root : 'results',
//				totalProperty : 'total',
//				id : 'id',
//				fields : ['id', 'name','color','calendar_id'],
//				remoteSort : true
//			}),
			border: false,
			title:GO.calendar.lang.categories,
			layout:'fit',
			paging:true,
			view:new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: GO.lang['strNoItems']		
			}),
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
          {
						header: GO.lang.strName,
						dataIndex: 'name'
          },{
						header: GO.lang.color,
						dataIndex: 'color',
						renderer: this.renderColor
          },{
            header: GO.calendar.lang.calendar,
						dataIndex: 'calendar_id',
						sortable: false,
						hidden:true
          }
        ]
			})
		});

		GO.calendar.CategoriesGrid.superclass.initComponent.call(this);
	},
	
	show : function(){
		GO.calendar.CategoriesGrid.superclass.show.call(this);
		this.store.load();
	},
	
	setCalendarId : function(id){
		this.store.baseParams.calendar_id=id;
		this.setDisabled(id==0);
	},
	
	dblClick : function(grid, record, rowIndex){
		this.showCategoryDialog(record.id);
	},
	
	btnAdd : function(){				
		this.showCategoryDialog();	  	
	},
	showCategoryDialog : function(id){
		if(!this.categoryDialog){
			this.categoryDialog = new GO.calendar.CategoryDialog();

			this.categoryDialog.on('save', function(){   
				this.store.load();
				this.changed=true;	    			    			
			}, this);	
		}
		this.categoryDialog.setCalendarId(this.store.baseParams.calendar_id);
		this.categoryDialog.show(id);	  
	},
	deleteSelected : function(){
		GO.calendar.CategoriesGrid.superclass.deleteSelected.call(this);
		this.changed=true;
	},
	renderColor : function(val){
				return '<div style="display:inline-block; width:38px; height:14px; background-color:#'+val+'; margin-right:4px;"></div>';
	}
});


//
//
//
//GO.calendar.CategoriesGrid = function(config){
//
//	if(!config)
//		config = {};
//
//	config.title = GO.calendar.lang.categories;
//	config.layout='fit';
//	config.autoScroll=true;
//	config.split=true;
//	config.paging=true;
//
//	var columnModel =  new Ext.grid.ColumnModel({
//		defaults:{
//			sortable:true
//		},
//		columns:[
//		{
//			header: GO.lang.strName,
//			dataIndex: 'name'
//		},{
//			header: GO.lang.strOwner,
//			dataIndex: 'user_name',
//			sortable: false
//		}]
//	});
//
//
//	config.cm=columnModel;
//	config.view=new Ext.grid.GridView({
//		autoFill: true,
//		forceFit: true,
//		emptyText: GO.lang['strNoItems']
//	});
//
//	config.sm=new Ext.grid.RowSelectionModel();
//	config.loadMask=true;
//
//	config.tbar=[{
//		iconCls: 'btn-add',
//		text: GO.lang['cmdAdd'],
//		cls: 'x-btn-text-icon',
//		handler: function()
//		{								
//			GO.calendar.categoryDialog.show(0);					
//		},
//		scope: this
//	},{
//		iconCls: 'btn-delete',
//		text: GO.lang['cmdDelete'],
//		cls: 'x-btn-text-icon',
//		handler: function()
//		{
//			this.deleteSelected();
//		},
//		scope: this
//	}];
//
//	GO.calendar.CategoriesGrid.superclass.constructor.call(this, config);
//
//	this.on('rowdblclick', function(grid, rowIndex)
//	{
//		var record = grid.getStore().getAt(rowIndex);		
//		if(GO.settings.has_admin_permission || (record.data.user_id > 0))
//		{
//			GO.calendar.categoryDialog.show(record);
//		}
//	}, this);
//
//	this.on('show', function(){
//		if(!this.store.loaded)
//		{
//			this.store.load();
//		}
//	},this, {
//		single:true
//	});
//
//};
//
//Ext.extend(GO.calendar.CategoriesGrid, GO.grid.GridPanel);