/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: FoldersDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.cms.FoldersDialog = function(config) {

	if (!config) config = {};

	Ext.apply(this, config);

	if(!config || !config.user_id) var user_id = 0 ;
	if(!config || !config.site_id) var site_id = 0 ;

	this.foldersTree = new Ext.tree.TreePanel({
		animate : true,
		region:'center',
		layout:'fit',
		border : false,
		autoScroll : true,
		rootVisible:false,
		height : 200,
		loader : new Ext.tree.TreeLoader({
			dataUrl : GO.settings.modules.cms.url
			+ 'json.php',
			baseParams : {
				task : 'tree-edit',
				user_id : user_id,
				site_id : site_id
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
		})
	});

	// set the root node
	this.rootNode = new Ext.tree.AsyncTreeNode({
		text : GO.cms.lang.root,
		draggable : false,
		id : 'folder_root',
		folder_id : 0,
		expanded : false,
		iconCls : 'folder-default'
	});
	this.foldersTree.setRootNode(this.rootNode);


	this.foldersTree.on('checkchange', function(node, checked) {

		this.body.mask(GO.lang.waitMsgSave, 'x-mask-loading');

		var task = checked ? 'subscribe' : 'unsubscribe';

		Ext.Ajax.request({
			url : GO.settings.modules.cms.url + 'action.php',
			params : {
				task : task,
				user_id : GO.cms.user_id,
				folder_id : node.attributes.folder_id
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

	}, this);


	this.dummyForm = new Ext.form.FormPanel({
		waitMsgTarget:true,
		url: GO.settings.modules.cms.url+'json.php',
		border: false,
		cls:'go-form-panel',
		autoHeight:true,
		region:'north',
		baseParams: {
			task: 'filter',
			site_id: GO.cms.site_id,
			user_id:0
		},
		items:[this.checkFilter = new Ext.form.Checkbox({
			boxLabel:GO.cms.lang.enablePermissionsPerFolder,
			hideLabel:true,
			name:'filter',
			anchor:'100%'
		})]
	});

	GO.cms.FoldersDialog.superclass.constructor.call(this, {
		layout : 'border',
		modal : false,
		shadow : false,
		minWidth : 300,
		minHeight : 300,
		height : 400,
		width : 500,
		plain : true,
		closeAction : 'hide',
		title : GO.cms.lang.folders,
		items : [this.dummyForm, this.foldersTree],
		buttons : [{
			text : GO.lang.cmdClose,
			handler : function() {
				this.hide();
			},
			scope : this
		}]
	});

	this.checkFilter.on('check', function(checkbox, checked) {

		this.body.mask(GO.lang.waitMsgSave, 'x-mask-loading');

		var task = checked ? 'enable_filter' : 'disable_filter';

		Ext.Ajax.request({
			url : GO.settings.modules.cms.url + 'action.php',
			params : {
				task : task,
				site_id : GO.cms.site_id,
				user_id: this.dummyForm.baseParams.user_id
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

	}, this);
}

Ext.extend(GO.cms.FoldersDialog, Ext.Window, {

	show : function(user_id,site_id) {

		this.site = site_id;
		this.foldersTree.loader.baseParams.user_id = GO.cms.user_id = user_id;
		this.foldersTree.loader.baseParams.site_id = this.dummyForm.baseParams.site_id = GO.cms.site_id = site_id;
		this.dummyForm.baseParams.user_id=user_id;
		this.dummyForm.form.load();

		if(!this.rendered)
		{
			//render will automatically expand hidden root folder because rootVisible=false
			this.render(Ext.getBody());
		}else
		{
			this.rootNode.reload();
		}
			

		GO.cms.FoldersDialog.superclass.show.call(this);

	}
});