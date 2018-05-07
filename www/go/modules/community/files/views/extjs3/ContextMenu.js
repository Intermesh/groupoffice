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
			this.topSeparator = new Ext.menu.Separator(),
			this.btnMakeCopy = new Ext.menu.Item({
				iconCls: 'ic-content-copy',
				text: t("Make copy"),
				handler: function(){
					console.log(this.records);
					go.modules.community.files.move(this.records,true);
				},
				scope: this
			}),
			this.btnMoveTo = new Ext.menu.Item({
				iconCls: 'ic-forward',
				text: t("Move to")+'&hellip;',
				handler: function(){
					
					console.log(this.records);
					go.modules.community.files.move(this.records,false);
					
					if(this.records && this.records.length === 1){ // Single select
						var moveDialog = new go.modules.community.files.MoveDialog();
						moveDialog.setTitle(t("Move")+ " " +this.records[0].name);
						moveDialog.load(this.records[0].id).show();
					}
				},
				scope: this
			}),
			this.btnSearchInFolder = new Ext.menu.Item({
				iconCls: 'ic-search',
				text: t("Search in this folder"),
				handler: function() {
					if(this.records && this.records.length === 1){ // Single select
						console.log(t("Search in this folder"));
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
			this.middleSeparator = new Ext.menu.Separator(),
			this.btnShare = new Ext.menu.Item({
				iconCls: 'ic-person-add',
				text: t("Share")+'&hellip;',
				handler: function(){
					if(this.records && this.records.length === 1){ // Single select
						var shareDialog = new go.modules.community.files.ShareDialog();
						shareDialog.setTitle(t("Share")+ " " +this.records[0].name);
						shareDialog.setAcl(this.records[0].aclId); // TODO: find other way to set
						shareDialog.load(this.records[0].id).show();
					}
				},
				scope: this
			}),
			this.btnEmail = new Ext.menu.Item({
				iconCls: 'ic-email',
				text: t("Email files"),
				handler: function(){
					console.log(this.records);
					go.modules.community.files.email(this.records);
				},
				scope: this
			}),
			this.bottomSeparator = new Ext.menu.Separator(),
			this.btnLock = new Ext.menu.Item({
				iconCls: 'ic-lock-outline',
				text: t("Lock"),
				hidden: true,
				handler: function(){
					console.log(this.records);
					go.modules.community.files.lock(this.records);
				},
				scope:this
			}),
			this.btnUnlock = new Ext.menu.Item({
				iconCls: 'ic-lock-open',
				text: t("Unlock"),
				handler: function(){
					console.log(this.records);
					go.modules.community.files.lock(this.records);
				},
				scope:this
			}),
			this.btnBookmark = new Ext.menu.Item({
				iconCls: 'ic-bookmark',
				text: t("Bookmark"),
				handler: function(){
					console.log(this.records);
//					var data = {};
//					for(var i=0; i< this.records.length; i++){
//						data.push({id: this.records[i].id)
//					}
//					
//					go.modules.community.files.bookmark(this.records);
				},
				scope:this
			}),
			this.btnRemoveBookmark = new Ext.menu.Item({
				iconCls: 'ic-bookmark',
				text: t("Remove Bookmark"),
				handler: function(){
					console.log(this.records);
					
					
					go.modules.community.files.bookmark(this.records);
				},
				scope:this
			})
		];

		go.modules.community.files.ContextMenu.superclass.initComponent.call(this, arguments);
	},
	
	setRecords : function(records){
		this.records = records;
		
		// Hide all items, the neccesary items will be showed again below
		this.items.each(function(button)  {
			button.hide();
		});
		
		// Multiple records selected
		if (records.length > 1) {
			
			this.btnEmail.show();
			Ext.each(records, function(r) {
				if(r.data.isDirectory) {
					this.btnEmail.hide();
					return;
				}
			}, this);
			
			return;
		}
		
		// Only 1 record selected
		if (records.length === 1) {
					
			// If it's a directory
			if(records[0].isDirectory) {
				this.btnMakeCopy.show();
				this.btnMoveTo.show();
				this.btnSearchInFolder.show();
				this.btnRename.show();
				this.btnDelete.show();
				
				this.middleSeparator.show();
				
				this.btnShare.show();
				
				this.bottomSeparator.show();
				
				var bookmarked = this.records[0].bookmarked;
				this.btnBookmark.setVisible(!bookmarked);
				this.btnRemoveBookmark.setVisible(bookmarked);
				
				return;
			}
			
			// Here it's a file
			this.btnOpen.show();
			this.btnOpenWith.show();
			this.btnDownload.show();
			
			this.topSeparator.show();

			this.btnMakeCopy.show();
			this.btnMoveTo.show();
			this.btnRename.show();
			this.btnDelete.show();
			
			this.middleSeparator.show();
			
			this.btnEmail.show();
			
			this.bottomSeparator.show();

			var locked = Ext.isEmpty(this.records[0].lockedBy);
			this.btnLock.hide(!locked);
			this.btnUnlock.hide(locked);

			var bookmarked = this.records[0].bookmarked;
			this.btnBookmark.setVisible(!bookmarked);
			this.btnRemoveBookmark.setVisible(bookmarked);
			
			switch(records[0].type) {
				//todo
			}
		}
		
	},
	
	showAt : function(xy, records) {
		
		if(records){
			this.setRecords(records);
		}
		
		go.modules.community.files.ContextMenu.superclass.showAt.call(this, xy);
	}
});
