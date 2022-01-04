/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ListGrid.js 22335 2018-02-06 16:25:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.ListGrid = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	config.store = new Ext.data.GroupingStore({
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
				'recurrence_start_time',
			'description',
			'location',
			'private',
			'repeats',
			'background',
			'status_color',
			'day',
			'task_id',
			'contact_id',
			'link_count',
			'has_reminder',
			'calendar_id',
			'calendar_name',
			'read_only',
//			'has_other_participants',
			'participant_ids',
			'permission_level',
			'ctime',
			'mtime',
			'username',
			'user_id',
			'is_organizer',
			'musername',
			'resources',
			'model_name'
			]
		}),
		proxy: new Ext.data.HttpProxy({
			url:GO.url('calendar/event/store')
		}),
		groupField:'day',
		sortInfo: {
			field: 'start_time',
			direction: 'ASC'
		},
		remoteSort:true
	});
	
	config.paging=false;
	config.autoExpandColumn='listview-calendar-name-heading';
	config.autoExpandMax=2500;
	config.enableColumnHide=false;
	config.enableColumnMove=false;
	config.autoScroll=true;
  
	config.columns=[
	{
		header:t("Day"),
		dataIndex: 'day',
		menuDisabled:true
	},
	{
		header:t("Time"),
		dataIndex: 'time',
		width:90,
		renderer: function(v, metadata, record)
		{
			var html = '';
			
			//TODO: Set the correct background color for the following span block. The background-color depends on the status of the event.
//			if(!GO.util.empty(record.data.status_color))
//				html += '<span class="x-calListGrid-event-status" style="background-color:#'+record.data.status_color+';"></span>';
			
			html += '<div class="';
			if(record.data.link_count>0)
			{
				html +='cal-has-links';
			}
			if (record.data.private) {
				v = v+'<span class="cal-is-private"></span>';
			}
			if (record.data.has_reminder==1) {
				v = v+'<span class="cal-has-reminders"></span>';
			}

			html +='" style="background-position:1px 3px !important;border:1px solid #c0c0c0;padding:2px;margin:2px;background-color:#'+record.data.background+';">'+v+'</div>';
			return html;
		},
		menuDisabled:true
	},	
	{
		id:'listview-calendar-name-heading',
		header:t("Name"),
		dataIndex: 'name',
		renderer: this.renderName,
		menuDisabled:true
	}];
		
	config.view=  new Ext.grid.GroupingView({
		hideGroupedColumn:true,
		groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'+t("items")+'" : "'+t("item")+'"]})',
		emptyText: t("No appointments to display", "calendar"),
		showGroupName:false
	});
	config.sm=new Ext.grid.RowSelectionModel({
		singleSelect:true
	});
	config.loadMask=true;
	
	GO.calendar.ListGrid.superclass.constructor.call(this, config);
	
	if(!this.startDate)
	{
		//lose time
		var date = new Date();
		this.startDate=Date.parseDate(date.format(this.dateFormat), this.dateFormat);
	}
	
	
	this.setDate(this.startDate);
	
};


Ext.extend(GO.calendar.ListGrid, Ext.grid.GridPanel, {
	
	/**
   * @cfg {String} The components handles dates in this format
   */
	dateFormat : 'Y-m-d',
	/**
   * @cfg {String} The components handles dates in this format
   */
	dateTimeFormat : 'Y-m-d H:i',
	
	timeFormat : 'H:i',
	/**
   * @cfg {Number} Start day of the week. Monday or sunday
   */
	firstWeekday : 1,
	/**
   * @cfg {Date} The date set by the user
   */
	configuredDate : false,
	/**
   * @cfg {Date} The date where the grid starts. This can be recalculated after a user sets a date
   */
	startDate : false,
	
	/**
   * @cfg {Integer} amount of days to display
   */
	days : 91,

	nextId : 0,

	periodDisplay : '',
	
	renderName : function(grid, value, record)
	{
			return '<div style="font-weight:bold;" ext:qtip="'+GO.calendar.formatQtip(record.data)+'">'+record.data.name+'</div>'+GO.calendar.formatQtip(record.data,false);
	},
		
	afterRender : function()
	{
		GO.calendar.ListGrid.superclass.afterRender.call(this);
    
		/*GO.calendar.eventDialog.on('save', function(){
    	if(this.isVisible())
    	{
    		this.store.reload();
    	}    	
    }, this);*/
    
		this.on("rowdblclick", function(grid, rowIndex, e){
			var record = grid.getStore().getAt(rowIndex);

			if(record.data.read_only)
					return false;

			if(record.data.event_id)
			{
				GO.calendar.showEventDialog({
					event_id: record.data.event_id
				});
				
			}else if(record.data.task_id)
			{
				GO.tasks.showTaskDialog({
					task_id : record.data.task_id
				});
			}else	if(record.data.contact_id)
			{
				go.Router.goto("#contact/"+record.data.contact_id);
			}
			
		}, this);
		
		this.on('rowcontextmenu', function(grid, rowIndex, e)
		{			
			var sm = grid.getSelectionModel();
			if(sm.isSelected(rowIndex) !== true) {
				sm.clearSelections();
				sm.selectRow(rowIndex);
			}

			var theEventData = grid.getStore().getAt(rowIndex).data;
			console.log(theEventData);
			if (theEventData.model_name=='GO\\Tasks\\Model\\Task') {
				if(go.Modules.isAvailable("legacy", "tasks")) {
					if (!this.taskContextMenu)
						this.taskContextMenu = new GO.calendar.TaskContextMenu();

					e.stopEvent();
					this.taskContextMenu.setTask(theEventData);
					this.taskContextMenu.showAt(e.getXY());
				}
			} else {
				this.showContextMenu(e, theEventData);
			}
		}, this);
		
	},
	
	getFirstDateOfWeek : function(date)
	{
		//Calculate the first day of the week
		var weekday = date.getDay();
		var offset = this.firstWeekday-weekday;
		if(offset>0)
		{
			offset-=7;
		}
		return date.add(Date.DAY, offset);
	},
	setDays : function(days, load)
	{
		this.setDate(this.configuredDate, 7, load);
	},
	
	getSelectedEvent : function(){
		
		var sm = this.getSelectionModel();
		var record = sm.getSelected();
		var event = record.data;
		
		event.startDate = Date.parseDate(event.start_time, this.dateTimeFormat);
		event.endDate = Date.parseDate(event.end_time, this.dateTimeFormat);
		
		return event;
	},
	
	removeEvent : function(){
		var sm = this.getSelectionModel();
		var record = sm.getSelected();
		this.store.remove(record)
	},
	
	setDate : function(date, days, load)
	{
		this.configuredDate = date;

		/*if(this.days>4)
		{
			this.startDate = this.getFirstDateOfWeek(date);
		}else
		{
			this.startDate = date;
		}*/

		var dateStr='';
		
		var year = date.getFullYear();

		if(date.getMonth()>8){
			dateStr=date.getFullYear()+'-10-01';
			this.periodDisplay = '4 ';
		}else if(date.getMonth()>5){
			dateStr=year+'-07-01';
			this.periodDisplay = '3 ';
		}else if(date.getMonth()>2){
			dateStr=year+'-04-01';
			this.periodDisplay = '2 ';
		}else
		{
			dateStr=year+'-01-01';
			this.periodDisplay = '1 ';
		}

		this.periodDisplay = t("Q", "calendar")+this.periodDisplay+year;
		
		this.startDate=Date.parseDate(dateStr, this.dateFormat);
		this.endDate = this.nextDate();		
		this.setStoreBaseParams();
  	
		if(load)
			this.store.reload();		
	},

	nextDate : function(){
		return this.startDate.add(Date.MONTH, 3);
	},

	previousDate : function(){
		return this.startDate.add(Date.MONTH, -3);
	},

	setStoreBaseParams : function(){
		this.store.baseParams['start_time']=this.startDate.format(this.dateTimeFormat);
		this.store.baseParams['end_time']=this.endDate.format(this.dateTimeFormat);
	},

	showContextMenu : function(e, event)
	{			
		if(!this.contextMenu)
		{
			this.contextMenu = new GO.calendar.ContextMenu();

			this.contextMenu.on('deleteEvent', function()
			{
				this.fireEvent("deleteEvent", this);
			},this);			
			
			this.contextMenu.on('updateEvent', function(obj)
			{
				var calendars = Ext.decode(this.store.baseParams['calendars']);
				
				if(!obj.isCopy) {
					this.store.reload();
				} else {
					for(var i=0,found=false; i<calendars.length && !found; i++) {
						if(calendars[i] == obj.event.calendar_id) {
							found = true;
							this.store.reload();
						}
					}
				}
			},this);
		}		

		e.stopEvent();
		this.contextMenu.setEvent(event);
		//this.contextMenu.setParticipants(event.participant_ids);
		this.contextMenu.showAt(e.getXY());
	}
	
});

	

