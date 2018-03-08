Ext.namespace('GO.ipwhitelist');



GO.moduleManager.onModuleReady('groups',function(){
	Ext.override(GO.groups.GroupDialog, {
		buildForm : GO.groups.GroupDialog.prototype.buildForm.createSequence(function(){

				this.checkboxPanel = new Ext.Panel({
					region:'north',
					height:35,
					border:false,
					cls:'go-form-panel',
					layout:'form',
					items:[
						this.enableWhitelistCheckBox = new Ext.ux.form.XCheckbox({
								name: 'enable_whitelist',
//								checked: false,
								boxLabel: GO.ipwhitelist.lang['enableWhitelist'],
								hideLabel:true
						})
					]				
				});

//				this.enableWhitelistCheckBox.on('check',function(checkbox,checked){
//					GO.request({
//						url: 'ipwhitelist/enableWhitelist/setWhiteList',
//						params: {
//							group_id : this.remoteModelId,
//							enable_whitelist : checked
//						}
//					});
//				},this);

				GO.ipwhitelist.ipGrid = new GO.ipwhitelist.WhitelistGrid({region:'center'});

				this.whitelistPanel = new Ext.Panel({
					layout:'border',
					title:GO.ipwhitelist.lang['ipWhitelist'],	
					items:[this.checkboxPanel, GO.ipwhitelist.ipGrid],
					disabled: true
				});
				
				this.whitelistPanel.on('show',function(){
					GO.ipwhitelist.ipGrid.store.load();
				}, this);
					

				this.addPanel(this.whitelistPanel);

		}),
							
		setRemoteModelId : GO.groups.GroupDialog.prototype.setRemoteModelId.createSequence(function(remoteModelId){
			this.whitelistPanel.setDisabled(!(remoteModelId>0) || !GO.settings.modules.groups.write_permission || !GO.settings.modules.ipwhitelist.write_permission);
			GO.ipwhitelist.ipGrid.setGroupId(remoteModelId);
		})
					
	});
});