/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MultifileDialog.js 8376 2011-10-24 09:55:16Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.MultifileDialog = Ext.extend(GO.Window , {
	
	initComponent : function(){
		
		this.buttonClose = new Ext.Button({
			text: t("Close"),
			handler: function(){
				this.hide();
			},
			scope:this
		});
		
		this.multifileView = new GO.site.MultifileView();		
		
		this.addButton = new Ext.Button({
			iconCls:'btn-add',
			text : t("Add"),
			handler : function()
			{
				if(go.Modules.isAvailable("legacy", "files"))
				{
					GO.files.createSelectFileBrowser();

					GO.selectFileBrowser.setFileClickHandler(function(){	

						var fileIds = [];
						var selections = GO.selectFileBrowser.getSelectedGridRecords();
						for (var i = 0; i < selections.length; i++){
							fileIds.push(selections[i].data.id);
						}

						this.multifileView.afterUpload({addFileStorageFilesById:Ext.encode(fileIds)});
						GO.selectFileBrowserWindow.hide();
					}, this);

					
					GO.selectFileBrowser.setFilesFilter('');
					
					GO.request({
						url:'files/folder/checkModelFolder',
						//maskEl:this.ownerCt.getEl(),
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
			},
			scope : this
		});

		Ext.apply(this, {
			goDialogId:'multifile-dialog',
			title:t("Multi File", "site"),
			layout:'fit',
			//autoScroll:true,
			modal:true,
			height:400,
			width:570,
			tbar : new Ext.Toolbar({
				cls:'go-head-tb',
				items: [
				this.addButton,
				{
					itemId:'delete',
					iconCls: 'btn-delete',
					text: t("Delete"),
					cls: 'x-btn-text-icon',
					handler: function(){
						this.multifileView.deleteSelected();

					},
					scope: this
				},
				'-',
				{
					iconCls: 'btn-refresh',
					text: t("Refresh"),
					cls: 'x-btn-text-icon',
					handler: function(){
						this.multifileView.store.load();
					},
					scope: this
				}]
			}),
			buttons: [this.buttonClose],
			items: [this.multifileView]
		});
		
		GO.site.MultifileDialog.superclass.initComponent.call(this);		
	},
	show : function(model_id,field_id){
		
		GO.site.multifileStore.baseParams.model_id = model_id;
		GO.site.multifileStore.baseParams.field_id = field_id;
		GO.site.multifileStore.load();
		
		GO.site.MultifileDialog.superclass.show.call(this);
	}
});
