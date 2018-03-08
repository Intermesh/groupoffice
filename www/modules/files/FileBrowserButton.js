GO.files.FileBrowserButton = Ext.extend(Ext.Button, {
	
	model_name : "",
	id: 0,
	iconCls: 'btn-files',
	setId : function(id){
		this.id=id;
		this.setDisabled(!id);
	},
	
	initComponent : function(){
		Ext.apply(this, {				
				cls: 'x-btn-text-icon', 
				text: GO.files.lang.files,
				handler: function(){			
					

					GO.request({
						url:'files/folder/checkModelFolder',
						maskEl:this.ownerCt.ownerCt.getEl(),
						params:{								
							mustExist:true,
							model:this.model_name,
							id:this.id
						},
						success:function(response, options, result){														
							GO.files.openFolder(result.files_folder_id);
							
							//reload display panel on close
							if(this.ownerCt.ownerCt.isDisplayPanel)
								GO.files.fileBrowserWin.on('hide', this.ownerCt.ownerCt.reload, this.ownerCt.ownerCt, {single:true});
						},
						scope:this

					});
					
					
				},
				scope: this,
				disabled:true
			});
		
		GO.files.FileBrowserButton.superclass.initComponent.call(this);
	}
	
});


Ext.reg('filebrowserbutton', GO.files.FileBrowserButton);