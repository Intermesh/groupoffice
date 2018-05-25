GO.files.FolderbrowserTabPanel = Ext.extend(Ext.TabPanel, {
	initComponent: function () {

		Ext.apply(this, {
			folderId: 0,
			activeTab: 0,
			height: 300,
			enableTabScroll: true,
			deferredRender: false,
			border: false,
			anchor: '100% 100%',
			listeners: {
				tabChange: function (tabPanel, tab) {

				},
				scope: this
			},
			items: [
			]
		});

		GO.files.FolderbrowserTabPanel.superclass.initComponent.call(this);
	},
	setFolderId: function (folderId) {
		this.folderId = folderId;
		this.updateTabs();
	},
	updateTabs: function () {

		//Get the folders that are a child of the selected folder.
		GO.request({
			url: 'files/folder/list',
			params: {
				folder_id: this.folderId
			},
			scope: this,
			success: function success(response, options, result) {
				//Make a new tab for each of the subfolders.
				for(var i=0; i<result.results.length;i++){
					if(result.results[i].extension == 'folder'){
						this.addTab(result.results[i]);
					}
				}
				
				this.doLayout();
				this.setActiveTab(0);
			},
			fail: function () {
				// If something fails with listing the folder, then remove the portlet. (Ususally an access denied error.)
				this.ownerCt.removePortlet();
			}
		});
		

		//For each tab, get the underlaying files/folders

	},
	
	addTab : function(folderObj){
		
		var panelConfig = {
			title:folderObj.name,
			name:folderObj.name,
			path: folderObj.path,
			folderId: folderObj.id
		};
		
		this.add(new GO.files.PortletFolderBrowserGrid(panelConfig));
	}

});
