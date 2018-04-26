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
				this.records[0].data.handler.call(this);
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
				window.open(GO.url("files/file/download",{id:this.records[0].data.id,inline:false}));
			},
			scope: this
		}),
		'-',
		this.btnMakeCopy = new Ext.menu.Item({
			iconCls: 'ic-content-copy',
			text: t("Make copy"),
			handler: function(){
				this.fireEvent('copy', this, this.records);
			},
			scope: this
		}),
		this.btnMoveTo = new Ext.menu.Item({
			iconCls: 'ic-forward',
			text: t("Move to")+'&hellip;',
			handler: function(){
				this.fireEvent('delete', this, this.records);
			},
			scope: this
		}),
		this.btnRename = new Ext.menu.Item({
			iconCls: 'ic-border-color',
			text: t("Rename"),
			handler: function(){
				this.fireEvent('batchEdit', this, this.records);
			},
			scope: this
		}),
		this.btnDelete = new Ext.menu.Item({
			iconCls: 'ic-delete',
			text: t("Delete"),
			handler: function(){
				this.fireEvent('delete', this, this.records);
			},
			scope: this
		}),
		'-',
		this.btnShare = new Ext.menu.Item({
			iconCls: 'ic-person-add',
			text: t("Share")+'&hellip;',
			handler: function(){
				this.fireEvent('share', this, this.records);
			},
			scope: this
		}),
		this.btnEmail = new Ext.menu.Item({
			iconCls: 'ic-email',
			text: t("Email files"),
			handler: function(){
				this.fireEvent('email', this, this.records);
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

		this.addEvents({
			open: true,
			delete: true,
			email: true,
			bookmark: true,
			download: true
		});

	},
	showAt : function(xy, records) {
		this.records = records;
		
		if (records.length > 1) {

			this.btnDownload.hide();
			this.btnOpenWith.hide();
			this.btnOpen.hide();
			this.btnEmail.show();
			for(var r in records) {
				if(records[r].data.isDirectory) {
					this.btnEmail.hide();
					break;
				}
			}

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
				return
			}
			
			var locked = Ext.isEmpty(this.records[0].data.lockedBy);
			this.btnLock.hide(!locked);
			this.btnUnlock.hide(locked);
			
			this.btnDownload.show();
			this.btnOpen.show();
			this.btnOpenWith.show();
			this.btnDownload.show();
			this.btnRename();
			this.btnEmail.show();
			this.btnBookmark.hide();
			
			
			switch(records[0].data.type) {
				//todo
			}
		}

		go.modules.community.files.ContextMenu.superclass.showAt.call(this, xy);
	}
});
