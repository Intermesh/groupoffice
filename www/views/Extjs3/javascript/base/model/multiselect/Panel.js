GO.base.model.multiselect.panel = function(config){
	
	config = config || {};

	// Make sure that the permission_level field is in the fields list.
	if(config.fields && config.fields.indexOf('permission_level') === -1){
		config.fields.push('permission_level');
	}

	config.store = new GO.data.JsonStore({
		url: GO.url(config.url+'/selectedStore'),
		baseParams:{
			model_id: config.model_id
			},
		fields: config.fields,
		remoteSort: true,
		listeners:{
			update:function(store, record,operation){
				if(operation==Ext.data.Record.EDIT){
					GO.request({
						maskEl:this.getEl(),
						url: this.url+'/updateRecord',
						params: {
							model_id: this.model_id,
							record: Ext.encode(record.data)
						},
						success:function(){
							this.store.commitChanges();
						},
						fail: function(response, options, result) {
							Ext.Msg.alert(t("Error"), result.feedback);
							this.store.rejectChanges();
						},
						scope: this
					});
				}
			},
			scope:this
		}
	});

	
	if(typeof(config.paging)=='undefined')
		config.paging=true;

	config.viewConfig = {
		autoFill: true,
		forceFit: true,
		getRowClass: function(record, rowIndex, rp, ds){ // rp = rowParams
			var permissionLevel = record.get('permission_level');

			if(permissionLevel === false){
				return 'permissions-error';
			} else {
				return 'permissions-ok';
			}
		}
	};

	config.sm=new Ext.grid.RowSelectionModel();
	Ext.apply(config,{
		loadMask:true,
		layout: 'fit',
		header: false,
		tbar : [
			{
				xtype: "tbtitle",
				text: config.title
			},'->',
		{
			iconCls: 'ic-add',
			tooltip: t("Add"),
			handler: function(){
				if(!this.addDialog){
					if(!config.selectColumns) {
						config.selectColumns = config.columns;
					}
										
					this.addDialog = new GO.base.model.multiselect.addDialog({
						multiSelectPanel:this,
						url: config.url,
						fields: config.fields,
						cm: config.selectColumns,
						handler: function(grid, selected){ 
							var params={add:Ext.encode(selected)};
							
							if(config.addAttributes){
								params.addAttributes=Ext.encode(config.addAttributes)
							}
							
							this.store.load({
								params: params,
								callback: function(r, options, success){
									
									//unset add item
									if(typeof this.store.lastOptions.params.add != 'undefined') {
										delete this.store.lastOptions.params.add;
									}
									if(typeof this.store.lastOptions.params.addAttributes != 'undefined') {
										delete this.store.lastOptions.params.addAttributes;
									}
								}, 
								scope: this
							});
						},
						scope: this
						
					});
				}
				this.addDialog.show();

			},
			scope: this
		},{
			iconCls: 'ic-delete',
			tooltip: t("Delete"),
			handler: function()
			{
				this.deleteSelected();
			},
			scope: this
		}
	]
	});	

	GO.base.model.multiselect.panel.superclass.constructor.call(this, config);
	
	this.on('show',function(){	
			this.store.load();
	}, this);
};

Ext.extend(GO.base.model.multiselect.panel, GO.grid.EditorGridPanel, {

	autoLoadStore : true,
	
	model_id: 0,

	afterRender : function(){

		GO.base.model.multiselect.panel.superclass.afterRender.call(this);	

		if(this.autoLoadStore && !this.store.loaded && this.model_id)		
			this.store.load();
	},
	setModelId : function(model_id,load){
		this.store.loaded=false;
		this.model_id=this.store.baseParams.model_id=model_id;
		this.setDisabled(!model_id);
		
		if(load){
			if(model_id)
				this.store.load();
			else
				this.store.removeAll();
		}
	}
	//private
//	callHandler : function(hide){
//		if(this.handler)
//		{
//			if(!this.scope)
//			{
//				this.scope=this;
//			}
//			
//			var selectedIds = this.getSelectionModel().getSelections().keys;
//			
//			var handler = this.handler.createDelegate(this.scope, [this.grid, selectedIds]);
//			handler.call();
//		}
//		if(hide)
//		{
//			this.ownerCt.hide();
//		}
//	}	
	
});
