GO.moduleManager.onModuleReady('calendar',function(){
	Ext.override(GO.calendar.SettingsPanel, {
		initComponent : GO.calendar.SettingsPanel.prototype.initComponent.createSequence(function(){
			this.freeBusyFieldSet = new Ext.form.FieldSet({
				title:t("Accessrights for free busy info", "freebusypermissions"),
				closable:true,
				closeAction:'hide',
				items:[{
					xtype:'htmlcomponent',
					html:t("Grant access to users and and groups to allow access to your free busy information. These users will also be able to schedule tentative events directly into your calendar.", "freebusypermissions"),
					style:'padding:4px 0'
				},
				this.freebusyPermissionsPanel = new GO.grid.PermissionsPanel({
					hideLevel:true,
					height:200
				})
				]
			})

			this.freebusyPermissionsPanel.border=false;
			
			this.add(this.freeBusyFieldSet);
			
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
		//				text:t("Accessrights for free busy info", "freebusypermissions")
		//			});			
		}),
		
		onLoadComplete : GO.calendar.SettingsPanel.prototype.onLoadComplete.createSequence(function(action){
			this.freebusyPermissionsPanel.setAcl(action.freebusySettings.acl_id);
		})
		
	});
});
