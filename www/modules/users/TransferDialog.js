GO.users.TransferDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){


		Ext.apply(this, {
			loadOnNewModel: false,
			goDialogId:'usertransferdialog',
			title: 'Transfer Userdata',
			formControllerUrl: 'users/user',
			createAction : 'transfer',
			layout: 'fit',
			height:230,
			//enableOkButton: GO.fixdossiers.isManager,
			enableApplyButton: false,
			width:400,
			jsonPost: true
		});
		
		GO.users.TransferDialog.superclass.initComponent.call(this);	
	},
	  
	buildForm : function () {
		this.transferPanel = this.buildTransferPanel();
		this.addPanel(this.transferPanel);
	},
	
	buildTransferPanel : function() {
		
		return new Ext.Panel({
			title:'Select users',			
			cls:'go-form-panel',
			layout:'form',
			width: '100%',
			items:[
				{
					xtype:'displayfield',
					html: 'Select 2 user accounts to transfer data from one account to the other'
				},
				new GO.form.SelectUser({
					hiddenName: 'transfer.id_from'
				}),
				new GO.form.SelectUser({
					hiddenName: 'transfer.id_to'
				})
			]				
		});
	}
	
});