/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ContextMenu.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

go.modules.community.files.ContextMenu = Ext.extend(Ext.menu.Menu,{

	minwidth: 180,
	records : [],

	initComponent: function() {

		this.items = [
		this.btnOpen = new Ext.menu.Item({
			text: t("Open"),
			iconCls: 'ic-open-in-new',
			handler: function(){
				if(!this.records[0])
					return;
				go.Preview(this.records[0].json);
			},
			scope: this
		}),
		this.btnOpenWith = new Ext.menu.Item({
			text: t("Open with"),
			iconCls: 'ic-open-in-new',
			handler: function() {
				GO.files.openFile({id:this.records[0].data.id});
			},
			scope: this
		}),
		this.btnDownload = new Ext.menu.Item({
			iconCls: 'ic-file-download',
			text: t("Download"),
			handler: function(){
				window.open(go.Jmap.downloadUrl(this.records[0].json.blobId));
			},
			scope: this
		}),
		'-',
		this.btnMakeCopy = new Ext.menu.Item({
			iconCls: 'ic-content-copy',
			text: t("Make copy"),
			handler: function(){

			},
			scope: this
		}),
		this.btnMoveTo = new Ext.menu.Item({
			iconCls: 'ic-forward',
			text: t("Move to")+'&hellip;',
			handler: function(){
				if(this.records && this.records.length === 1){ // Single select
					var moveDialog = new go.modules.community.files.MoveDialog();
					moveDialog.setTitle(t("Move")+ " " +this.records[0].data.name);
					moveDialog.load(this.records[0].id).show();
				}
			},
			scope: this
		}),
		this.btnRename = new Ext.menu.Item({
			iconCls: 'ic-border-color',
			text: t("Rename"),
			handler: function(){
				if(this.records && this.records.length === 1){ // Single select
					var nodeDialog = new go.modules.community.files.NodeDialog();
					nodeDialog.setTitle(t("Rename")+ " " +this.records[0].data.name);
					nodeDialog.load(this.records[0].id).show();
				}
			},
			scope: this
		}),
		this.btnDelete = new Ext.menu.Item({
			iconCls: 'ic-delete',
			text: t("Delete"),
			handler: function(){
				if(this.records && this.records.length === 1){ // Single select
					go.Stores.get("Node").set({destroy: [this.records[0].id]}, function (options, success, response) {
						if (response.destroyed) {
							// success
						}
					}, this);
				}
			},
			scope: this
		}),
		'-',
		this.btnShare = new Ext.menu.Item({
			iconCls: 'ic-person-add',
			text: t("Share")+'&hellip;',
			handler: function(){
				if(this.records && this.records.length === 1){ // Single select
					var shareDialog = new go.modules.community.files.ShareDialog();
					shareDialog.setTitle(t("Share")+ " " +this.records[0].data.name);
					shareDialog.setAcl(this.records[0].data.aclId); // TODO: find other way to set
					shareDialog.load(this.records[0].id).show();
				}
			},
			scope: this
		}),
		this.btnEmail = new Ext.menu.Item({
			iconCls: 'ic-email',
			text: t("Email files"),
			handler: function(){
				
			},
			scope: this
		}),
		'-',
		this.btnLock = new Ext.menu.Item({
			iconCls: 'ic-lock-outline',
			text: t("Lock"),
			hidden: true,
			handler: function(){

			},
			scope:this
		}),
		this.btnUnlock = new Ext.menu.Item({
			iconCls: 'ic-lock-open',
			text: t("Unlock"),
			handler: function(){

			},
			scope:this
		}),
		this.btnBookmark = new Ext.menu.Item({
			iconCls: 'ic-bookmark',
			text: t("Bookmark"),
			handler: function(){

			},
			scope:this
		})
		];

		go.modules.community.files.ContextMenu.superclass.initComponent.call(this, arguments);
	},
	showAt : function(xy, records) {
		this.records = records;
		
		if (records.length > 1) {

			this.btnDownload.hide();
			this.btnOpenWith.hide();
			this.btnOpen.hide();
			this.btnEmail.show();
			Ext.each(records, function(r) {
				if(r.data.isDirectory) {
					this.btnEmail.hide();
					return;
				}
			}, this);

		}
		if (records.length === 1) {
			
			if(records[0].isDirectory) {
				this.btnLock.hide();
				this.btnUnlock.hide();
				this.btnDownload.hide();
				this.btnOpenWith.hide();
				this.btnOpen.hide();
				this.btnRename.hide();
				this.btnEmail.hide();
				this.btnBookmark.show();
				return;
			}
			
			var locked = Ext.isEmpty(this.records[0].data.lockedBy);
			this.btnLock.hide(!locked);
			this.btnUnlock.hide(locked);
			
			this.btnDownload.show();
			this.btnOpen.show();
			this.btnOpenWith.show();
			this.btnDownload.show();
			this.btnRename.show();
			this.btnEmail.show();
			this.btnBookmark.hide();

			switch(records[0].data.type) {
				//todo
			}
		}

		go.modules.community.files.ContextMenu.superclass.showAt.call(this, xy);
	}
});
