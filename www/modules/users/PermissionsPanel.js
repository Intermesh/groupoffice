/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PermissionsPanel.js 19015 2015-04-21 08:15:50Z michaelhart86 $
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
    config.title = GO.lang['strPermissions'];
    config.layout='columnfit';
    config.anchor='100% 100%';
	
    config.defaults={
        border:true,
        height:280,
        autoScroll:true
    };
	
		this.moduleAccessGrid = new GO.grid.ModulePermissionsGrid({
			title: GO.users.lang.moduleAccess,
			storeUrl: GO.url('modules/module/permissionsStore'),
			columnWidth: .4,
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
        columnWidth: .3,
        layout:'fit',
        title: GO.users.lang.userIsMemberOf,
        columns: [
        {
            id:'name',
            header: GO.users.lang.group,
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
	
	
	
    /* group visible grid */
	
    var groupsVisibleToColumn = new GO.grid.CheckColumn({
        header: '',
        dataIndex: 'selected',
        width: 55,
        menuDisabled:true
    });
	
	
	
    this.groupVisibleStore = new GO.data.JsonStore({
        url:GO.url('users/user/visibleGroupStore'),
        baseParams: {
            user_id: -1,
			limit: 0
        },
        fields: ['id', 'disabled', 'name', 'selected'],
        root: 'results'
    });
	
    var groupVisibleGrid = new GO.grid.GridPanel({
        columnWidth: .3,
        layout:'fit',
        title: GO.users.lang.userVisibleTo,
        columns: [
        {
            id:'name',
            header: GO.users.lang.group,
            dataIndex: 'name',
            menuDisabled:true
        },
        groupsVisibleToColumn
        ],
        ds: this.groupVisibleStore,
        plugins: groupsVisibleToColumn,
        autoExpandColumn:'name'
    });
	
	
    /* end group visible grid */

    config.items=[
    this.moduleAccessGrid,
    groupMemberGrid,
    groupVisibleGrid];
	

    GO.users.PermissionsPanel.superclass.constructor.call(this, config);
}


Ext.extend(GO.users.PermissionsPanel, Ext.Panel,{
	
    setUserId : function(user_id, reset)
    {
        if(!this.isVisible() && user_id!=this.user_id)
        {
            this.groupMemberStore.removeAll();
            //this.modulePermissionsStore.removeAll();
            this.groupVisibleStore.removeAll();
			
            //this.modulePermissionsStore.baseParams.user_id=-1;
						this.moduleAccessGrid.setIdParam(-1);
            this.groupMemberStore.baseParams.user_id=-1;
            this.groupVisibleStore.baseParams.user_id=-1;
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
					this.groupVisibleStore.baseParams.user_id=this.user_id;

          this.groupMemberStore.load();
          this.moduleAccessGrid.store.load();
					this.groupVisibleStore.load();
        }
    },

		commit : function(){
			this.moduleAccessGrid.store.commitChanges();
			this.moduleAccessGrid.show();
			this.groupMemberStore.commitChanges();
			this.groupVisibleStore.commitChanges();
		},
	
    getPermissionParameters : function(){

		
//        var modulePermissions = new Array();
        var memberGroups = new Array();
        var visibleGroups = new Array();
		 
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

        for (var i = 0; i < this.groupVisibleStore.data.items.length;  i++)
        {
            visibleGroups[i] =
            {
                id: this.groupVisibleStore.data.items[i].get('id'),
                group: this.groupVisibleStore.data.items[i].get('name'),
                selected: this.groupVisibleStore.data.items[i].get('selected')
            };
        }
	
	
        return {
            modules : this.moduleAccessGrid.getPermissionData(),
            groups_visible : Ext.encode(visibleGroups),
            group_member : Ext.encode(memberGroups)
        };
    }

});			