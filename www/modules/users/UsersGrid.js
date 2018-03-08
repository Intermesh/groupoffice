/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UsersGrid.js 22395 2018-02-19 14:18:35Z wsmits $
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
				{header: GO.users.lang.id, dataIndex: 'id', width: 40},
        {header: GO.lang['strUsername'], dataIndex: 'username', width: 200},
        {header: GO.lang['strName'], dataIndex: 'name', width: 250},
        {header: GO.users.lang.numberOfLogins, dataIndex: 'logins', width: 100, align:"right"},
        {header: GO.users.lang['cmdFormLabelLastLogin'], dataIndex: 'lastlogin', width: 110},
        {header: GO.users.lang['cmdFormLabelRegistrationTime'], dataIndex: 'ctime', width: 110},      
        {header: GO.lang['strEmail'], dataIndex: 'email',  hidden: false, width: 150},
        {
            header: GO.users.lang['diskQuota'], 
            dataIndex: 'disk_quota',
            width: 100, 
            renderer: function(v, metaData, record){
                if(v)
                   return v+' MB';
            }
        },
        {
            header: GO.users.lang['spaceUsed'], 
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
	{header: GO.users.lang['cmdBoxLabelEnabled'], dataIndex: 'enabled',  hidden: false, width: 100},
	{header: GO.users.lang['changePassword'], dataIndex: 'force_password_change',  hidden: false, width: 170, renderer: GO.grid.ColumnRenderers.yesNo}
	,{header: GO.lang.strMtime, dataIndex: 'mtime',  hidden: true, width: 170}
    ]
	};

	if(GO.customfields)
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
			if(record.data.enabled == GO.lang['no']){
				return 'user-disabled';
			}
		}
	});

	config.deleteConfig={extraWarning:GO.users.lang.deleteWarning+"\n\n"};
			
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
	        displayMsg: GO.lang['displayingItems']+'. '+GO.lang.strMax+' '+GO.settings.config.max_users,
	        emptyMsg: GO.lang['strNoItems']
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