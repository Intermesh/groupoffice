GO.files.SelectFile = Ext.extend(Ext.form.TriggerField,{

	triggerClass : 'fs-form-file-select',

	filesFilter : '',

	root_folder_id : 0,

	files_folder_id : 0,


	onTriggerClick : function(){


		GO.files.createSelectFileBrowser();

		GO.selectFileBrowser.setFileClickHandler(function(r){
			if(r){
				this.setValue(r.data.path);
			}else
			{
				this.setValue(GO.selectFileBrowser.path);
			}

			GO.selectFileBrowserWindow.hide();
		}, this);

		GO.selectFileBrowser.setFilesFilter(this.filesFilter);
		GO.selectFileBrowser.setRootID(this.root_folder_id, this.files_folder_id);
		GO.selectFileBrowserWindow.show();
	}

});

Ext.ComponentMgr.registerType('selectfile', GO.files.SelectFile);