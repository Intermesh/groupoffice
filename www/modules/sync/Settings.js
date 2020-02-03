/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Settings.js 23425 2018-02-13 09:48:05Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
GO.sync.SettingsPanel = Ext.extend(Ext.Panel,{
	
	autoScroll:true,
	title : t("Synchronization", "sync"),
	iconCls: 'ic-sync',
	defaultType: 'textfield',
	layout: "form",
	defaults: {
		anchor: "100%"
	},


	onLoadStart: function (userId) {
		if(this.panelTasklist)
			this.panelTasklist.setModelId(userId);

		if(this.panelCalendar)
			this.panelCalendar.setModelId(userId);
	},
	
	initComponent: function() {
		
		this.items = [];

		if(go.Modules.isAvailable("legacy", "email")) {
			this.items.push(new Ext.form.FieldSet({
				title:'E-mail',
				labelWidth: 170,
				items: [
					this.selectAccount = new GO.email.SelectAccount({
						hidden: (!GO.settings.modules.email || !GO.settings.modules.email.read_permission),
						hiddenName:'syncSettings.account_id'
					})
				]}));

			//Only writable accounts can be used for sync
			this.selectAccount.store.baseParams.permissionLevel = go.permissionLevels.write;
		}

		var syncComponents = {calendar: 'Calendar',tasks: 'Tasklist'};
		
		for(var i in syncComponents) {
			var module = i,
				name = syncComponents[i],
				id = name.toLowerCase();			
			
			if(go.Modules.isAvailable("legacy", module))
			{
				var defaultCol = new GO.grid.RadioColumn({
					header: t("Default", "sync"),
					dataIndex: 'default_'+id,
					width: 22,
					isDisabled:function(record){
						return record.get('permission_level')<GO.permissionLevels.writeAndDelete;
					}
				});

				this['panel'+name] = new GO.base.model.multiselect.panel({

					autoLoadStore: false,
					deleteDefaultCol: 'default_'+id,
					deleteSelected : this.checkDefaultSelected,
					autoHeight:true,
					paging: false,
					autoExpandColumn:'name',
					url:'sync/user'+name,
					columns:[{
							header: t("Name"), 
							dataIndex: 'name', 
							sortable: true,
							id:'name'
						},
						defaultCol
					],
					plugins: [defaultCol],
					selectColumns:[{
						header: t("Name"), 
						dataIndex: 'name', 
						sortable: true
					}],
					fields:['id','name','default_'+id,'permission_level'],
					model_id: GO.settings.user_id,
					title: t("name", module)					
				});
//				this['panel'+name].getTopToolbar().insert(0,"->");
//				this['panel'+name].getTopToolbar().insert(0,t("name", module));
				
				this.items.push(this['panel'+name]);
			}
		}


		if(go.Modules.isAvailable("community", "notes"))
		{
			var defaultCol = new GO.grid.RadioColumn({
					header: t("Default", "sync"),
					dataIndex: 'isDefault',
					width: dp(104)
				});
				
			this.noteBookSelect = new go.form.multiselect.Field({
				name: "syncSettings.noteBooks",
				idField: "noteBookId",
				displayField: "name",
				entityStore: "NoteBook",
				hideLabel: true,
				title: t("Notebooks", "community", "notes"),
				extraColumns: [defaultCol],
				extraFields: [{name: "isDefault", type: "boolean"}],
				plugins: [defaultCol]
			});
			
			this.items.push(this.noteBookSelect);
		}
		
		if(go.Modules.isAvailable("community", "addressbook"))
		{
			var defaultCol = new GO.grid.RadioColumn({
					header: t("Default", "sync"),
					dataIndex: 'isDefault',
					width: dp(104)
				});
				
			this.addressBookSelect = new go.form.multiselect.Field({
				name: "syncSettings.addressBooks",
				idField: "addressBookId",
				displayField: "name",
				entityStore: "AddressBook",
				hideLabel: true,
				title: t("Address books", "community", "addressbook"),
				extraColumns: [defaultCol],
				extraFields: [{name: "isDefault", type: "boolean"}],
				plugins: [defaultCol]
			});
			
			this.items.push(this.addressBookSelect);
		}
	
		this.on('show',function(){
			if(this.panelAddressbook)
				this.panelAddressbook.store.load();

			if(this.panelTasklist)
				this.panelTasklist.store.load();
		
			if(this.panelCalendar)
				this.panelCalendar.store.load();


		},this);
		
		GO.sync.SettingsPanel.superclass.initComponent.call(this);
	},
	
	checkDefaultSelected : function(){
		var defaultFound = false,
			records = this.selModel.getSelections();
		for (var i=0;i<this.selModel.selections.keys.length;i++) {
			if(records[i].data[this.deleteDefaultCol] == 1 && !defaultFound){
				defaultFound = true;
				break;
			}
		}

		if(!defaultFound){
			return GO.base.model.multiselect.panel.superclass.deleteSelected.call(this);
		}
		alert(t("Can't delete the default item."));
	}

});			

go.Modules.register("legacy", 'sync', {
	userSettingsPanels: ["GO.sync.SettingsPanel"]
});	

