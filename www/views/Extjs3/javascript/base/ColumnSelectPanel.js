GO.base.ColumnSelectPanel = Ext.extend(Ext.Panel,{
	allCols: {},
	initComponent : function(){

		var cols = [{
			dataIndex: 'name',
			header: t("Name"),
			id:'name',
			sortable:true
		},{
			dataIndex: 'label',
			header: t("Label"),
			id:'label',
			sortable:true
		}];
	
		var fields = [{
				name:'name',
				sortType:Ext.data.SortTypes.asUCString
			},{	
				name:'label',
				sortType:Ext.data.SortTypes.asUCString
		}];

		this.allColGrid = new GO.grid.GridPanel({
			ddGroup: 'selectedColGridDDGroup',
			store: new Ext.data.JsonStore({
				fields: fields
			}),
			noDelete:true,
			columns: cols,
			enableDragDrop: true,
			stripeRows: true,
			autoExpandColumn: 'name',
			title: t("Available columns")
		});

		this.selectedColGrid = new GO.grid.GridPanel({
			ddGroup: 'selectedColGridDDGroup',
			store: new Ext.data.JsonStore({
				fields: fields
			}),
			noDelete:true,
			columns: cols,
			enableDragDrop: true,
			stripeRows: true,
			autoExpandColumn: 'name',
			title: t("Selected Columns")
		});

		this.helpText = new GO.form.HtmlComponent({
			region: 'north',
			hegith:30,
			html:t("Drag or double-click the columns you want to export from the left grid to the right."),
			style:'padding:10px;background-color:#FFF;'
		});

		Ext.applyIf(this, {
			title: t("Columns"),
			layout: 'border',
			items:[
				this.helpText,
				{
					type:'container',
					region:'center',
					layout:'hbox',
					defaults: {flex: 1}, //auto stretch
					layoutConfig: {align: 'stretch'},
					items:[
						this.allColGrid,
						this.selectedColGrid
					]
				}
			]
		});
		
		this.allColGrid.on('rowdblclick', function(grid, rowIndex, e){
			this.moveItem(grid,this.selectedColGrid,rowIndex);
		},this);
		
		this.selectedColGrid.on('rowdblclick', function(grid, rowIndex, e){
			this.moveItem(grid,this.allColGrid,rowIndex);
			this.allColGrid.store.sort('name', 'ASC');
		},this);
		
		this.allColGrid.on('viewready', function(grid){
			
			var grid = grid;
			
			// This will make sure we only drop to the  view scroller element
			var allColGridDropTargetEl = this.allColGrid.getView().scroller.dom;
			var allColGridDropTarget = new Ext.dd.DropTarget(allColGridDropTargetEl, {
				ddGroup: 'selectedColGridDDGroup',
				notifyDrop: function (ddSource, e, data) {
					var records = ddSource.dragData.selections;
					
					var t = Ext.lib.Event.getTarget(e);
					var rindex = grid.getView().findRowIndex(t);

					Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
					if(rindex === false){
						grid.store.add(records);
					} else {
						grid.store.insert(rindex,records);
					}
					return true;
				}
			});
			
		},this);
		
		this.selectedColGrid.on('viewready', function(grid){
			
			var grid = grid;
			
			// This will make sure we only drop to the view scroller element
			var selectedColGridDropTargetEl = this.selectedColGrid.getView().scroller.dom;
			var selectedColGridDropTarget = new Ext.dd.DropTarget(selectedColGridDropTargetEl, {
				ddGroup: 'selectedColGridDDGroup',
				notifyDrop: function (ddSource, e, data) {
					var records = ddSource.dragData.selections;
					
					var t = Ext.lib.Event.getTarget(e);
					var rindex = grid.getView().findRowIndex(t);

					Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
					
					if(rindex === false){
						grid.store.add(records);
					} else {
						grid.store.insert(rindex,records);
					}
					return true;
				}
			});
			
		},this);
		
		
		GO.base.ColumnSelectPanel.superclass.initComponent.call(this);
	},
	
	/**
	 * Move an item from one grid to another
	 * 
	 * @param grid from
	 * @param grid to
	 * @param int itemIndex
	 * @returns 
	 */
	moveItem : function(from, to, itemIndex){
		var record = from.store.getAt(itemIndex);
		
		if(Ext.isDefined(record)){
			from.store.removeAt(itemIndex);		
			to.store.add(record);
		}
	},
	
	/**
	 * Pass a comma separated string of column names that need to be selected by default.
	 * Eg. 'id,name,company.name,customfield.col1'
	 * 
	 * @param string cols
	 * @returns 
	 */
	setSelected : function(cols){
		var colArray = cols.split(',');
	
		for (var i = 0; i < colArray.length; i++) {
			var index = this.allColGrid.store.findExact('name',colArray[i]);
			
			if(index != -1){
				this.moveItem(this.allColGrid,this.selectedColGrid,index);
			}
			
		}
	},
	
	loadData : function(allCols){
		this.allColGrid.store.removeAll();
		this.selectedColGrid.store.removeAll();
		
		this.allCols = allCols;
		this.allColGrid.store.loadData(allCols);
		this.allColGrid.store.sort('name', 'ASC');
	},
	
	reset : function(){
		if(GO.util.empty(this.allCols)){
			this.allCols = {};
		}
		
		this.loadData(this.allCols);
	},

	getSelected : function(){
		var ids = [];
		for (var i = 0; i < this.selectedColGrid.store.data.items.length;  i++){
			ids.push(this.selectedColGrid.store.data.items[i].id);
		}
		return ids;
	},
	
	getSelectedRecords : function(){
		var records = [];
		for (var i = 0; i < this.selectedColGrid.store.data.items.length;  i++){
			records.push(this.selectedColGrid.store.data.items[i]);
		}
		return records;
	}
});			
