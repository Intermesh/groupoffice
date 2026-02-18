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

GO.summary.CalendarPortlet = function(config)
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
				'eventId',
				'title',
				'start',
				'duration',
				'description',
				'location',
				'createdAt',
				'private',
				'showWithoutTime',
				'recurrenceRule',
				'calendar',
				'end',
				'day'
			]
		}),
		baseParams: {
			task:'summary',
			'user_id' : GO.settings.user_id,
			'portlet' : true
		},
		proxy: new Ext.data.HttpProxy({
			url: GO.url("summary/calendar/portletGrid")
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
			dataIndex: 'start',
			width:100,
			align:'right',
			groupable:false,
			renderer: (v, p,rec) => { return rec.data.showWithoutTime ? '-' : (new Date(v)).format('G:i'); }
		},
		{
			id:'summary-calendar-name-heading',
			header:t("Name"),
			dataIndex: 'title',
			renderer:function(value, p, record){
				p.attr = 'ext:qtip="'+GO.summary.formatCalendarQtip(record.data)+'"'; // TODO!
				return value;
			},
			groupable:false
		},{
			header:t("Calendar", "calendar"),
			dataIndex: 'calendar',
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

	GO.summary.CalendarPortlet.superclass.constructor.call(this, config);
};

GO.summary.formatCalendarQtip = function(data)
{

	data.start = new Date(data.start);
	data.end = new Date(data.end);
	data.createdAt = new Date(data.createdAt);

	var new_df = GO.settings.time_format;
	if(data.start.format('Ymd')!=data.end.format('Ymd'))
	{
		new_df = GO.settings.date_format+' '+GO.settings.time_format;
	}

	var str = t("Starts at", "calendar")+': '+data.start.format(new_df)+'<br />'+
		t("Ends at", "calendar")+': '+data.end.format(new_df);

	if(!GO.util.empty(data.duration))
		str += '<br />'+t("Timespan", "calendar")+': '+data.duration;

	if(!GO.util.empty(data.status))
	{
		str += '<br />'+t("Status", "calendar")+': ';

		if(t("statuses", "calendar")[data.status]){
			// str+=Ext.util.Format.htmlEncode(t("statuses", "calendar")[data.status]);
			str+=t("statuses", "calendar")[data.status];
		}else
		{
			str+=data.status;
		}
	}

	if(!GO.util.empty(data.calendar))
	{
		// str += '<br />'+t("Calendar", "calendar")+': '+Ext.util.Format.htmlEncode(data.calendar_name);
		str += '<br />'+t("Calendar", "calendar")+': '+data.calendar;
	}

	if(!GO.util.empty(data.username))
	{
		// str += '<br />'+t("Owner")+': '+Ext.util.Format.htmlEncode(data.username);
		str += '<br />'+t("Owner")+': '+data.username;
	}

	str += '<br />'+t("Created at")+': '+data.createdAt.format(GO.settings.date_format+' '+GO.settings.time_format);


	if(!GO.util.empty(data.location))
	{
		// str += '<br />'+t("Location", "calendar")+': '+Ext.util.Format.htmlEncode(data.location);
		str += '<br />'+t("Location", "calendar")+': '+data.location;
	}

	if(!GO.util.empty(data.description))
	{
		str += '<br /><br /><span style="white-space: pre-wrap">'+data.description + '</span>';
		// str += '<br /><br />'+Ext.util.Format.htmlEncode(data.description);
	}


	return Ext.util.Format.htmlEncode(str);
}


Ext.extend(GO.summary.CalendarPortlet, Ext.grid.GridPanel, {


	afterRender : function()
	{
		GO.summary.CalendarPortlet.superclass.afterRender.call(this);

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
				go.Router.goto('calendarevent/' + record.data.id);
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
	if(go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("community", "calendar"))
	{
		var calGrid = new GO.summary.CalendarPortlet({
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
							url:'summary/calendar',
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
