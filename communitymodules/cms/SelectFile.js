GO.cms.SelectFile=Ext.extend(function(cfg){
	
	this.treePanel = new GO.cms.TreePanel({
    region:'west',
    title:GO.lang.menu,
		autoScroll:true,				
		width: 250,
		split:true,
		rootNodeId:'site_'+GO.cms.site_id
	});
	
	var config = {				
			title: GO.cms.lang.selectPage,
			height:400,
			width:600,
			layout:'fit',
			border:false,
			closeAction:'hide',
			tbar:[{
				iconCls: 'cms-folder-properties',
				text: GO.cms.lang.files,
				handler:function(){		
					
					GO.cms.createFileBrowser(GO.cms.editorPanel.root_folder_id, '', function(){
						var items = GO.cms.fb.getSelectedGridRecords();						
						GO.cms.popupWin.document.getElementById(GO.cms.popupField ).value=GO.settings.modules.files.full_url+'download.php?id='+items[0].data.id;
						GO.cms.fileBrowserWindow.hide();
					}, GO.cms.editorPanel.files_folder_id);
					
					this.hide();
				},
				scope:this
			}],
			items: this.treePanel,
			buttons:[
				{
					text: GO.lang.cmdOk,				        						
					handler: function(){
						var selModel = this.treePanel.getSelectionModel();

						if(selModel.selNode==null)
						{
							Ext.MessageBox.alert(GO.lang.strError, GO.lang.noItemSelected);
						}else
						{
							this.fireEvent('fileselected', selModel.selNode.attributes);
							this.hide();							
						}
					}, 
					scope: this 
				},{
					text: GO.lang.cmdClose,				        						
					handler: function(){
						this.hide();
					},
					scope:this
				}
				
			]
							        				
		};
	
	Ext.apply(cfg, config);
	
	this.addEvents({'fileselected':true});
	
	GO.cms.SelectFile.superclass.constructor.call(this, config);
	
},
Ext.Window,{
	
	show : function(){
		
		//if(this.treePanel.rootNode.id!='site_'+GO.cms.site_id)
		this.treePanel.resetRootNode('site_'+GO.cms.site_id);
		
		GO.cms.SelectFile.superclass.show.call(this);
		
	}
	
	
});