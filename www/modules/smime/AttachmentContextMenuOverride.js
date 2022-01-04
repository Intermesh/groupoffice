/* global GO, Ext */

GO.moduleManager.onModuleReady('email',function(){
	Ext.override(GO.email.AttachmentContextMenu, {
		
		initComponent : GO.email.AttachmentContextMenu.prototype.initComponent.createSequence(function(){
			this.smimeAddItem = new Ext.menu.Item({
					iconCls: 'ic-add',
					text: t("Add to SMIME certificates", "smime"),
					handler: function(){
						this.importAttachment(this.attachment,this.messagePanel);
					},
					scope: this
				});
			this.items.add(this.smimeAddItem);
		}),
		
		showAt : GO.email.AttachmentContextMenu.prototype.showAt.createSequence(function(xy, attachment){
			var bIsVisible = (Ext.isDefined(attachment) && attachment.extension === 'cer');
			this.smimeAddItem.setVisible(bIsVisible);
			this.doLayout();
		}),
		
		importAttachment: function(attachment,panel) {
			//account_id mailbox uid number encoding sender
			GO.request({
				url: 'smime/publicCertificate/importAttachment',
				params:{
					uid: panel.uid,
					mailbox: panel.mailbox,
					number: attachment.number,
					encoding: attachment.encoding,
					account_id: panel.account_id,
					sender:panel.data.sender
				},
				success: function(options, response, result) {
					if(result.success){
						Ext.MessageBox.alert(t('Certificate added'), t('SMIME certificate was added for')+' '+panel.data.sender);
					} else {
						Ext.MessageBox.alert(t('Error'), t('Could not add SMIME certificate for')+' '+panel.data.sender);
					}
				},
				scope:this
			});
		}
	});
});