/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UsageHistoryGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
 GO.servermanager.UsageHistoryGrid = Ext.extend(GO.grid.GridPanel,{	
	constructor : function(config){
		
		config=config||{};
		
		config.title = t("usageHistory", "servermanager");
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
			{dataIndex:'ctime', header:t("Created at"), width:110},
			{dataIndex:'count_users',header:t("countUsers", "servermanager"), width:100},
			{dataIndex:'database_usage',header:t("databaseUsage", "servermanager")},
			{dataIndex:'file_storage_usage',header:t("fileStorageUsage", "servermanager")},
			{dataIndex:'mailbox_usage',header:t("mailboxUsage", "servermanager")},
			{dataIndex:'total_usage',header:t("totalUsage", "servermanager")},
			{dataIndex:'total_logins',header:t("totalLogins", "servermanager")}
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