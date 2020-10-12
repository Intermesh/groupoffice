GO.base.model.multiselect.addDialog = function(config){
	
	Ext.apply(this, config);
	
	this.store = new GO.data.JsonStore({
		url: GO.url(config.url+'/selectNewStore'),
		baseParams:{
			model_id: 0
			},
		fields: config.fields,
		remoteSort: true
	});
	
	this.searchField = new GO.form.SearchField({
		store: this.store,
		width:476
	});
	
	this.grid = new GO.grid.EditorGridPanel({
		paging:true,
		border:false,
		store: this.store,
		view: new Ext.grid.GridView({
			autoFill: true,
			forceFit: true
		}),
		cm: new Ext.grid.ColumnModel({
			defaults:{
				sortable:true
			},
			columns:config.cm
		}),
		sm: new Ext.grid.RowSelectionModel()	
	});
	
	GO.base.model.multiselect.addDialog.superclass.constructor.call(this, {
		layout: 'fit',
		modal:true,
		height:600,
		tbar: [this.searchField],
		width:500,
		loadMask:true,
		closeAction:'hide',
		title: t("Please select..."),
		items: this.grid,
		buttons: [
		{
			text: t("Add"),
			handler: function (){
				this.callHandler(true);
			},
			scope:this
		}]
	});
	
	this.grid.on('rowdblclick',function(){this.callHandler(true);},this);
};

Ext.extend(GO.base.model.multiselect.addDialog, GO.Window, {
	multiSelectPanel: false,
	
	show : function(){
		GO.base.model.multiselect.addDialog.superclass.show.call(this);
		this.grid.store.removeAll();
		this.grid.store.baseParams.model_id=this.multiSelectPanel.model_id;
		this.grid.store.load();
	},
	//private
	callHandler : function(hide){
		if(this.handler)
		{
			if(!this.scope)
			{
				this.scope=this;
			}
			this.handler.call(this.scope, this.grid, this.grid.selModel.selections.keys);
		}
		if(hide)
		{
			this.hide();
		}
	}	
	
});
