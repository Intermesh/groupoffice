

GO.files.openDetailViewFileBrowser = function () {

	var dv;
	if(this.detailView){
		dv = this.detailView;
	} else {
		dv = this.findParentByType("detailview");
		if (!dv) {
			dv = this.findParentByType("displaypanel") || this.findParentByType("tmpdetailview"); //for legacy modules
		}
	}

	GO.request({
		url: 'files/folder/checkModelFolder',
		maskEl: dv.getEl(),
		jsonData: {},
		params: {
			mustExist: true,
			model: dv.model_name || dv.entity || dv.entityStore.entity.name,
			id: dv.data.id
		},
		success: function (response, options, result) {
			var fb = GO.files.openFolder(result.files_folder_id);
			fb.model_name = dv.model_name || dv.entity || dv.entityStore.entity.name;
			fb.model_id = dv.data.id;
			fb.contact_id = dv.data.contact_id || dv.data.contactId || null; // if you want to email or sign files later

			folderId = result.files_folder_id;

			// //hack to update entity store
			// var store = go.Db.store(fb.model_name);
			// if (store && store.data[fb.model_id]) {
			// 	store.data[fb.model_id].filesFolderId = result.files_folder_id;
			// }

			//reload display panel on close
			GO.files.fileBrowserWin.on('hide', function () {

				fb.model_id = null;
				fb.model = null;
				fb.contact_id = null;

				var filesDetailPanels = dv.findByType("filesdetailpanel");
				filesDetailPanels.forEach(function(fdp) {
					fdp.load(folderId);
				});
			}, this, {single: true});
		},
		scope: this

	});


};
GO.files.FileBrowserMenuItem = Ext.extend(Ext.menu.Item, {
	iconCls: 'ic-folder',
	text: t("Files"),
	handler: GO.files.openDetailViewFileBrowser
});

GO.files.DetailFileBrowserButton = Ext.extend(Ext.Button, {
	iconCls: 'ic-folder',
	tooltip: t("Files"),
	handler: GO.files.openDetailViewFileBrowser
});

Ext.reg("filebrowsermenuitem", GO.files.FileBrowserMenuItem);
Ext.reg('detailfilebrowserbutton', GO.files.DetailFileBrowserButton);