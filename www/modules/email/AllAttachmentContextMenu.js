/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AllAttachmentContextMenu.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.email.AllAttachmentContextMenu = Ext.extend(Ext.menu.Menu, {
	
	messagePanel:false,
	allZipFileUrl:false,
	
	initComponent : function(){
		
		var mnuItems = [];
		
		this.downloadButton = new Ext.menu.Item({
			iconCls: 'btn-download',
			text: t("Download all as zipfile", "email"),
			cls: 'x-btn-text-icon',
			handler: function(){						
				window.open(this.allZipFileUrl);
			},
			scope: this
		});
				
		mnuItems.push(this.downloadButton);
		
		if(go.Modules.isAvailable("legacy", "files")){
			this.saveButton = new Ext.menu.Item({
				iconCls: 'btn-save',
				text: t("Save all to personal folder", "email"),
				cls: 'x-btn-text-icon',
				handler: function(){
					GO.email.saveAllAttachments(this.messagePanel);
				},
				scope: this
			});
			
			mnuItems.push(this.saveButton);

			// Save to item button.
			// Shows the link dialog so you can select an item to add the attachment to.
			this.saveToItemButton = new Ext.menu.Item({
				iconCls: 'btn-save',
				text: t("Save all to item", "email"),
				cls: 'x-btn-text-icon',
				handler: function(){

					var dlg = new GO.email.LinkAttachmentDialog();
					dlg.show(null,this.messagePanel);
				},
				scope: this
			});
			
			mnuItems.push(this.saveToItemButton);
		}
		
		Ext.apply(this, {
			shadow:'frame',
			minWidth:180,
			items: mnuItems			
		});
		
		GO.email.AllAttachmentContextMenu.superclass.initComponent.call(this);	
	},
	
	showAt : function(xy) {
		
		// Reset the value, so we don't get the url of the previous email
		this.allZipFileUrl = false;
		
		if(this.messagePanel){
			// Check if there is a "zip_of_attachments_url" given, if so then enable the downloadButton and set the url
			this.allZipFileUrl = this.messagePanel.data.zip_of_attachments_url;
			this.downloadButton.setVisible(!GO.util.empty(this.allZipFileUrl));
		}
		
		GO.email.AllAttachmentContextMenu.superclass.showAt.call(this,xy);
	}
	
});
