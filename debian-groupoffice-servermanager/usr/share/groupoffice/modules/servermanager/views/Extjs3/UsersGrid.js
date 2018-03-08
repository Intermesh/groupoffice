/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UsersGrid.js 16251 2013-11-15 08:39:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
 GO.servermanager.UsersGrid = Ext.extend(GO.grid.GridPanel,{	
	constructor : function(config){
		
		config=config||{};
		
		config.title = GO.servermanager.lang["users"];
		//config.autoHeight=true;
		config.paging=true;
		
		config.store = new GO.data.JsonStore({
			url: GO.url("servermanager/installation/usersStore"),
			id: 'id',
			baseParams: {
				installation_id:0
			},
			fields: ['id','used_modules','username','enabled','lastlogin','ctime','user_id','trialDaysLeft', 'installation_id'],
			remoteSort: true
		});
		
		config.viewConfig = {'forceFit':true,'autoFill':true};
		
		config.columns = [
			{dataIndex:'user_id',header:GO.lang.strUser},
			{dataIndex:'username', header:GO.lang.strUsername},
			{dataIndex:'used_modules', header:GO.servermanager.lang['modules']},
			{dataIndex:'enabled',header:GO.servermanager.lang['enabled'],width:100},
			{dataIndex:'lastlogin',header:GO.servermanager.lang['lastlogin']},
			{dataIndex:'ctime',header:GO.lang.strCtime, width:110},
			{dataIndex:'trialDaysLeft',header:'Trial days left'}
		];
		
		config.listeners={
			show:function(){
				this.store.load();
			}
		}
		
		//TODO: render some total at the bottom
		GO.servermanager.UsersGrid.superclass.constructor.call(this,config);
	}
});