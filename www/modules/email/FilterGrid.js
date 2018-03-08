GO.email.FilterGrid = Ext.extend(GO.grid.GridPanel,{
	constructor : function(config){
		
		config=config||{};
		
		Ext.applyIf(config, {
			tbar:[GO.email.lang.sieveNote],
			standardTbar:true,
			title : GO.email.lang.filters,
			disabled:true,
			store: new GO.data.JsonStore({
				url : GO.url("email/filter/store"),
				baseParams : {
					account_id : 0
				},
				fields : ['id', 'field', 'keyword', 'folder', 'mark_as_read'],
				remoteSort : false
			}),
			layout : 'fit',
			border : false,
			loadMask : true,
			enableDragDrop:true,
			ddGroup:'EmailFiltersDD',			
			cm : new Ext.grid.ColumnModel([{
				header : GO.email.lang.field,
				dataIndex : 'field'
			}, {
				header : GO.email.lang.contains,
				dataIndex : 'keyword'
			}, {
				header : GO.email.lang.moveToFolder,
				dataIndex : 'folder'
			}, {
				header : GO.email.lang.markAsRead,
				dataIndex : 'mark_as_read',
				renderer : function (value) {
					return value == "1" ? GO.lang.cmdYes : GO.lang.cmdNo;
				}
			}]),
			view : new Ext.grid.GridView({
				autoFill : true,
				forceFit : true,
				emptyText : GO.lang.strNoItems
			}),
			sm : new Ext.grid.RowSelectionModel(),
			listeners:{
				scope:this,
				show:function(){					
					if(!GO.email.subscribedFoldersStore.loaded)
						GO.email.subscribedFoldersStore.load();

					this.store.load();					
				},
				render:function(){
					//enable row sorting
					var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody,
					{
						ddGroup : 'EmailFiltersDD',
						copy:false,
						notifyDrop : this.onNotifyDrop.createDelegate(this)
					});
					
				}
			}
		});
		
		this.filterDialog = new GO.email.FilterDialog();

		this.filterDialog.on('save', function(){   
			this.store.load();  			    			
		}, this);	
		
		GO.email.FilterGrid.superclass.constructor.call(this, config);
	},
	
	dblClick : function(grid, record, rowIndex){
		this.showFilterDialog(record.id);
	},
	
	btnAdd : function(){				
		this.showFilterDialog();	  	
	},
	showFilterDialog : function(id){

		this.filterDialog.show(id);	  
	},
	
	setAccountId : function(id){
		this.store.baseParams.account_id=id;
		this.setDisabled(!id);
		this.filterDialog.formPanel.baseParams.account_id=id;
	},
	
	onNotifyDrop : function(dd, e, data)
	{
		var rows=this.selModel.getSelections();
		var dragData = dd.getDragData(e);
		var cindex=dragData.rowIndex;
		if(cindex=='undefined')
		{
			cindex=this.store.data.length-1;
		}

		for(i = 0; i < rows.length; i++)
		{
			var rowData=this.store.getById(rows[i].id);

			if(!this.copy){
				this.store.remove(this.store.getById(rows[i].id));
			}

			this.store.insert(cindex,rowData);
		}

		//save sort order
		var filters = {};

		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			filters[this.store.data.items[i].get('id')] = i;
		}
		
		GO.request({
			url:'email/filter/saveSort',
			params:{
				filters:Ext.encode(filters)
			}
		})

	}
})