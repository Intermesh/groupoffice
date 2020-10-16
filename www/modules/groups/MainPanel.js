/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 15285 2013-07-23 13:51:52Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

 
GO.groups.MainPanel = function(config){
	
	if(!config)
	{
		config = {};
	}

	this.store = new GO.data.JsonStore({
	    url: GO.url('groups/group/store'),
	    baseParams: {
				permissionLevel: GO.permissionLevels.manage
			},
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id', 'name', 'user_id', 'user_name','acl_id'],
	    remoteSort: true
	});			

	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
        {header: GO.groups.lang.groups, dataIndex: 'name', width: 300},
        {header: GO.groups.lang.owner, dataIndex: 'user_name', sortable:false}
    ]
	});  
	
	this.searchField = new GO.form.SearchField({
		store: this.store,
		width:320
	});
		    	

	var tbar = new Ext.Toolbar({
		cls:'go-head-tb',
		items: [{
                xtype:'htmlcomponent',
		        html:GO.groups.lang.name,
		        cls:'go-module-title-tbar'
		},
		{
			iconCls: 'btn-add',
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){this.showGroupDialog(0);},
			scope: this,
			disabled: !GO.settings.modules.groups.write_permission
		},
		{
			iconCls: 'btn-delete',
			text: GO.lang['cmdDelete'],
			cls: 'x-btn-text-icon',
			handler: function(){this.deleteSelected();},
			scope: this,
			disabled: !GO.settings.modules.groups.write_permission
		},'-',GO.lang['strSearch'] + ':', this.searchField
		]});
      
  config.layout='fit';

  config.cm=columnModel;
  config.sm=new Ext.grid.RowSelectionModel({singleSelect: false});
  config.tbar=tbar;
  config.paging=true;
	config.noDelete= !GO.settings.modules.groups.write_permission;
  config.viewConfig={
  	autoFill:true,
  	forceFit:true
  };
  
	GO.groups.MainPanel.superclass.constructor.call(this, config);	
};


Ext.extend(GO.groups.MainPanel, GO.grid.GridPanel, {
	afterRender : function(){
    GO.groups.MainPanel.superclass.afterRender.call(this);

    this.on("rowdblclick",this.rowDoubleClick, this);

    this.store.load();
  },

  rowDoubleClick : function(grid)
  {
    this.showGroupDialog(grid.selModel.selections.items[0].data.id);
  },

  showGroupDialog : function(group_id)
  {
    if(!this.groupDialog)
    {
      this.groupDialog = new GO.groups.GroupDialog();
      this.groupDialog.on('save', function(dlg, id){
        this.store.reload();
      },this);
    }

    this.groupDialog.show(group_id);
  }
});


/*
 * This will add the module to the main tabpanel filled with all the modules
 */
GO.moduleManager.addModule('groups', GO.groups.MainPanel, {
		title : GO.groups.lang.groups,
		iconCls : 'go-tab-icon-groups',
		admin :true
});
