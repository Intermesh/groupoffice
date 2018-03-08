/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UsageHistoryGrid.js 16251 2013-11-15 08:39:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
 GO.servermanager.UsageHistoryGrid = Ext.extend(GO.grid.GridPanel,{	
	constructor : function(config){
		
		config=config||{};
		
		config.title = GO.servermanager.lang["usageHistory"];
		//config.autoHeight=true;
		config.paging=true;
		
		config.store = new GO.data.JsonStore({
			url: GO.url("servermanager/installation/historyStore"),
			id: 'id',
			baseParams: {
				installation_id:0
			},
			sortInfo: {
				field: 'id',
				direction: 'DESC' // or 'DESC' (case sensitive for local sorting)
			},
			fields: ['id','ctime','count_users','database_usage','file_storage_usage','mailbox_usage', 'total_logins','total_usage' ,'installation_id'],
			remoteSort: true
		});
		
		config.viewConfig = {'forceFit':true,'autoFill':true};
		
		config.columns = [
			{dataIndex:'ctime', header:GO.lang.strCtime, width:110},
			{dataIndex:'count_users',header:GO.servermanager.lang["countUsers"], width:100},
			{dataIndex:'database_usage',header:GO.servermanager.lang["databaseUsage"]},
			{dataIndex:'file_storage_usage',header:GO.servermanager.lang["fileStorageUsage"]},
			{dataIndex:'mailbox_usage',header:GO.servermanager.lang["mailboxUsage"]},
			{dataIndex:'total_usage',header:GO.servermanager.lang["totalUsage"]},
			{dataIndex:'total_logins',header:GO.servermanager.lang["totalLogins"]}
		];
		
		config.listeners={
			show:function(){
				this.store.load();
			}
		}
		
		//TODO: render some total at the bottom
		GO.servermanager.UsageHistoryGrid.superclass.constructor.call(this,config);
	}
});