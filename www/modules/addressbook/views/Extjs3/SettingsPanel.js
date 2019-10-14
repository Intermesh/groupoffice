GO.addressbook.ManagePanel = Ext.extend(Ext.Panel, {
		initComponent : function(){

		this.tabPanel = new Ext.TabPanel({
			activeTab:0,
			items: [
				this.addressbooksGrid = new GO.addressbook.ManageAddressbooksGrid(),
				this.templatesGrid = new GO.addressbook.TemplatesGrid(),
				this.addresslistsGrid = new GO.addressbook.AddresslistsGrid()
			]
		});

		Ext.apply(this, {
			title:t("Manage", "addressbook"),
			formControllerUrl: 'addressbook/settings',
			enableOkButton : false,
			enableApplyButton : false,
			layout:'fit',
			
			items: [this.tabPanel]
		});
		
		GO.addressbook.ManagePanel.superclass.initComponent.call(this);	
		
		if(GO.settings.has_admin_permission){
			this.exportPermissionsTab = new GO.grid.PermissionsPanel({
				title:t("Export permissions", "addressbook"),
				hideLevel:true
			});
			this.tabPanel.add(this.exportPermissionsTab);
		}
	},
	
	show : function(){
		if(!this.rendered){
			this.render(Ext.getBody());
		}

		if(GO.settings.has_admin_permission){
			this.exportPermissionsTab.setAcl(GO.addressbook.export_acl_id);
		}
		
		GO.addressbook.ManagePanel.superclass.show.call(this);
	}
});
