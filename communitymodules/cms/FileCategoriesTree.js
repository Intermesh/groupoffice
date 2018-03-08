/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: 
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */

GO.cms.FileCategoriesTree = function(config){
	
	config = config || {};

	config.title = GO.cms.lang.categories;
	
	config.animate = true;
	config.layout = 'fit';
	config.border = false;
	config.autoScroll = true;
	config.rootVisible = false;
	config.split=true;
	config.enableDD = true;
	
	config.loader = new Ext.tree.TreeLoader({
		dataUrl : GO.settings.modules.cms.url
		+ 'json.php',
		baseParams : {
			task : 'categories_tree',
			file_id : 0
		},
		preloadChildren : true,
		listeners : {
			beforeload : function() {
				this.body.mask(GO.lang.waitMsgLoad);
			},
			load : function() {
				this.body.unmask();
			},
			scope : this
		}
	});
	config.rootNode = new Ext.tree.AsyncTreeNode({
			text : GO.cms.lang.root,
			draggable : false,
			id : 'category_root',
			category_id : 0,
			expanded : false,
			editable : false,
			iconCls : 'folder-default'
		});

	config.tbar=[{
		iconCls: 'btn-add',
		text: GO.lang['cmdAdd'],
		cls: 'x-btn-text-icon',
		handler: function(){
			this.addCategory();
		},
		scope: this
	},{
		iconCls: 'btn-delete',
		text: GO.lang['cmdDelete'],
		cls: 'x-btn-text-icon',
		handler: function(){
			this.deleteSelected();
		},
		scope: this
	}];

	GO.cms.FileCategoriesTree.superclass.constructor.call(this, config);

	this.setRootNode(this.rootNode);
	
	this.on('checkchange', function(node, checked) {

		if (node.attributes.id>0) {
			this.body.mask(GO.lang.waitMsgSave, 'x-mask-loading');

			var task = checked ? 'assign_category' : 'unassign_category';

			Ext.Ajax.request({
				url : GO.settings.modules.cms.url + 'action.php',
				params : {
					task : task,
					file_id : this.file_id,
					category_id : node.attributes.id
				},
				callback : function(options, success, response) {
					if (!success) {
						Ext.MessageBox.alert(GO.lang.strError,
							response.result.feedback);
					}
					this.body.unmask();
				},
				scope : this
			});
		} else {
			this.load(this.file_id);
		}

	}, this);
	
	this.on('dragdrop', function(treepanel,node,dd,e){
		Ext.Ajax.request({
				url : GO.settings.modules.cms.url + 'action.php',
				params : {
					task : 'update_category',
					file_id : this.file_id,
					id : node.attributes.id,
					name : node.attributes.text,
					parent_id : node.parentNode.attributes.id
				},
				callback : function(options, success, response) {
					if (!success) {
						Ext.MessageBox.alert(GO.lang.strError,
							response.result.feedback);
					} else {
						this.load(this.file_id);
					}
					this.body.unmask();
				},
				scope : this
			});
	},this);
	
	this.on('contextmenu',function(node,e){
		if (node.attributes.id>0)
			this.showCategoryDialog(node);
	}, this);
	
};

Ext.extend(GO.cms.FileCategoriesTree, Ext.tree.TreePanel,{

	load : function(file_id){	
		this.loader.baseParams.file_id=this.file_id=file_id;
		
		if(!this.rendered)
		{
			//render will automatically expand hidden root folder because rootVisible=false
			this.render(Ext.getBody());
		}else
		{
			this.rootNode.reload();
		}
//			
//		GO.cms.FileCategoriesTree.superclass.show.call(this);
	},
	
	showCategoryDialog : function(node) {
		if (!this.categoryDialog) {
			this.categoryDialog = new GO.cms.CategoryDialog();
			this.categoryDialog.on('save',function(){
				this.load(this.file_id);
			},this);
		}
		
		var attributes = node.attributes;
		attributes.parentName = node.parentNode.attributes.text;
		
		this.categoryDialog.show(attributes);
	},
	
	deleteSelected : function() {
		Ext.MessageBox.confirm(GO.lang.strWarning,GO.cms.lang.sure2remove,function(){
			this.loader.baseParams.delete_key = this.getSelectionModel().selNode.attributes.id;
			this.load(this.file_id);
			this.loader.baseParams.delete_key = undefined;
		},this);
	},
	
	addCategory : function() {
		this.body.mask(GO.lang.waitMsgSave);
		
		if (this.getSelectionModel().selNode)
			var parent_id = this.getSelectionModel().selNode.attributes.id;
		else
			var parent_id = 0;
		
		Ext.Ajax.request({
			url : GO.settings.modules.cms.url + 'action.php',
			params : {
				task : 'add_category',
				parent_id : parent_id,
				file_id : this.file_id
			},
			scope : this,
			callback : function (options, success,response) {
				var responseParams = Ext.decode(response.responseText);
				if (!success) {
					GO.errorDialog.show(responseParams.feedback)
				}
				else
				{
					this.load(this.file_id);
					this.body.unmask();
				}
			}
		})
	},
	
	save_category : function (data) {
		this.body.mask(GO.lang.waitMsgSave);
		Ext.Ajax.request({
			url : GO.settings.modules.cms.url + 'action.php',
			params : {
				task : 'save_category',
				id : data.id,
				file_id : this.file_id,
				used : data.used,
				name : data.name
			},
			scope : this,
			callback : function (options, success,response) {
				var responseParams = Ext.decode(response.responseText);
				if (!success) {
					GO.errorDialog.show(responseParams.feedback)
				}
				else
				{
					this.body.unmask();
				}
			}
		})
	}
	
});