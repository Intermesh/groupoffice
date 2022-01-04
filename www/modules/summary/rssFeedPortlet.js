/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: rssFeedPortlet.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.portlets.rssFeedPortlet = function(config) {
	
	config = config || {};

	config.store = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
			url: GO.url("summary/rssFeed/proxy")
		}),
		baseParams: {
			feed: config.feed
			},

		reader: new Ext.data.XmlReader(
		{
			record: 'item'
		},
		['title', 'author', {
			name:'pubDate',
			type:'date'
		}, 'link', 'description', 'content']
		)
	});
	config.store.setDefaultSort('pubDate', "DESC");

	config.columns = [{
		id: 'title',
		header: t("Title"),
		dataIndex: 'title',
		sortable:true,
		width: 420,
		renderer: this.formatTitle
	},{
		header: t("Author"),
		dataIndex: 'author',
		width: 100,
		hidden: true,
		sortable:true
	},{
		id: 'last',
		header: t("Date"),
		dataIndex: 'pubDate',
		width: 150,
		xtype:"datecolumn",
		//renderer:  this.formatDate,
		sortable:true
	}];

	config.loadMask = {
			msg:t("Loading Feed...", "summary")
			};
	config.sm = new Ext.grid.RowSelectionModel({
			singleSelect:true
		});

	config.viewConfig={
			forceFit:true,
			enableRowBody:true,
			showPreview:config.showPreview,
			getRowClass : this.applyRowClass
		};

	GO.portlets.rssFeedPortlet.superclass.constructor.call(this, config);	
};

Ext.extend(GO.portlets.rssFeedPortlet, GO.grid.GridPanel, {

	refreshTask : false,

	afterRender : function(){
		GO.portlets.rssFeedPortlet.superclass.afterRender.call(this);

		this.on('rowcontextmenu', this.onContextClick, this);
		this.on('rowdblclick', this.rowDoubleClick, this);
		this.on('rowclick', this.rowClick, this);

		this.refreshTask ={
			run: function(){this.store.load()},
			scope:this,
			//interval:5000
			interval:1800000
		};

		Ext.TaskMgr.start(this.refreshTask);

		this.on('beforedestroy', function(){
			Ext.TaskMgr.stop(this.refreshTask);
		}, this);
	},
		
	rowDoubleClick : function(grid, index, e) {
		var record = this.store.getAt(index);
			
		window.open(record.data.link);
			
	},


	rowClick : function(grid, index, e){
		var target = e.target;
	
		if(target.tagName!='A')
		{
			target = Ext.get(target).findParent('A', 10);
			
			if(!target)
				return false;
		}
		e.preventDefault();
		window.open(target.attributes['href'].value);
	},
	onContextClick : function(grid, index, e){
		if(!this.menu){ // create context menu on first right click
			this.menu = new Ext.menu.Menu({
				items: [
				{
					iconCls: 'new-win',
					text: t("Go to Post", "summary"),
					scope:this,
					handler: function(){
						window.open(this.ctxRecord.data.link);
					}
				},'-',{
					iconCls: 'refresh-icon',
					text:t("Refresh"),
					scope:this,
					handler: function(){
						this.ctxRow = null;
						this.store.reload();
					}
				}]
			});
			this.menu.on('hide', this.onContextHide, this);
		}
		e.stopEvent();
		if(this.ctxRow){
			Ext.fly(this.ctxRow).removeClass('x-node-ctx');
			this.ctxRow = null;
		}
		this.ctxRow = this.view.getRow(index);
		this.ctxRecord = this.store.getAt(index);
		Ext.fly(this.ctxRow).addClass('x-node-ctx');
		this.menu.showAt(e.getXY());
	},

	onContextHide : function(){
		if(this.ctxRow){
			Ext.fly(this.ctxRow).removeClass('x-node-ctx');
			this.ctxRow = null;
		}
	},

	loadFeed : function(url, preview) {
		//console.log(preview);
		var view = this.getView();
		view.showPreview = preview;

		this.store.baseParams = {
			feed: url
		};
		
        this.store.load();
	},

	// within this function "this" is actually the GridView
	applyRowClass: function(record, rowIndex, p, ds) {
		if (this.showPreview) {
			p.body = '<p class="description">' +Ext.util.Format.htmlDecode(record.data.description.trim()) + '</p>';
			return 'x-grid3-row-expanded';
		}
		return 'x-grid3-row-collapsed';
	},

	formatDate : function(date) {
		if (!date) {
			return '';
		}
		var now = new Date();
		var d = now.clearTime(true);
		var notime = date.clearTime(true).getTime();
		if (notime == d.getTime()) {
			return t("Today ", "summary") + date.dateFormat('g:i a');
		}
		d = d.add('d', -6);
		if (d.getTime() <= notime) {
			return date.dateFormat('D g:i a');
		}
		return date.dateFormat('n/j g:i a');
	},

	formatTitle: function(value, p, record) {
		return '<div class="topic"><b>'+value+'</b></div>';
               
	}
});
