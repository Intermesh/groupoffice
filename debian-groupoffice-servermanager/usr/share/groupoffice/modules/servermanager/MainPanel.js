/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


//GO.servermanager.MainPanel = function(config){
//	
//	if(!config)
//	{
//		config = {};
//	}
//	
//	this.installationsGrid = new GO.servermanager.InstallationsGrid({
//		region:'center'
//	});
//
//	this.infoPanel = new Ext.Panel({
//		region:'north',
//		bodyStyle:'padding:5px',
//		height:40,
//		split:true
//	});
//	config.tbar=new Ext.Toolbar({
//		cls:'go-head-tb',
//		items:[{
//			iconCls: 'btn-add',
//			text: GO.lang['cmdAdd'],
//			cls: 'x-btn-text-icon',
//			handler: function(){
//				GO.servermanager.installationDialog.show();
//			},
//			scope: this
//		},{
//			iconCls: 'btn-delete',
//			text: GO.lang['cmdDelete'],
//			cls: 'x-btn-text-icon',
//			handler: function(){
//				this.installationsGrid.deleteSelected();
//			},
//			scope: this
//		},'-',GO.lang['strSearch']+': ', ' ',this.installationsGrid.searchField]		
//	});
//
//	config.items=[
//		this.infoPanel,
//		this.installationsGrid
//		];
//	
//
//	this.xtemplate = new Ext.XTemplate(
//		'<tpl if="max_users==0"><p>No license restrictions apply to this server</p></tpl>'+
//		'<tpl if="max_users!=0">'+
//		'<tpl if="max_users &gt; total_users"><p>Used {total_users} of {max_users} available users</p></tpl>'+
//		'<tpl if="max_users &lt;= total_users"><p>You have no professional licenses available.</p></tpl>'+
//		//'<tpl if="max_billing &gt; total_billing"><p>Used {total_billing} of {max_billing} available billing installations</p></tpl>'+
//		//'<tpl if="max_billing &lt;= total_billing"><p>No billing installations available</p></tpl>'+
//		'</tpl>'
//		);
//
//	
//	//dirty but it works
//	this.installationsGrid.store.on('load', function(){		
//		this.xtemplate.overwrite(this.infoPanel.body, this.installationsGrid.store.reader.jsonData);
//	}, this);
//
//	config.title=GO.servermanager.lang.servermanager;
//	
//	config.layout='border';
//	GO.servermanager.MainPanel.superclass.constructor.call(this, config);
//	
//};
//
//
//Ext.extend(GO.servermanager.MainPanel, Ext.Panel, {
//	afterRender : function(){
//		GO.servermanager.MainPanel.superclass.afterRender.call(this);
//	}
//});



GO.mainLayout.onReady(function(){
	GO.servermanager.installationDialog = new GO.servermanager.InstallationDialog();
/* {LINKDIALOGS} */
});
/*
 * This will add the module to the main tabpanel filled with all the modules
 */
 
GO.moduleManager.addModule('servermanager', GO.servermanager.InstallationsGrid, {
	title : GO.servermanager.lang.servermanager,
	iconCls : 'go-tab-icon-servermanager'
});
