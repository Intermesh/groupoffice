GO.site.SelectFile = Ext.extend(Ext.form.TriggerField,{

	triggerClass : 'fs-form-site-file-select',

//	filesFilter : '',
//
//	root_folder_id : 0,
//
//	files_folder_id : 0,


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
		
		GO.request({
			url:'files/folder/checkModelFolder',
			maskEl:this.ownerCt.ownerCt.getEl(),
			params:{								
				mustExist:true,
				model:'GO\\Site\\Model\\Site',
				id:GO.site.currentSiteId
			},
			success:function(response, options, result){														
				GO.selectFileBrowser.setRootID(result.files_folder_id);
				GO.selectFileBrowserWindow.show();
			},
			scope:this

		});
		
	}

});

Ext.ComponentMgr.registerType('siteselectfile', GO.site.SelectFile);
