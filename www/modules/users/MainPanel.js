/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 21680 2017-11-14 08:16:38Z michaelhart86 $
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
	
	this.usersGridPanel = new GO.users.UsersGrid({'region':'center'});
	
	this.groupsGrid = new GO.users.GroupsGrid({
		relatedStore: this.usersGridPanel.store,
		region:'west',
		id:'users-groups-panel',
		width: 250

	});
	
  this.searchField = new GO.form.SearchField({
		store: this.usersGridPanel.store,
		width:320
  });
	
	this.tbar = new Ext.Toolbar({		
			cls:'go-head-tb',
			items: [{
		      	 	xtype:'htmlcomponent',
				html:GO.users.lang.name,
				cls:'go-module-title-tbar'
			},
		  	{
		  		iconCls: 'btn-add', 
		  		text: GO.lang['cmdAdd'], 
		  		cls: 'x-btn-text-icon', 
		  		handler: function(){
		  			//if(GO.settings.config.max_users > 0 && this.usersGridPanel.store.totalLength >= GO.settings.config.max_users)
		  			//{
					// THIS is now check serverside because we can only check the enabled users
		  			//	Ext.Msg.alert(GO.lang.strError, GO.users.lang.maxUsersReached);
		  			//}else
		  			//{
		  				GO.users.showUserDialog();
		  			//}
		  		}, 
		  		scope: this
		  	},
		  	{
		  		iconCls: 'btn-delete', 
		  		text: GO.lang['cmdDelete'], 
		  		cls: 'x-btn-text-icon', 
		  		handler: function(){
						this.usersGridPanel.deleteSelected();
					},
		  		scope: this
		  	},{
		  		iconCls: 'btn-upload',
		  		text:GO.lang.cmdImport,
		  		handler:function(){
		  			if(!this.importDialog)
		  			{
		  				this.importDialog = new GO.users.ImportDialog();
		  				this.importDialog.on('import', function(){this.usersGridPanel.store.reload();}, this);
		  			}
		  			this.importDialog.show();
		  		},
		  		scope:this		  		
		  	},{
				iconCls:'btn-settings',
				text:GO.lang.administration,
				handler:function(){
					if(!this.settingsDialog)
					{
						this.settingsDialog = new GO.users.SettingsDialog();
					}
					this.settingsDialog.show();
				},
				scope:this
			},
			this.exportMenu = new GO.base.ExportMenu({className:'GO\\Users\\Export\\CurrentGrid'}),
			{
				iconCls: 'bsync-btn-sync',
				text: GO.users.lang['transferData'],
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
//				text:GO.users.lang.showProUsers,
//				toggleHandler:function(btn, pressed){
//					this.store.baseParams.show_licensed=pressed ? 1 : 0;
//					this.store.load();
//				},
//				scope:this
//			},
				'-',
		         GO.lang['strSearch']+':',
		        this.searchField
		    ]});
			
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


GO.linkHandlers["GO\\Base\\Model\\User"]=function(id){
	//GO.users.showUserDialog(id);
	if(!GO.users.userLinkWindow){
		var userPanel = new GO.users.UserPanel();
		GO.users.userLinkWindow = new GO.LinkViewWindow({
			title: GO.lang.strUser,
			closeAction:'hide',
			items: userPanel,
			userPanel: userPanel
		});
	}
	GO.users.userLinkWindow.userPanel.load(id);
	GO.users.userLinkWindow.show();
	return GO.users.userLinkWindow;
};

GO.linkPreviewPanels["GO\\Base\\Model\\User"]=function(config){
	config = config || {};
	return new GO.users.UserPanel(config);
}


GO.moduleManager.addModule('users', GO.users.MainPanel, {
	title : GO.lang.users,
	iconCls : 'go-tab-icon-users',
	admin :true
});