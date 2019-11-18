/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: FoldersDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.FoldersDialog = function(config) {
	Ext.apply(this, config);

	this.foldersTree = new Ext.tree.TreePanel({
		animate : true,
		border : false,
		autoScroll : true,
		height : 200,
		loader : new GO.base.tree.TreeLoader({
			dataUrl : GO.url("email/account/subscribtionsTree"),
			baseParams : {
				list_all : 1,
				account_id : 0
			},
			preloadChildren : true,
			listeners : {
				beforeload : function() {
					this.body.mask(t("Loading..."));
				},
				load : function() {
					this.body.unmask();
				},
				scope : this
			}
		})
	});
	
	this.foldersTree.on('load', function(node) {
		if(node.attributes.mailbox == "INBOX") {
			node.disable();
		}
	});

	// set the root node
	this.rootNode = new Ext.tree.AsyncTreeNode({
		text : t("Root", "email"),
		draggable : false,
		id : 'root',
		expanded : false
	});
	this.foldersTree.setRootNode(this.rootNode);

	this.rootNode.on('load', function() {
		this.rootNode.select();

	}, this);

	this.foldersTree.on('checkchange', function(node, checked) {
		
		
		var route = checked ? 'email/folder/subscribe' : 'email/folder/unsubscribe';
		var mailboxs = [];
		mailboxs.push(node.attributes.mailbox);
		
		GO.request({
			maskEl:this.body,
			url : route,
			params : {
				account_id : this.account_id,
				mailboxs : Ext.util.JSON.encode(mailboxs) 
			},
			fail: function(response, options, result) {
				Ext.Msg.alert(t("Error"), result.feedback);
				this.foldersTree.getRootNode().reload();
			},
			scope : this
		});

	}, this);

	var treeEdit = new Ext.tree.TreeEditor(this.foldersTree, {
		ignoreNoChange : true
	});

	treeEdit.on('beforestartedit', function(editor, boundEl, value) {
		if (editor.editNode.attributes.folder_id == 0
			|| editor.editNode.attributes.mailbox == 'INBOX') {
			alert(t("You can't edit this folder", "email"));
			return false;
		}
	});

	treeEdit.on('beforecomplete', function(editor, text, value) {
		
		var mailboxs = [];
		mailboxs.push(editor.editNode.attributes.mailbox);
		
		GO.request({
			url : 'email/folder/rename',
			params : {
				account_id: editor.editNode.attributes.account_id,
				mailboxs: Ext.util.JSON.encode(mailboxs),
				name: text
			},
			fail : function() {
				this.foldersTree.getRootNode().reload();
			}
		});

	});

	GO.email.FoldersDialog.superclass.constructor.call(this, {
		layout : 'fit',
		modal : false,
		shadow : false,
		minWidth : 300,
		minHeight : 300,
		height : 400,
		width : 500,
		plain : true,
		closeAction : 'hide',
		title : t("Folders", "email"),

		items : this.foldersTree,

		tbar : [{
			iconCls : 'ic-add',
			text : t("Add"),
			handler : function() {

				var sm = this.foldersTree.getSelectionModel();
				var node = sm.getSelectedNode();

				if (!node) {
					Ext.MessageBox.alert(t("Error"),
						t("First select a folder from the tree where you want to add the new folder. Then click on 'Add'", "email"));
				} else {
					Ext.MessageBox.prompt(t("Name"),
						t("Enter the folder name:", "email"), function(button,
							text) {

							if (button == 'ok') {
								GO.request({
									url: "email/folder/create",
									maskEl: Ext.getBody(),
									params: {
										parent: node.attributes.mailbox,
										account_id: this.account_id,
										name: text
									},
									success: function(options, response, result)
									{								
										delete node.attributes.children;
										node.reload();
									},
									fail : function(){
										this.rootNode.reload();
									},
									scope: this
								});
						
							}
						}, this);
				}
			},
			scope : this
		},{
			iconCls : 'ic-delete',
			tooltip : t("Delete"),
			scope : this,
			handler : function() {
				var sm = this.foldersTree.getSelectionModel();
				var node = sm.getSelectedNode();

				if(!node|| node.attributes.folder_id<1)
				{
					Ext.MessageBox.alert(t("Error"), t("Select a folder to delete please", "email"));
				}else if(node.attributes.mailbox=='INBOX')
				{
					Ext.MessageBox.alert(t("Error"), t("You can't delete the INBOX folder", "email"));
				}else
				{
					GO.deleteItems({
						url: GO.url("email/folder/delete"),
						params: {						
							account_id:this.account_id,
							mailbox: node.attributes.mailbox
						},
						callback: function(responseParams)
						{
							if(responseParams.success)
							{
								node.remove();								
							}else
							{
								Ext.MessageBox.alert(t("Error"),responseParams.feedback);
							}
						},
						count: 1,
						scope: this
					});
				}
			}
		}, '-', {
			iconCls : 'ic-refresh',
			tooltip : t("Refresh"),
			handler : function() {
				this.rootNode.reload();
			},
			scope : this
		},
		'->' 
		,{
			iconCls : 'ic-add-circle',
			text : t("Select all"),
			handler : function() {

				var list = this.treeToMailboxList(this.rootNode);


				GO.request({
					maskEl:this.body,
					url : 'email/folder/subscribe',
					params : {
						account_id : this.account_id,
						mailboxs : Ext.util.JSON.encode(list)
					},
					success:function(){
						this.foldersTree.getRootNode().reload();
					},
					fail:function(){
						this.foldersTree.getRootNode().reload();
					},
					scope : this
				});



				this.rootNode.reload();
				
			},
			scope : this
		},{
			iconCls : 'ic-remove-circle',
			text : t("Deselect all"),
			handler : function() {
				
				var list = this.treeToMailboxList(this.rootNode);


				GO.request({
					maskEl:this.body,
					url : 'email/folder/unsubscribe',
					params : {
						account_id : this.account_id,
						mailboxs : Ext.util.JSON.encode(list)
					},
					success:function(){
						this.foldersTree.getRootNode().reload();
					},
					fail:function(){
						this.foldersTree.getRootNode().reload();
					},
					scope : this
				});



				this.rootNode.reload();
			},
			scope : this
		}

		]
	});
}

Ext.extend(GO.email.FoldersDialog, go.Window, {

	show : function(account_id) {

		this.render(Ext.getBody());

		this.account_id = account_id;
		this.foldersTree.loader.baseParams.account_id = account_id;

		if (!this.rootNode.isExpanded())
			this.rootNode.expand();
		else
			this.rootNode.reload();

		GO.email.FoldersDialog.superclass.show.call(this);

	},

	getSubscribtionData : function() {
		var data = [];
		for (var i = 0; i < this.allFoldersStore.data.items.length; i++) {
			data[i] = {
				id : this.allFoldersStore.data.items[i].get('id'),
				subscribed : this.allFoldersStore.data.items[i]
				.get('subscribed'),
				name : this.allFoldersStore.data.items[i].get('name')
			};
		}
		return data;
	},
	
	treeToMailboxList: function(node, list) {
		if(!list){
			list = [];
		}
		node.eachChild(function(subNode) {
			
			list.push(subNode.attributes.mailbox)
			this.treeToMailboxList(subNode, list);
		}, this)
		return list;
		
	}
	
});
