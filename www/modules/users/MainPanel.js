/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 22445 2018-03-06 08:36:59Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Boy Wijnmaalen <bwijnmaalen@intermesh.nl>
 */


GO.users.MainPanel = function(config)

{	
	if(!config)
	{
		config = {};
	}


	
	config.layout='border';
	config.border=false;
	
	this.usersGridPanel = new GO.users.UsersGrid({'region':'center',tbar:[]});
	this.groupsGrid = new GO.users.GroupsGrid({
		relatedStore: this.usersGridPanel.store,
		region:'west',
		cls: 'go-sidenav',
		width:dp(224),
		id:'users-groups-panel'
	});
	
	this.usersGridPanel.getTopToolbar().add([
		  	{
		  		iconCls: 'ic-add', 
		  		text: t("Add"),  
		  		handler: function(){
		  			//if(GO.settings.config.max_users > 0 && this.usersGridPanel.store.totalLength >= GO.settings.config.max_users)
		  			//{
					// THIS is now check serverside because we can only check the enabled users
		  			//	Ext.Msg.alert(t("Error"), t("The maximum number of users has been reached. Contact your hosting provider to extend your maximum number of users.", "users"));
		  			//}else
		  			//{
		  				GO.users.showUserDialog();
		  			//}
		  		}, 
		  		scope: this
		  	},
		  	{
		  		iconCls: 'ic-delete', 
		  		tooltip: t("Delete"),
		  		handler: function(){
						this.usersGridPanel.deleteSelected();
					},
		  		scope: this
		  	},'-',{
				iconCls:'ic-settings',
				tooltip:t("Administration"),
				handler:function(){
					if(!this.settingsDialog)
					{
						this.settingsDialog = new GO.users.SettingsDialog();
					}
					this.settingsDialog.show();
				},
				scope:this
			},{
		  		iconCls: 'ic-file-upload',
		  		text:t("Import"),
		  		handler:function(){
		  			if(!this.importDialog)
		  			{
		  				this.importDialog = new GO.users.ImportDialog();
		  				this.importDialog.on('import', function(){this.usersGridPanel.store.reload();}, this);
		  			}
		  			this.importDialog.show();
		  		},
		  		scope:this		  		
		  	},
			this.exportMenu = new GO.base.ExportMenu({className:'GO\\Users\\Export\\CurrentGrid'}),
			{
				iconCls: 'ic-compare-arrows',
				text: t("Transfer data", "users"),
				handler:function(){
					if(!this.transferDialog)
					{
						this.transferDialog = new GO.users.TransferDialog();
					}
					this.transferDialog.show();
				},
				scope:this
			},
//			{
//				enableToggle:true,
//				text:t("Show pro users", "users"),
//				toggleHandler:function(btn, pressed){
//					this.store.baseParams.show_licensed=pressed ? 1 : 0;
//					this.store.load();
//				},
//				scope:this
//			},
			'->',
			  this.searchField = new go.toolbar.SearchButton({
					store: this.usersGridPanel.store
			  })
		 ]);
			
	this.exportMenu.setColumnModel(this.usersGridPanel.getColumnModel());
	
	config.items= [
		this.groupsGrid,
		this.usersGridPanel
	];
	
	GO.users.MainPanel.superclass.constructor.call(this, config);

};

Ext.extend(GO.users.MainPanel, Ext.Panel,{
	show : function() {
		
		GO.users.MainPanel.superclass.show.call(this);
		this.groupsGrid.store.load();
		
	}
});

GO.users.showUserDialog = function(user_id, config){

	if(!GO.users.userDialog)
		GO.users.userDialog = new GO.users.UserDialog();

	GO.users.userDialog.show(user_id, config);
}


go.Modules.register("community", 'users', {
	mainPanel: GO.users.MainPanel,
	admin: true,	
	title: t("Users"),
	entities:['User'],
	systemSettingsPanels: [GO.users.SystemSettingsPanel]
});
