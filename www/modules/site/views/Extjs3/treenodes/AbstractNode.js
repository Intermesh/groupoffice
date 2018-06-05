
Ext.namespace('GO.site.treeNodes');
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AbstractNode.js 16600 2014-01-10 13:48:07Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.treeNodes.AbstractNode = Ext.extend(Ext.Component , {

	treeNode : false,
	treePanel : false,
	
	contextMenu : false,
	rootContextMenu : false,
	
	extractedNode : false,
	
	isRootNode : function(extractedNode){
		if(extractedNode.modelId){
			return false;
		} else {
			return true;
		}
	},
	moveNode: function(tree, node, oldParent, newParent, index){
		return true;
	},
	beforeNodeDrop: function(node, e){
		return true;
	},
	nodeDrop: function(node, e){
		
		var sortorder = [];
		var parentNode = false;
		var parent = 0;
		
		if(e.point === "append") // The node is dropped on an item			
			parentNode = e.target;
		else // The node is dropped between two items
			parentNode = e.target.parentNode;
		
		if(parentNode.attributes.id)
				parent = parentNode.attributes.id;
			
		var children = parentNode.childNodes;
		
		for(var i=0;i<children.length;i++){				
			if(children[i].attributes.id)
				sortorder.push(children[i].attributes.id);
		}
		
		var isDropNodeInArray = sortorder.indexOf(e.dropNode.attributes.id);
			if(isDropNodeInArray === -1)
				sortorder.push(e.dropNode.attributes.id);
		
		GO.request({
			url: "site/site/treeSort",
			params: {
				parent: parent, 
				sortOrder: Ext.encode(sortorder)
			}
		});

	}
});
