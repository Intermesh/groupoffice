GO.smime.PublicCertsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
		url: GO.url("smime/publicCertificate/store"),
		baseParams: {
			task: 'public_certs'	    	
		},
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['id','email']
		//autoLoad:true
	});
	
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header: 'email', 
			dataIndex:  'email'
		}
		]
	});
	
	config.cm=columnModel;
	config.paging=true;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	this.searchField = new GO.form.SearchField({
		store: config.store,
		width:220
	});
		    	
	config.tbar = [{
		iconCls: 'btn-delete',
		text: t("Delete"),
		cls: 'x-btn-text-icon',
		handler: function(){
			this.deleteSelected();
		},
		scope: this
	},'-',t("Search") + ':', this.searchField];
	
	
	config.listeners={
		scope:this,
		rowdblclick:function(grid, rowIndex){
			var record = grid.getStore().getAt(rowIndex);
			
			if(!this.certWin){
				this.certWin = new GO.Window({
					title:t("SMIME Certificate", "smime"),
					width:500,
					height:300,
					closeAction:'hide',
					layout:'fit',
					items:[this.certPanel = new Ext.Panel({
						bodyStyle:'padding:10px'
					})]
				});
			}
												
			this.certWin.show();
			
			GO.request({
				maskEl:this.certPanel.getEl(),
				url: "smime/certificate/verify",
				params:{
					cert_id:record.id,
					email:record.data.email
				},
				scope: this,
				success: function(options, response, result)
				{
					this.certPanel.update(result.html);				
				}							
			});
		}
	}
	
	GO.smime.PublicCertsGrid.superclass.constructor.call(this, config);
};

Ext.extend(GO.smime.PublicCertsGrid, GO.grid.GridPanel,{
	

});


GO.smime.PublicCertsWindow = Ext.extend(GO.Window, {
	initComponent : function(){
		
		this.title=t("Public SMIME certificates", "smime");
		this.width=400;
		this.height=400;
		this.layout='fit';
		this.grid=new GO.smime.PublicCertsGrid();
		this.items=this.grid
		this.closeAction='hide';
		
		this.buttons=[{
			text:t("Close"),
			handler:function(){
				this.hide();
			},
			scope:this
		}]
		
		GO.smime.PublicCertsWindow.superclass.initComponent.call(this);
	},
	show : function(){
		this.grid.store.reload();
		
		GO.smime.PublicCertsWindow.superclass.show.call(this);
	}
});


GO.moduleManager.onModuleReady('email',function(){
	Ext.override(GO.email.MessagesGrid, {
		initComponent : GO.email.MessagesGrid.prototype.initComponent.createSequence(function(){
			this.settingsMenu.add('-');
			this.settingsMenu.add({
				iconCls:'ic-person',
				text:t("Public SMIME certificates", "smime"),
				handler:function(){
					if(!this.pubCertsWin)
						this.pubCertsWin = new GO.smime.PublicCertsWindow ();
					
					this.pubCertsWin.show();
				}
			})
		})
	})
});
