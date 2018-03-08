/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author WilmarVB <wilmar@intermesh.nl>
 */

GO.email.FolderDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
		
	initComponent : function(){
		
		Ext.apply(this, {
			title:GO.email.lang.mailbox,
			titleField: 'mailboxPath',
//			goDialogId:'note',
			height: 160,
			formControllerUrl: 'email/folder'
		});
		
		GO.email.FolderDialog.superclass.initComponent.call(this);
		
	},
	
	beforeLoad : function(remoteModelId,config) {
		this.hiddenAccountIdField.setValue(remoteModelId);
		this.hiddenMailboxPathField.setValue(config.mailboxPath);
		config.loadParams = {
			accountId : remoteModelId,
			mailboxPath : config.mailboxPath
		}
	},
	
	buildForm : function() {
		this.addPanel(new Ext.Panel({
			layout: 'form',
			cls:'go-form-panel',
			items: [this.hiddenAccountIdField = new Ext.form.TextField({
				name : 'accountId',
				hidden: true
			}), this.hiddenMailboxPathField = new Ext.form.TextField({
				name : 'mailboxPath',
				hidden: true
			}), this.checkUnseenField = new Ext.form.Checkbox({
				name : 'checkUnseen',
				boxLabel: GO.email.lang.checkUnseen,
				allowBlank: true,
				hideLabel:true
			})]
		}));
	}

});
