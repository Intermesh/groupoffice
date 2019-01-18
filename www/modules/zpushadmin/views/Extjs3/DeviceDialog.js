GO.zpushadmin.DeviceDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'zpushadmindevice',
			title:t("Device", "zpushadmin"),
			formControllerUrl: 'zpushadmin/device',
			height:dp(500),
			width: dp(500),
			helppage:'Z-push_admin_user_manual#Device_dialog'
		});
		
		GO.zpushadmin.DeviceDialog.superclass.initComponent.call(this);	
	},
	  
	buildForm : function () {
		
		this.deviceIDTextField = new Ext.form.TextField({
			name: 'device_id',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			disabled:true,
			fieldLabel: t("Device", "zpushadmin")
		});
		
		this.deviceTypeTextField = new Ext.form.TextField({
			name: 'device_type',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			disabled:true,
			fieldLabel: t("Device Type", "zpushadmin")
		});
		
		this.resyncButton = new Ext.Button({
			text : t("Resync Device", "zpushadmin"),
			handler : function() {
				Ext.Msg.show({
					title: t("Resync Device", "zpushadmin"),
					icon: Ext.MessageBox.WARNING,
					msg: t("Do you really want to resynchronize ALL data on the device?", "zpushadmin"),
					buttons: Ext.Msg.YESNO,
					scope:this,
					fn: function(btn) {
						if (btn=='yes') {
							GO.request({
								maskEl:Ext.getBody(),
								url:'zpushadmin/admin/resyncDevice',
								params:{
									deviceId:this.loadData.device_id,
									username:this.loadData.username
								},
								scope:this
							});
						}
					}
				})
			},
			scope : this
		});
		
//		this.wipeDeviceButton = new Ext.Button({
//			text : t("Wipe this device", "zpushadmin"),
//			handler : function() {
//				Ext.Msg.show({
//					title: t("Wipe this device", "zpushadmin"),
//					icon: Ext.MessageBox.WARNING,
//					msg: t("This action will RESET the device to the FACTORY settings and will delete ALL data on the device!! Do you really want to wipe the device?", "zpushadmin"),
//					buttons: Ext.Msg.YESNO,
//					scope:this,
//					fn: function(btn) {
//						if (btn=='yes') {
//							GO.request({
//								maskEl:Ext.getBody(),
//								url:'zpushadmin/admin/wipe',
//								params:{
//									deviceId:this.loadData.device_id,
//									username:this.loadData.username
//								},
//								scope:this
//							});
//						}
//					}
//				})
//			},
//			scope : this
//		});
		
		this.canConnectCheckbox = new Ext.ux.form.XCheckbox({
			name: 'can_connect',
			width:300,
			anchor: '99%',
			maxLength: 100,
//			allowBlank:false,
			fieldLabel: t("Can connect", "zpushadmin")
		});
		
		this.commentsTextArea = new Ext.form.TextArea({
			name: 'comment',
			width:300,
			anchor: '99%',
			allowBlank:true,
			disabled:false,
			fieldLabel: t("Comments", "zpushadmin")
		});
			
			
		this.buttonContainer = new Ext.Container({
			layout: {
				type: 'hbox',
				padding: '20px'
			},
			items: [
				this.resyncButton
//				,
//				this.wipeDeviceButton
			]
		});	
		
		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),			
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.deviceIDTextField,
				this.deviceTypeTextField,
				this.canConnectCheckbox,
				this.commentsTextArea,
				this.buttonContainer
      ]				
		});

    this.addPanel(this.propertiesPanel);
	},
	afterLoad : function(remoteModelId, config, action){
		
//		this.resyncButton.setDisabled(!action.result.zpushAdminFound);
//		//this.wipeDeviceButton.setDisabled(!action.result.zpushAdminFound);
	}
});
