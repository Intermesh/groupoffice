/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AllAttachmentContextMenu.js 20914 2017-03-07 13:05:13Z wsmits $
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
			text: GO.email.lang.downloadAllAsZip,
			cls: 'x-btn-text-icon',
			handler: function(){						
				window.open(this.allZipFileUrl);
			},
			scope: this
		});
				
		mnuItems.push(this.downloadButton);
		
		if(GO.files){
			this.saveButton = new Ext.menu.Item({
				iconCls: 'btn-save',
				text: GO.email.lang.saveAllToPersonal,
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
				text: GO.email.lang.saveAllToItem,
				cls: 'x-btn-text-icon',
				handler: function(){

					if(!GO.email.linkAttachmentDialog){
						GO.email.linkAttachmentDialog = new GO.email.LinkAttachmentDialog();
					}

					GO.email.linkAttachmentDialog.show(null,this.messagePanel);
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