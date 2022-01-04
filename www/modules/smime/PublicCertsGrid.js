/* global GO, Ext, go */

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
	
	var columnModel = new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[{
			header: 'Email', 
			dataIndex:  'email'
		}]
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
		iconCls: 'ic-file-upload',
		text: t("Import"),
		handler: function(){
			this.uploadCert();
		},
		scope:this
	},{
		iconCls: 'ic-delete',
		text: t("Delete"),
		handler: function(){
			this.deleteSelected();
		},
		scope: this
	},'->',{xtype:'tbsearch', store: config.store}];
	
	
	config.listeners={
		scope:this,
		rowdblclick:function(grid, rowIndex){
			var record = grid.getStore().getAt(rowIndex);

			GO.request({
				maskEl:this.certPanel.getEl(),
				url: "smime/publicCertificate/verify",
				params:{
					cert_id:record.id,
					email:record.data.email
				},
				scope: this,
				success: function(options, response, result)
				{
					let dlg = new GO.smime.CertificateDetailWindow();
					dlg.show();
					dlg.load(record.data.email, result);
				}							
			});
		}
	};
	
	GO.smime.PublicCertsGrid.superclass.constructor.call(this, config);
};

Ext.extend(GO.smime.PublicCertsGrid, GO.grid.GridPanel,{
	
	uploadCert : function() {
		var complete = false, email = '';
		
		go.util.openFileDialog({
			multiple:true,
			autoUpload:true,
			accept: '.cer, .pem',
			listeners: {
				select: function(files) {
					email = window.prompt('Email address');
				},
				upload: function(response) {
					GO.request({
						url:'smime/publicCertificate/import',
						params:{
							blobId: response.blobId,
							email: email
						},
						success:function(){
							if(complete) {
								this.store.reload();
							}
						},
						scope:this
					});
				},
				uploadComplete: function() {
					complete = true;
				},
				scope: this
			}
		});
	}
});


GO.smime.PublicCertsWindow = Ext.extend(GO.Window, {
	initComponent : function(){
		
		this.title=t("Public SMIME certificates", "smime");
		this.width=11*dp(56);
		this.height=400;
		this.layout='fit';
		this.grid=new GO.smime.PublicCertsGrid();
		this.items=this.grid;
		this.closeAction='hide';
		
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
				iconCls:'ic-verified-user',
				text:t("Public SMIME certificates", "smime"),
				handler:function(){
					if(!this.pubCertsWin)
						this.pubCertsWin = new GO.smime.PublicCertsWindow ();
					
					this.pubCertsWin.show();
				}
			});
		})
	});
});
