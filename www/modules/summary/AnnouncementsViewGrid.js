/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AnnouncementsViewGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.summary.AnnouncementsViewGrid = function(config){
	if(!config)
	{
		config = {};
	}
	config.border=false;
	//config.layout='fit';
	config.autoHeight=true;
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
		suppressError: true,
		url: GO.url('summary/announcement/store'),
		baseParams: {
			active:'true'
		},
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['id','user_name','due_time','ctime','mtime','title', 'content'],
		remoteSort: true
	});
	var columnModel =  new Ext.grid.ColumnModel([
	{
		header: '',
		dataIndex: 'title',
		sortable: false,
		renderer: function(value, p, record) {
			return '<b>'+value+'</b>';
		}
	}
	]);

	config.cls='go-colored-table go-grid3-hide-headers';
	
	config.cm=columnModel;
	config.view=new Ext.grid.GridView({
		enableRowBody:true,
		showPreview:true,
		forceFit:true,
		autoFill: true,
		getRowClass : function(record, rowIndex, p, ds) {

			var cls = rowIndex%2 == 0 ? 'odd' : 'even';

			if (this.showPreview) {
				p.body = '<div class="description go-html-formatted">' +record.data.content + '</div>';
				return 'x-grid3-row-expanded '+cls;
			}
			return 'x-grid3-row-collapsed';
		},
		emptyText: t("No items to display")		
	});
	//config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	config.disableSelection=true;

	config.listeners={
		rowclick : function( grid , rowIndex, e ) {

			if(e.target.tagName=='A')
			{
				e.preventDefault();
				
				var href=e.target.attributes['href'].value;
				if(GO.email && href.substr(0,6)=='mailto')
				{
					var indexOf = href.indexOf('?');
					if(indexOf>0)
					{
						var email = href.substr(7, indexOf-8);
					}else
					{
						var email = href.substr(7);
					}
					GO.email.addressContextMenu.showAt(e.getXY(), email);
				}else if(href!='#')
				{
					if(href.substr(0,6)=='callto')
						document.location.href=href;
					else
						window.open(href);
				}
			}
		},
		scope:this
	}
	
	GO.summary.AnnouncementsViewGrid.superclass.constructor.call(this, config);
};
Ext.extend(GO.summary.AnnouncementsViewGrid, GO.grid.GridPanel,{

	processEvent: function() {},
	afterRender : function(){
		GO.summary.AnnouncementsViewGrid.superclass.afterRender.call(this);
		
		Ext.TaskMgr.start({
			run: function(){
				this.store.load();
			},
			scope:this,
			interval:180000
		});

	}
	
});
