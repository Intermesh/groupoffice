/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: TreePanel.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.TreePanel = function(config){
	if(!config)
	{
		config = {};
	}
	
	
	if(!config.rootNodeId)
		config.rootNodeId='root';
		
	config.layout='fit';
  config.split=true;
	config.autoScroll=true;
	config.width=200;
	
	
	config.animate=true;
	config.loader=new Ext.tree.TreeLoader(
	{
		dataUrl:GO.settings.modules.cms.url+'json.php',
		baseParams:{task: 'tree'},
		preloadChildren:true
	});
	config.containerScroll=true;
	config.rootVisible=false;
	config.collapseFirst=false;
	config.containerScroll=true;	
	config.enableDD=true;
	config.ddGroup='cmsDD';


	GO.cms.TreePanel.superclass.constructor.call(this, config);	
	
	
	// set the root node
	this.rootNode = new Ext.tree.AsyncTreeNode({
		text: GO.lang.root,
		id:config.rootNodeId,
		draggable:false,
		iconCls : 'folder-default',
		expanded:false
	});
	this.setRootNode(this.rootNode);
	
	
	this.on('nodedrop', function(e){
		

		var moveParams={};
	  //node moved

	  if(e.dropNode.attributes.file_id)
	  {
	  	moveParams.task='move_file';
	  	moveParams.file_id=e.dropNode.attributes.file_id;
	  	if(e.point=='append')
	  	{
	  		moveParams.folder_id=e.target.attributes.folder_id;
	  	}else
	  	{
	  		moveParams.folder_id=e.target.parentNode.attributes.folder_id;
	  	}
	  	
	  	e.dropNode.attributes.folder_id=e.target.attributes.folder_id;
	  	var parentNode = this.getNodeById('folder_'+moveParams.folder_id);	  	
	  }else
	  {
	  	moveParams.task='move_folder';
	  	moveParams.folder_id=e.dropNode.attributes.folder_id;
	  	if(e.point=='append')
	  	{
	  		moveParams.parent_id=e.target.attributes.folder_id;
	  	}else
	  	{
	  		moveParams.parent_id=e.target.parentNode.attributes.folder_id;
	  	}
	  	var parentNode = this.getNodeById('folder_'+moveParams.parent_id);	  	
	  }
	  
	  e.dropNode.attributes.site_id=e.target.attributes.site_id;
	  
	  //figure out new sort order
	  
	  
	  var sortOrder = [];
	  
	  for(var i=0;i<parentNode.childNodes.length;i++)
	  {
	  	var node = parentNode.childNodes[i];
	  	if(node.attributes.file_id)
	  	{
	  		var type = 'file';
	  		var id=node.attributes.file_id;
	  		
	  	}else
	  	{
	  		var type='folder';
	  		var id = node.attributes.folder_id;
	  	}
	  	var item = {
	  		fstype: type,
	  		id: id,
	  		sort_order: i	  		
	  	}
	  	
	  	sortOrder.push(item);
	  }
	  
	  moveParams.sort_order=Ext.encode(sortOrder);
		
		
		Ext.Ajax.request({
	  	url: GO.settings.modules.cms.url+'action.php',
	  	params: moveParams,
	  	callback: function (options, success, response)
	  	{
	  		var responseParams = Ext.decode(response.responseText);
	  		if(!success || !responseParams.success)
				{
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang.strSaveError);
				}
	  	},
	  	scope: this
	  });
	},
	this);
}

Ext.extend(GO.cms.TreePanel, Ext.tree.TreePanel, {	
	resetRootNode : function(id)
	{		
		this.rootNode.id=id;
		this.rootNode.attributes.id=id;
		//delete this.rootNode.children;
		//this.rootNode.expanded=false;
		//this.rootNode.childrenRendered=false;	
		this.rootNode.reload();		
	},
	
	getActivePath : function(){
		var selModel = this.getSelectionModel();
		
		if(selModel.selNodes[0]==null)
		{
			return '';
		}
		
		var node = selModel.selNodes[0];
		
		var path = '/'+node.text;
		
		if(!node.parentNode || !node.parentNode.parentNode)
		{
			return '';
		}
		
		while(!node.parentNode.parentNode.isRoot)
		{
			node = node.parentNode;
			
			path = '/'+node.text+path;
		}
		
		return path;
		
	}
	
	
});