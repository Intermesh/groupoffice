GO.moduleManager.onModuleReady('calendar',function(){
	Ext.override(GO.calendar.SettingsPanel, {
		initComponent : GO.calendar.SettingsPanel.prototype.initComponent.createInterceptor(function(){
			this.freeBusyFieldSet = new Ext.form.FieldSet({
				title:GO.freebusypermissions.lang.FreeBusyaccessrights,
				closable:true,
				closeAction:'hide',
				items:[{
					xtype:'htmlcomponent',
					html:GO.freebusypermissions.lang.info,
					style:'padding:4px 0'
				},
				this.freebusyPermissionsPanel = new GO.grid.PermissionsPanel({
					hideLevel:true,
					height:200
				})
				]
			})

			this.freebusyPermissionsPanel.border=false;
			
			this.items.push(this.freeBusyFieldSet);
			
			this.on('show',function(){
				this.freebusyPermissionsPanel.loadAcl();
			});

		//			this.items.push({
		//				xtype:'button',
		//				handler:function(){
		//					//this.freebusyPermissionsPanel.setUserId(	);
		//					this.freeBusyWindow.show();
		//					//this.freebusyPermissionsPanel.loadAcl();
		//				},
		//				scope:this,
		//				text:GO.freebusypermissions.lang.FreeBusyaccessrights
		//			});			
		}),
		
		onLoadSettings : GO.calendar.SettingsPanel.prototype.onLoadSettings.createSequence(function(action){
			this.freebusyPermissionsPanel.setAcl(action.result.data.freebusypermissions_acl_id);
		})
		
	});
});