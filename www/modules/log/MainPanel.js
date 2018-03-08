


/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 21354 2017-08-03 15:05:25Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.log.MainPanel = function(config) {
	if (!config) {
		config = {};
	}
	
	config.noDelete = true;
	config.title = GO.log.lang.name;
	config.layout = 'fit';
	config.autoScroll = true;
	config.split = true;
	config.store = new GO.data.JsonStore({
		url : GO.url("log/log/store"),
		fields : ['id','ctime','action','message', 'model_id', 'model', 'username','user_agent','ip','controller_route'],
		remoteSort : true
	});
	config.paging = true;
	var columnModel = new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[{
			header : GO.log.lang.logCtime	,
			dataIndex : 'ctime'
		},{
			header : GO.log.lang.logAction,
			dataIndex : 'action'
		}, {
			header : GO.log.lang.logMessage,
			dataIndex : 'message'
		},{
			header : GO.lang.strUsername,
			dataIndex : 'username',
			sortable : false
		}, {
			header : GO.log.lang.logModel,
			dataIndex : 'model'
		}, {
			header : GO.log.lang.logModel_id,
			dataIndex : 'model_id'
		}, {
			header : GO.log.lang.logUser_agent,
			dataIndex : 'user_agent'
		}, {
			header : GO.log.lang.logIp,
			dataIndex : 'ip'
		}, {
			header : GO.log.lang.logController_route,
			dataIndex : 'controller_route'
		}
		]
	});
	
	config.cm = columnModel;
	config.view = new Ext.grid.GridView({
		autoFill : true,
		forceFit : true,
		emptyText : GO.lang['strNoItems']
	});
	config.sm = new Ext.grid.RowSelectionModel();
	config.loadMask = true;

	this.searchField = new GO.form.SearchField({
		store : config.store,
		width : 320
	});
		
	config.tbar=new Ext.Toolbar({items:[{
	    xtype:'htmlcomponent',
			html:GO.log.lang.name,
			cls:'go-module-title-tbar'
		},
		this.exportMenu = new GO.base.ExportMenu({className:'GO\\Log\\Export\\CurrentGrid'})
			,'-',GO.lang['strSearch'] + ':', this.searchField], cls:'go-head-tb'});
	
	this.exportMenu.setColumnModel(columnModel);
			
	GO.log.MainPanel.superclass.constructor.call(this, config);
};
Ext.extend(GO.log.MainPanel, GO.grid.GridPanel, {
	afterRender : function() {
		GO.log.MainPanel.superclass.afterRender.call(this);
		this.store.load();
	}
});


GO.moduleManager.addModule('log', GO.log.MainPanel, {
	title : GO.log.lang.name,
	iconCls : 'go-tab-icon-log',
	admin:true
});