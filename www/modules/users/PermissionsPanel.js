/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PermissionsPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.PermissionsPanel = function(config)
{
    if(!config)
    {
        config={};
    }
	
    config.autoScroll=false;
    config.hideLabel=true;
    config.title = t("Permissions");
    config.layout='columnfit';
    config.anchor='100% 100%';
	
    config.defaults={
        border:true,
        height:280,
        autoScroll:true
    };
	
		this.moduleAccessGrid = new GO.grid.ModulePermissionsGrid({
			title: t("Module access", "users"),
			storeUrl: GO.url('modules/module/permissionsStore'),
			columnWidth: .5,
			layout:'fit',
			paramIdType: 'userId'
		});
	
    /* end module permissions grid */
	
	
    /* group member grid */
	
    var groupsMemberOfColumn = new GO.grid.CheckColumn({
        header: '',
        dataIndex: 'selected',
        width: 55,
        menuDisabled:true
    });
	
	
    this.groupMemberStore = new GO.data.JsonStore({
        url:GO.url('users/user/groupStore'),
        baseParams: {
            user_id: 0,
			limit: 0
        },
        fields: ['id', 'disabled', 'name', 'selected'],
        root: 'results'
    });
	
    var groupMemberGrid = new GO.grid.GridPanel({
        columnWidth: .5,
        layout:'fit',
        title: t("User is member of", "users"),
        columns: [
        {
            id:'name',
            header: t("Group", "users"),
            dataIndex: 'name',
            menuDisabled:true
        },
        groupsMemberOfColumn
        ],
        ds: this.groupMemberStore,
        //sm: new Ext.grid.RowSelectionModel({singleSelect:singleSelect}),
        plugins: groupsMemberOfColumn,
        autoExpandColumn:'name'
    });
	
	
	
    /* end group member grid */
	
	
	
	

    config.items=[
    this.moduleAccessGrid,
    groupMemberGrid];
	

    GO.users.PermissionsPanel.superclass.constructor.call(this, config);
}


Ext.extend(GO.users.PermissionsPanel, Ext.Panel,{
	
    setUserId : function(user_id, reset)
    {
        if(!this.isVisible() && user_id!=this.user_id)
        {
            this.groupMemberStore.removeAll();
            //this.modulePermissionsStore.baseParams.user_id=-1;
						this.moduleAccessGrid.setIdParam(-1);
            this.groupMemberStore.baseParams.user_id=-1;
        }
        this.user_id=user_id;
				
				this.moduleAccessGrid.setIdParam(user_id);
    //this.setDisabled(this.user_id==0);
    },
	
    onShow : function(){
        GO.users.PermissionsPanel.superclass.onShow.call(this);
				
        if(this.groupMemberStore.baseParams.user_id!=this.user_id)
        {
					this.moduleAccessGrid.setIdParam(this.user_id);
          //this.modulePermissionsStore.baseParams.user_id=this.user_id;
					this.groupMemberStore.baseParams.user_id=this.user_id;

          this.groupMemberStore.load();
          this.moduleAccessGrid.store.load();
        }
    },

		commit : function(){
			this.moduleAccessGrid.store.commitChanges();
			this.moduleAccessGrid.show();
			this.groupMemberStore.commitChanges();
		},
	
    getPermissionParameters : function(){

		
//        var modulePermissions = new Array();
        var memberGroups = new Array();
		 
//        for (var i = 0; i < this.modulePermissionsStore.data.items.length;  i++)
//        {
//            modulePermissions[i] =
//            {
//                id: this.modulePermissionsStore.data.items[i].get('id'),
//                name: this.modulePermissionsStore.data.items[i].get('name'),
//                read_permission: this.modulePermissionsStore.data.items[i].get('read_permission'),
//                write_permission: this.modulePermissionsStore.data.items[i].get('write_permission')
//            };
//        }
		 
        for (var i = 0; i < this.groupMemberStore.data.items.length;  i++)
        {
            memberGroups[i] =
            {
                id: this.groupMemberStore.data.items[i].get('id'),
                group: this.groupMemberStore.data.items[i].get('name'),
                selected: this.groupMemberStore.data.items[i].get('selected')
            };
        }
	
	
        return {
            modules : this.moduleAccessGrid.getPermissionData(),            
            group_member : Ext.encode(memberGroups)
        };
    }

});			