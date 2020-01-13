/**
 * Store needs an id, name and checked field
 */

GO.grid.MultiSelectGrid = function (config){
	config = config || {};

	this.checkColumn = new GO.grid.CheckColumn({
		header: '&nbsp;',
		dataIndex: 'checked',
		width: dp(40),
		listeners:{
			scope:this,
			change:function(record){

				record.commit();
				this.lastRecordClicked = record;

				if(this.timeoutNumber)
					clearTimeout(this.timeoutNumber);			

				this.timeoutNumber=this.applyFilter.defer(750, this,[]);
			}
		}
	});

	if(config.allowNoSelection)
		this.allowNoSelection = true;
	
	

	Ext.applyIf(config, {
		plugins: [this.checkColumn],		
		layout:'fit',
		autoScroll:true,
		columns:this.getColumns(),		
		autoExpandColumn:'name',
		view:new Ext.grid.GridView({
			emptyText: t("No items to display")
		})
	});
	if(config.cls) {
		config.cls += ' go-multiselect-grid';
	} else {
		config.cls = 'go-multiselect-grid';
	}
	
	if(!config.showHeaders)
		config.cls +=' go-grid3-hide-headers';
	
	if(config.extraColumns)
		config.columns = config.columns.concat(config.extraColumns);
	
	GO.grid.MultiSelectGrid.superclass.constructor.call(this, config);

	if(!config.noSingleSelect){
		this.on('rowclick',function(grid, rowIndex){
				this.applyFilter([grid.store.getAt(rowIndex).id]);
			}, this);
	}

	this.store.on('load', function()
	{
		var num_selected = 0;
		for(var i=0; i<this.store.data.items.length; i++)
		{
			if(this.store.data.items[i].data.checked)
			{
				num_selected++;
			}
		}

		this.selectedAll = (num_selected == this.store.data.items.length) ? true : false;
		
		//this.fireEvent('change', this, this.getSelected(), this.getSelectedRecords());

//		if(this.allowNoSelection)
//		{
//			var text = (this.selectedAll) ? t("Deselect all") : t("Select all");
//			this.selectButton.setText(text);
//		}
	    
	},this);
	


	this.addEvents({
		change : true
	});
}

Ext.extend(GO.grid.MultiSelectGrid, GO.grid.GridPanel,{
	
	relatedStore : false,
	
	autoLoadRelatedStore : true,
	
	timeoutNumber : false,
	
	allowNoSelection : false,

	lastRecordClicked : false,

	lastSelectedIndex : -1,

	selectedAll : false,
	
	requestPrefix : '',
	
	initComponent: function() {
		if(this.tbtools) {
			this.tbar = [
				{xtype:'tbtitle',text: this.title}, 
				'->'
			];
			delete this.title;


			for(var i =0; i < this.tbtools.length; i++) {
				this.tbar.push(this.tbtools[i]);
			}
			delete this.tbtools;

			this.tbar.push({
				iconCls:'ic-done-all',
				qtip:t("Select all"),
				handler:function(){this.selectAll();},
				scope: this
			});
		} else {
			if(!this.tools) {
				this.tools = [];
				this.tools.push({
					text:t("Select all"),
					id:'plus',
					qtip:t("Select all"),
					handler:function(){this.selectAll();},
					scope: this
				});
			}
		}
		
		GO.grid.MultiSelectGrid.superclass.initComponent.call(this);
	},
	
	afterRender : function() {
		
		
		GO.grid.MultiSelectGrid.superclass.afterRender.call(this);
		
		if(this.relatedStore){
			this.on('change', function(grid, categories, records)
			{
				//if(records.length){
					this.relatedStore.baseParams[this.getRequestParam()] = Ext.encode(categories);
					this.relatedStore.load();
					delete this.relatedStore.baseParams[this.getRequestParam()];
				//}
			}, this);


			if(this.autoLoadRelatedStore){
				this.store.on('load', function()
				{
					this.relatedStore.baseParams[this.getRequestParam()] = Ext.encode(this.getSelected());
					this.relatedStore.load();		
					delete this.relatedStore.baseParams[this.getRequestParam()];
				}, this);
			}
		}
	},
	
	getRequestParam : function(){
		return this.requestPrefix+this.id;
	},
	
	getColumns : function (){
		var columns = [this.checkColumn,{
			header:t("Name"),
			dataIndex: 'name',
			id:'name',
			renderer:function(value, p, record){
				if(!GO.util.empty(record.data.tooltip)) {
					p.attr = 'ext:qtip="'+Ext.util.Format.htmlEncode(record.data.tooltip)+'"';
				}
				return value;
			}
		}];		
		return columns;
	},

	selectAll : function()
	{	
		if(this.allowNoSelection || !this.selectedAll)
		{
			var select_records = (this.selectedAll && this.allowNoSelection) ? 'clear' : 'all';
			this.applyFilter(select_records);
		}
	},

	/**
	 * Select a record in the multiSelectGrid
	 * 
	 * @param Ext.data.Record record
	 * @returns {undefined}
	 */
	selectRecord : function(record){
		this.applyFilter([record.id]);
	},

	getSelected : function(){
		var ids = [];
		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			if( this.store.data.items[i].get('checked'))
			{
				ids.push(this.store.data.items[i].id);
			}
		}
		return ids;
	},
	
	getSelectedRecords : function(){
		var records = [];
		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			if( this.store.data.items[i].get('checked'))
			{
				records.push(this.store.data.items[i]);
			}
		}
		return records;
	},
	
	clearSelections : function(){
		this.applyFilter('clear');
	},

	applyFilter : function(select_records, suppressEvent){

		this.timeoutNumber=false;

		this.lastSelectedIndex=-1;
		var records = [], ids=[], checked, current_record_id, will_be_checked;

		var changedRecords=[];

		var max = this.store.data.items.length;
		
		if(select_records=='all' && max>50){
			
			if(!confirm(t("This action will select {count} items and might cause Group-Office to become slow. Are you sure you want continue?").replace('{count}', max).replace('Group-Office', GO.settings.config.product_name))){
				return;
			}
		}


		for (var i=0; i < max;  i++)
		{
			current_record_id = this.store.data.items[i].id;
			will_be_checked= select_records && select_records!='clear' && (select_records=='all' || select_records.indexOf(current_record_id)>-1);

			if(select_records && !will_be_checked){
				checked=false;
				if(this.store.data.items[i].data.checked){
					this.store.data.items[i].data.checked=false;
					changedRecords.push(this.store.data.items[i]);
				}
			}else
			{
				if(will_be_checked){
					checked=true;
					if(GO.util.empty(this.store.data.items[i].data.checked)){					
						this.store.data.items[i].data.checked="1";
						changedRecords.push(this.store.data.items[i]);
					}
				}else
				{
					checked = this.store.data.items[i].data.checked;
				}
			}
			if(checked)
			{
				this.lastSelectedIndex = i;
				ids.push(this.store.data.items[i].id);
				records.push(this.store.data.items[i]);
			}
		}

		if(!this.allowNoSelection && (ids.length == 0))
		{
			alert(t("Select at least one item please."));

			if(this.lastRecordClicked){
				this.lastRecordClicked.set('checked', true);
				this.lastRecordClicked.commit();
			}

			this.lastRecordClicked = false;
			this.store.rejectChanges();
		}else
		{
			if(!suppressEvent)
			{
				this.fireEvent('change', this, ids, records);
			}

			if(changedRecords.length>10){
				this.getView().refresh();
			}else
			{
				for (var i = 0, max=changedRecords.length; i < max;  i++)
					this.getView().refreshRow(changedRecords[i]);
			}

			this.getSelectionModel().clearSelections();
		}
//		if(this.lastSelectedIndex>-1)
//		{
//			this.getView().focusRow(this.lastSelectedIndex);
//		}

		this.selectedAll = (records.length == this.store.data.items.length) ? true : false;
		if(this.allowNoSelection)
		{						
//			var text = (this.selectedAll) ? t("Deselect all") : t("Select all");			
			//this.selectButton.setText(text);
		}
		
	}
});
