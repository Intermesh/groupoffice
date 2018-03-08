GO.files.SelectFolder = Ext.extend(Ext.form.TriggerField,{

	triggerClass : 'fs-form-file-select',

	onTriggerClick : function(){

		if(!this.folderSelector){
			this.folderSelector = new GO.files.SelectFolderDialog({
				scope:this,
				handler:function(fs, path){
					this.setValue(path);
				}
			});
		}

		this.folderSelector.show();	
	}
});

Ext.ComponentMgr.registerType('selectfolder', GO.files.SelectFolder);


