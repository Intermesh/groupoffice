


/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.log.MainPanel = function(config) {
	if (!config) {
		config = {};
	}
	
	config.noDelete = true;
	config.title = t("Activity log", "log");
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
			header : t("Time", "log")	,
			dataIndex : 'ctime'
		},{
			header : t("Action", "log"),
			dataIndex : 'action'
		}, {
			header : t("Message", "log"),
			dataIndex : 'message'
		},{
			header : t("Username"),
			dataIndex : 'username',
			sortable : false
		}, {
			header : t("Model", "log"),
			dataIndex : 'model'
		}, {
			header : t("ID", "log"),
			dataIndex : 'model_id'
		}, {
			header : t("User agent", "log"),
			dataIndex : 'user_agent'
		}, {
			header : t("IP address", "log"),
			dataIndex : 'ip'
		}, {
			header : t("Controller", "log"),
			dataIndex : 'controller_route'
		}
		]
	});
	
	config.cm = columnModel;
	config.view = new Ext.grid.GridView({
		autoFill : true,
		forceFit : true,
		emptyText : t("No items to display")
	});
	config.sm = new Ext.grid.RowSelectionModel();
	config.loadMask = true;

	this.searchField = new GO.form.SearchField({
		store : config.store,
		width : 320
	});
		
	config.tbar=new Ext.Toolbar({items:[{
	    xtype:'htmlcomponent',
			html:t("Activity log", "log"),
			cls:'go-module-title-tbar'
		},
		this.exportMenu = new GO.base.ExportMenu({className:'GO\\Log\\Export\\CurrentGrid'})
			,'-',t("Search") + ':', this.searchField], cls:'go-head-tb'});
	
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
	title : t("Activity log", "log"),
	iconCls : 'go-tab-icon-log',
	admin:true
});
