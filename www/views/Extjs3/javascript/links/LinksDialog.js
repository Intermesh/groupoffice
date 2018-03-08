/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LinksDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
  
GO.dialog.LinksDialog = function(config){
	config = config || {};

	Ext.apply(this, config);
	
	if(!config.filesupport) // Load only the models that can handle files then set to true else false
		config.filesupport = false;
	
	if(!config.filter_model_type_ids)
		config.filter_model_type_ids = [];
	
	this.grid = new GO.grid.SearchPanel({
			filesupport: config.filesupport,
			filter_model_type_ids: config.filter_model_type_ids,
			for_links: true,
			noTitle:true,
			noOpenLinks:true,
			hideDescription:config.hideDescription,
//			dontLoadOnRender:true,
			singleSelect:config.singleSelect,
			minimumWritePermission:true
		});

	this.grid.searchGrid.on('rowdblclick', this.linkItems, this);
		
	var focusSearch = function(){
		this.grid.searchField.focus(true);		
	};
	
	GO.dialog.LinksDialog.superclass.constructor.call(this, {
   	layout: 'fit',
   	focus: focusSearch.createDelegate(this),
		modal:false,
		minWidth:300,
		minHeight:300,
		height:500,
		width:700,
		border:false,
		plain:true,
		closeAction:'hide',
		title:t("Search for items to link"),
		items: this.grid,
		listeners : {
			show:function(){
				this.grid.load();
				this.grid.linkDescriptionField.reset();
			},
			scope:this
		},
		buttons: [
			{
				text: t("Ok"),
				handler: function(){							
					this.linkItems();
				},
				scope:this
			},
			{
				text: t("Close"),
				handler: function(){this.hide();},
				scope: this
			}
		]
    });
    
   this.addEvents({'link' : true});
};

Ext.extend(GO.dialog.LinksDialog, Ext.Window, {
	
	folder_id : 0,
	
	setLinkRecords : function(gridRecords)
	{
		this.fromLinks = [];
		for (var i = 0;i<gridRecords.length;i++)
		{
			this.fromLinks.push({'model_id' : gridRecords[i].data['model_id'], 'model_name' : gridRecords[i].data['model_name']});
		}
	},
	setSingleLink : function(model_id, model_name)
	{
		this.fromLinks=[{"model_id":model_id,"model_name":model_name}];
	},

	selectFolder : function(toLinks){



		
	},
	
	linkItems : function()	{
		var selectionModel = this.grid.searchGrid.getSelectionModel();
		var records = selectionModel.getSelections();

		this.tolinks = [];

		for (var i = 0;i<records.length;i++)
		{
			this.tolinks.push({'model_id' : records[i].data['model_id'], 'model_name' : records[i].data['model_name']});
		}

		if(this.tolinks.length==1){
			if(!this.selectFolderWindow){

				this.selectFolderTree = new GO.LinksTree();
				this.selectFolderTree.on('dblclick', function(node){
					var to_folder_id = parseInt(node.id.replace('lt-folder-',''));
					this.sendLinkRequest(this.tolinks, to_folder_id);
					this.selectFolderWindow.hide();
				}, this);

				this.selectFolderWindow = new GO.Window({
					layout:'fit',
					title:t("Select a folder please"),
					items:this.selectFolderTree,
					closeAction:'hide',
					width:400,
					height:400,
					modal:true,
					closable:true,
					buttons:[{
							text:t("Ok"),
							handler:function(){

								var node = this.selectFolderTree.getSelectionModel().getSelectedNode();
								if(!node){
									alert(t("Select a folder please"));
								}

								var to_folder_id = parseInt(node.id.replace('lt-folder-',''));
								this.sendLinkRequest(this.tolinks, to_folder_id);
								this.selectFolderWindow.hide();
							},
							scope:this
					}]
				});
			}
			this.selectFolderWindow.show();

			this.selectFolderTree.loadLinks(this.tolinks[0]['model_id'], this.tolinks[0]['model_name'], function(rootNode){
				if(!rootNode.childNodes.length){
					this.selectFolderWindow.hide();
					this.sendLinkRequest(this.tolinks);
				}
			}, this);
			
		}else
		{
			this.sendLinkRequest(this.tolinks);
		}

		
	},

	sendLinkRequest : function(tolinks, to_folder_id){
		var to_folder_id = to_folder_id || 0;
		Ext.Ajax.request({
			url: GO.url('core/link'),
			params: {
				fromLinks: Ext.encode(this.fromLinks),
				toLinks: Ext.encode(tolinks),
				description:this.grid.linkDescriptionField.getValue(),
				from_folder_id: this.folder_id,
				to_folder_id : to_folder_id
				},
			callback: function(options, success, response)
			{
				if(!success)
				{
					Ext.MessageBox.alert(t("Error"), response.result.errors);
				}else
				{
					this.fireEvent('link');
					this.grid.searchGrid.getSelectionModel().clearSelections();
					
					this.hide();
				}
			},
			scope: this
		});
	},
	
	show : function(filesupport){
		if(!filesupport) // Load only the models that can handle files then set to true else false
			filesupport = this.filesupport
		
		this.setFileSupport(filesupport);
		GO.dialog.LinksDialog.superclass.show.call(this);
	},
	setFileSupport : function(filesupport){
		this.grid.setFileSupport(filesupport);
	}
});


