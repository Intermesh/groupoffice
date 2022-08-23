/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AttachmentContextMenu.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.AttachmentContextMenu = function(config)
{
	if(!config)
		config = {};

	config['shadow']='frame';
	config['minWidth']=180;
	
	this.downloadButton = new Ext.menu.Item({
					iconCls: 'ic-file-download',
					text: t("Download"),
					handler: function(){	
									
						GO.email.openAttachment(
							this.attachment,
							this.messagePanel,
							true);
					},
					scope: this
				});
	config.items=[this.downloadButton];
	if(GO.files && !config.removeSaveButton)
	{
		this.openWithButton = new Ext.menu.Item({
			iconCls: 'ic-launch',
			text: t("Open with", "files"),
			handler: function(){
				GO.files.openEmailAttachment(this.attachment,this.messagePanel, true);
			},
			scope: this
		});
		config.items.push(this.openWithButton);


		this.saveButton = new Ext.menu.Item({
					iconCls: 'ic-save',
					text: t("Save to personal folder", "email"),
					handler: function(){
						GO.email.saveAttachment(this.attachment,this.messagePanel);
					},
					scope: this
				});
		config.items.push(this.saveButton);
	
		// Save to item button.
		// Shows the link dialog so you can select an item to add the attachment to.
		this.saveToItemButton = new Ext.menu.Item({
			iconCls: 'ic-save',
			text: t("Save to item", "email"),
			handler: function(){

				var dlg = new GO.email.LinkAttachmentDialog();				

				dlg.show(this.attachment,this.messagePanel);
			},
			scope: this
		});
		config.items.push(this.saveToItemButton);
	}


	config.items.push(this.copyImageButton = new Ext.menu.Item({
		iconCls: 'ic-content-copy',
		text: t("Copy image"),
		handler: function() {
			this.copyToClipboard();
		},
		scope: this
	}));
	
	GO.email.AttachmentContextMenu.superclass.constructor.call(this, config);	
}

Ext.extend(GO.email.AttachmentContextMenu, Ext.menu.Menu,{
	attachment : false,

	copyToClipboard: async function() {
		try {
			let canvas = document.createElement('canvas');
			canvas.width = this.img.clientWidth;
			canvas.height = this.img.clientHeight;
			let context = canvas.getContext('2d');
			context.drawImage(this.img, 0, 0);

			const blob = await canvas.toBlob((blob) => {
				let data = [new ClipboardItem({[blob.type]: blob})];

				if (navigator.clipboard) {
					navigator.clipboard.write(data).then(function () {
						console.log('done')
					}, function (err) {
						console.log('error')
					});
				} else {
					console.log('Browser do not support Clipboard API')
				}
			});
		} catch (e) {
			console.error(e);
		}

	},

	showAt : function(xy, attachment, img)
	{ 	
		this.attachment = attachment;
		this.img = img;

		this.copyImageButton.setVisible(!!this.img);


		GO.email.AttachmentContextMenu.superclass.showAt.call(this, xy);
	}	
});
