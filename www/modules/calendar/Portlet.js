/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Portlet.js 22337 2018-02-07 08:23:15Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.SummaryGroupPanel = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	config.store = new GO.data.GroupingStore({
		suppressError: true,
		reader: new Ext.data.JsonReader({
			totalProperty: "count",
			root: "results",
			id: "id",
			fields: [
			'id',
			'event_id',
			'name',
			'time',
			'start_time',
			'end_time',
			'description',
			'location',
			'private',
			'repeats',
			'day',
			'calendar_name'
			]
		}),
		baseParams: {
			task:'summary',
			'user_id' : GO.settings.user_id,
			'portlet' : true
		},
		proxy: new Ext.data.HttpProxy({
			url: GO.url("calendar/portlet/portletGrid")
		}),
		groupField:'day',
		sortInfo: {
			field: 'id',
			direction: 'ASC'
		},
		remoteGroup:true,
		remoteSort:true
	});

	config.paging=false;
	config.autoExpandColumn='summary-calendar-name-heading';

	config.cls = "go-grid3-hide-headers";
	config.columns=[
	{
		header:t("Day"),
		dataIndex: 'day'
	},
	{
		header:t("Time"),
		dataIndex: 'time',
		width:100,
		align:'right',
		groupable:false
	},
	{
		id:'summary-calendar-name-heading',
		header:t("Name"),
		dataIndex: 'name',
		renderer:function(value, p, record){
			p.attr = 'ext:qtip="'+GO.calendar.formatQtip(record.data)+'"';
			return value;
		},
		groupable:false
	},{
		header:t("Calendar", "calendar"),
		dataIndex: 'calendar_name',
		width:140
	}];
		
	config.view=  new Ext.grid.GroupingView({
		scrollOffset: 2,
		hideGroupedColumn:true,
		groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'+t("items")+'" : "'+t("item")+'"]})',
		emptyText: t("No appointments to display", "calendar"),
		showGroupName:false
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	config.autoHeight=true;
	
	GO.calendar.SummaryGroupPanel.superclass.constructor.call(this, config);
};


Ext.extend(GO.calendar.SummaryGroupPanel, Ext.grid.GridPanel, {
	
		
	afterRender : function()
	{
		GO.calendar.SummaryGroupPanel.superclass.afterRender.call(this);

		GO.dialogListeners.add('event',{
			save:function(){
				this.store.reload()
			},
			scope:this
		});		

		this.on("rowclick", function(grid, rowClicked, e){

			var record = grid.store.getAt(rowClicked);

			if(record.data.contact_id)
			{
				go.Router.goto('contact/' + record.data.contact_id);
			}else
			{				
				go.Router.goto('event/' + record.data.event_id);
			}
		}, this);
		
		Ext.TaskMgr.start({
			run: function(){this.store.load();},
			scope:this,
			interval:900000
		});  
	}
	
});

GO.mainLayout.onReady(function(){
	
	if(go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("legacy", "calendar"))
	{
		var calGrid = new GO.calendar.SummaryGroupPanel({
			//state causes it to load: id: 'summary-calendar-grid'
		});
		
		GO.summary.portlets['portlet-calendar']=new GO.summary.Portlet({
			id: 'portlet-calendar',
			//iconCls: 'go-module-icon-calendar',
			title: t("Appointments", "calendar"),
			layout:'fit',
			tools: [{
				id: 'gear',
				handler: function(){
					if(!this.selectCalendarWin)
					{
						this.selectCalendarWin = new GO.base.model.multiselect.dialog({
							url:'calendar/portlet',
							columns:[{ header: t("Name"), dataIndex: 'name', sortable: true }],
							fields:['id','name'],
							title:t("Visible calendars", "calendar"),
							model_id:GO.settings.user_id,
							listeners:{
								hide:function(){
									calGrid.store.reload();
								},
								scope:this
							}
						});
					}
					this.selectCalendarWin.show();
					
				}
			},{
				id:'close',
				handler: function(e, target, panel){
					panel.removePortlet();
				}
			}],
			items: calGrid,
			autoHeight:true
			
		});
	}
});
