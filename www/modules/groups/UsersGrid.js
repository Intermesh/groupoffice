/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UsersGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 

GO.groups.UsersGrid = Ext.extend(GO.grid.GridPanel,{
	changed : false,
	
	initComponent : function(){
		
    this.userStore = new GO.data.JsonStore({
        url: GO.url("groups/group/getUsers"),
        baseParams: {id: 0},
        root: 'results',
        id: 'id',
        fields: ['id', 'user_id', 'name', 'username', 'email'],
        remoteSort: true
    });
    
		Ext.apply(this,{
			standardTbar:true,
			store: this.userStore,
			border: false,
			paging:true,
			view:new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: GO.lang['strNoItems']		
			}),
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
          {header: GO.lang.strName, dataIndex: 'name'},
          {header: GO.lang.strUsername, dataIndex: 'username'},
          {header: GO.lang.strEmail, dataIndex: 'email'}	
				]
			})
		});
		
		GO.groups.UsersGrid.superclass.initComponent.call(this);
		
   
	},  
  
  setGroupId : function(group_id){
    this.userStore.baseParams.id=group_id;
    this.userStore.load();
		this.setDisabled(!group_id);
  },
	
	btnAdd : function(){				
		if(!this.addUsersDialog)
    {
      this.addUsersDialog = new GO.dialog.SelectUsers({
        handler:function(allUserGrid)
        {
          if(allUserGrid.selModel.selections.keys.length>0)
          {
            this.userStore.baseParams['add_users']=Ext.encode(allUserGrid.selModel.selections.keys);
            this.userStore.load();
            delete this.userStore.baseParams['add_users'];
          }
        },
        scope:this				
      });
    }
    this.addUsersDialog.show();	  	
	},
	deleteSelected : function(){
		GO.groups.UsersGrid.superclass.deleteSelected.call(this);
		this.changed=true;
	}
});