GO.mainLayout.onReady(function(){
	if(go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("legacy", "files"))
	{
		GO.summary.portlets['portlet-folder-browser']={
			multiple:true,
			settings:{
				folderId:0,
				folderPath:''
			},
//			folderBrowserTabPanel:folderBrowserTabPanel,
			portletType: 'portlet-folder-browser',
			title: t("Show folder", "files"),
			layout:'fit',
			height:200,
			tools: [{
				id: 'gear',
				handler: function(e, target, panel){
					
					if(!this.selectFolderDialog){
						this.selectFolderDialog = new GO.files.SelectFolderDialog({
							value: panel.settings.folderPath,
							handler:function(fs, path, fullResponse){
								panel.settings.folderPath = path;
								panel.settings.folderId = fullResponse.id;
								panel.mainPanel.saveActivePortlets();
								panel.update(path,fullResponse.id);
							}
						});
					}
					
					this.selectFolderDialog.show();
				}
			},{
				id:'close',
				handler: function(e, target, panel){
					panel.removePortlet();
				}
			}],
			
			update:function(path,id){
								
				if(!this.folderBrowserTabPanel){
					this.folderBrowserTabPanel = new GO.files.FolderbrowserTabPanel();
					this.add(this.folderBrowserTabPanel);
				}
				
				// Remove all panels from the tabs
				this.folderBrowserTabPanel.removeAll();
				
				this.setTitle(t("Folder", "files") +': '+path);
				this.folderBrowserTabPanel.setFolderId(id);
			},
			
			listeners:{
				render:function(){
					this.update(this.settings.folderPath, this.settings.folderId);
				}
			},
			autoHeight:true			
		};
	}
});
