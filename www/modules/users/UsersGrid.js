/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UsersGrid.js 22467 2018-03-07 08:42:50Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Boy Wijnmaalen <bwijnmaalen@intermesh.nl>
 */

GO.users.UsersGrid = function(config)
{	
	if(!config)
	{
		config = {};
	}
	
	
		var fields = {
		fields:['id', 'username', 'name','logins','lastlogin','disk_quota','disk_usage', 'ctime','address','address_no','zip','city','state','country','home_phone','email',
	    	'waddress','waddress_no','wzip','wcity','wstate','wcountry','wphone','enabled','force_password_change', 'mtime'],
		columns:[
				{header: t("ID", "users"), dataIndex: 'id', width: 40},
        {header: t("Username"), dataIndex: 'username', width: 200},
        {header: t("Name"), dataIndex: 'name', width: 250},
        {header: t("Number of logins", "users"), dataIndex: 'logins', width: 100, align:"right"},
        {header: t("Last Login", "users"), dataIndex: 'lastlogin', width: dp(140)},
        {header: t("Registration time", "users"), dataIndex: 'ctime', width: dp(140)},      
        {header: t("E-mail"), dataIndex: 'email',  hidden: false, width: 150},
        {
            header: t("Disk Quota", "users"), 
            dataIndex: 'disk_quota',
            width: 100, 
            renderer: function(v, metaData, record){
                if(v)
                   return v+' MB';
            }
        },
        {
            header: t("Space used", "users"), 
            dataIndex: 'disk_usage',
            width: 100, 
            renderer: function(v, metaData, record){
                var quota = record.data.disk_quota
                var mb_used = v/1024/1024;
                if(v) {
                    return '<div class="go-progressbar">'+
                            '<div class="go-progress-indicator" style="width:'+Math.ceil(mb_used/GO.util.unlocalizeNumber(quota)*100)+'%"></div>'+
                            '</div>';
                }
                else
                    return mb_used+' MB';
            }
        },
	{header: t("Enabled", "users"), dataIndex: 'enabled',  hidden: false, width: 100},
	{header: t("Change password", "users"), dataIndex: 'force_password_change',  hidden: false, width: 170, renderer: GO.grid.ColumnRenderers.yesNo}
	,{header: GO.lang.strMtime, dataIndex: 'mtime',  hidden: true, width: 170}
    ]
	};

	if(go.Modules.isAvailable("core", "customfields"))
	{
		GO.customfields.addColumns("GO\\Base\\Model\\User", fields);
	}

	config.store = new GO.data.JsonStore({
	    url: GO.url('users/user/store'),
	    baseParams: {task: 'users'},
	    id: 'id',
	    totalProperty: 'total',
	    root: 'results',
	    fields: fields.fields,
	    remoteSort: true
	});

	config.loadMask=true;
						
	config.store.setDefaultSort('username', 'ASC');
 
	config.view = new Ext.grid.GridView({
		getRowClass : function(record, rowIndex, p, store){
			if(record.data.enabled == t("No")){
				return 'user-disabled';
			}
		}
	});

	config.deleteConfig={extraWarning:t("WARNING!!! All of the user data including addressbooks, projects, calendars etc. will be deleted!", "users")+"\n\n"};
			
	config.cm = new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:fields.columns
	});	
    
   if(GO.settings.config.max_users>0)
   {
	   config.bbar = new Ext.PagingToolbar({
	   			cls: 'go-paging-tb',
	        store: config.store,
	        pageSize: parseInt(GO.settings['max_rows_list']),
	        displayInfo: true,
	        displayMsg: t("Displaying items {0} - {1} of {2}")+'. '+t("Maximum")+' '+GO.settings.config.max_users,
	        emptyMsg: t("No items to display")
	    });
   }

		config.sm = new Ext.grid.RowSelectionModel();
		config.paging=true;		
				
		GO.users.UsersGrid.superclass.constructor.call(this,config);
};
		
Ext.extend(GO.users.UsersGrid, GO.grid.GridPanel,{
	
	afterRender : function(){
		GO.users.UsersGrid.superclass.afterRender.call(this);
		
		this.on("rowdblclick",this.rowDoubleClick, this);			
		this.store.load();


		GO.dialogListeners.add('user',{
			scope:this,
			save:function(){
				this.store.reload();
			}
		});
	},			
	
	rowDoubleClick : function (grid, rowIndex, event)
	{
		var selectionModel = grid.getSelectionModel();
		var record = selectionModel.getSelected();
		GO.users.showUserDialog(record.data['id']);
	}
});
