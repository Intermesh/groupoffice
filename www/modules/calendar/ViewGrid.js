/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: ViewGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

 
GO.grid.ViewGrid = Ext.extend(Ext.Panel, {
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

	categories: '[]',

	//private var that is used when an event is dragged to another location
	dragEvent : false,
	
	//all the grid appointments are stored in this array. First index is day and second is the dom ID.
	appointments : Array(),
	
	//The remote database ID's can be stored in this array. Useful for database updates
	remoteEvents : Array(),
	
	//An object with the event_id as key and the value is an array with dom id's
	domIds : Array(),
	
	//amount of days to display
	days : 1,
	
	selected : Array(),
	
	view_id : 0,
	
	//a collection of all the gridcells
	gridCells : Array(),

	nextId : 0,

	periodDisplay : '',

	// private
	initComponent : function(){
		GO.grid.ViewGrid.superclass.initComponent.call(this);
	
		this.addEvents({
			/**
		     * @event click
		     * Fires when this button is clicked
		     * @param {Button} this
		     * @param {EventObject} e The click event
		     */
			"create" : true,
			/**
		     * @event toggle
		     * Fires when the "pressed" state of this button changes (only if enableToggle = true)
		     * @param {Button} this
		     * @param {Boolean} pressed
		     */
			"move" : true,
			"eventResize" : true,
			"eventDblClick" : true,
			"zoom" : true,
			"storeload": true
	
		});
	    
   

		if(!this.startDate)
		{
			//lose time
			var date = new Date();
			this.startDate=Date.parseDate(date.format(this.dateFormat), this.dateFormat);
		}
		
		this.configuredDate=this.startDate;
	},
    
	setViewId : function(view_id)
	{
		this.view_id=view_id;
	//this.load();
	},

	//build the html grid
	onRender : function(ct, position){
		
		GO.grid.ViewGrid.superclass.onRender.apply(this, arguments);
		
		//important to do here. Don't remember why :S
		this.setDate(this.startDate, false);
		

		
		//if this is not set the grid does not display well when I put a load mask on it.
		this.body.setStyle("overflow", "hidden");
		
		//Don't select things inside the grid
		this.body.unselectable();

		//this.renderViewView();
		
		this.initDD();
	},
	
	renderView : function()
	{
	
		this.body.update('');
        
		//get content size of element
		var ctSize = this.container.getSize(true);
		
		//column width is the container size minus the time column width
		var columnWidth = (ctSize['width']-150)/this.days;
        
		//generate table for headings and all day events
		this.headingsTable = Ext.DomHelper.append(this.body,
		{
			tag: 'table',
			id: Ext.id(),
			cls: "x-calGrid-headings-table",
			style: "width:"+ctSize['width']+"px;"
				
		},true);
			
		var tbody = Ext.DomHelper.append(this.headingsTable,
		{
			tag: 'tbody'
		}, true);
		this.headingsRow = Ext.DomHelper.append(tbody,
		{
			tag: 'tr',
			children:{
				tag:'td',
				style:'width:200px',
				cls: "x-calGrid-heading"
			}
		}, true);
			
			
		var yearPos = GO.settings.date_format.indexOf('Y');
		var dateFormat = 'D '+GO.settings.date_format.substring(0, yearPos-1);
		
		for(var day=0;day<this.days;day++)
		{	
			
			var dt = this.startDate.add(Date.DAY, day);
			//create grid heading
			var heading = Ext.DomHelper.append(this.headingsRow,
			{
				tag: 'td',
				cls: "x-calGrid-heading",
				style: "width:"+(columnWidth)+"px",
				html: dt.format(dateFormat)
			}
          );
		}
		

		//for the scrollbar
		Ext.DomHelper.append(this.headingsRow,
		{
			tag: 'td', 
			style: "width:"+(this.scrollOffset-3)+"px;height:0px",
			cls: "x-calGrid-heading"
		});
	
	
		//create container for the grid
		this.gridContainer = Ext.DomHelper.append(this.body,
		{
			tag: 'div',
			cls: "x-calGrid-grid-container"
		}, true);

		//calculate gridContainer size
		var headingsHeight = this.headingsTable.getHeight();

		var gridContainerHeight = ctSize['height']-headingsHeight;
		this.gridContainer.setSize(ctSize['width'],gridContainerHeight );
			
			
		
		this.gridTable = Ext.DomHelper.append(this.gridContainer,
		{
			tag: 'table', 
			id: Ext.id(), 
			cls: "x-viewGrid-table", 
			style: "width:"+ctSize['width']-this.scrollWidth+"px;"
			
		},true);
		
		this.tbody = Ext.DomHelper.append(this.gridTable,
		{
			tag: 'tbody'
		}, true);
		
		this.gridCells = {};
		
				
		//The keys of this array is jsons time_of_day value the value the language
//    var timeFormat = (GO.settings.time_format === 'g:i a') ? 'ga'  : GO.settings.time_format;
           
				
		var timeOfDay = {
							'allday': t("Day", "calendar"),
              'morning': t("Morning", "calendar"),
              'afternoon': t("Afternoon", "calendar"),
              'evening': t("Evening", "calendar")
            };
						
		//for(var calendar_id in this.jsonData)
		for(var i=0,max=this.jsonData.results.length;i<max;i++)
		{
			
			var gridRow =  Ext.DomHelper.append(this.tbody,
			{
					tag: 'tr',
					cls: 'x-viewGrid-row-allday'
			});

			var calendar_id=this.jsonData.results[i].view_calendar_id;
			
			var cell = Ext.DomHelper.append(gridRow, {
				tag: 'td', 
				cls: 'x-viewGrid-calendar-name-cell',
				rowspan: 4,
				style:'width:150px'
			}, true);	
            
			var link = Ext.DomHelper.append(cell, {
				tag: 'a', 
				id: 'view_cal_'+calendar_id,
				href:'#',
				cls:'normal-link',
				html:this.jsonData.results[i].view_calendar_name
			}, true);

			link.on('click', function(e, target){			
				e.preventDefault();
				var calendar_id = target.id.substring(9);

				var calendar =this.getCalendar(calendar_id);

				this.fireEvent('zoom', {
					group_id: calendar.group_id,
					calendar_id: calendar_id,
					calendar_name:target.innerHTML,
					title:target.innerHTML
				});
			}, this);
             
					

			this.gridCells[calendar_id]={};
            
			for(var time in timeOfDay) {
				if(time!='allday'){
					gridRow =  Ext.DomHelper.append(this.tbody,
					{
						tag: 'tr',
						cls: 'x-viewGrid-row-'+time
					});
				}

				var borderStyle = 'border:0; border-bottom:1px dashed #ddd; border-right:1px solid #ddd;';
				if(time === 'evening')
					borderStyle='border-top:0;';

				Ext.DomHelper.append(gridRow, {
					tag: 'td', 
					cls: 'x-viewGrid-calendar-name-cell',
					style:'width:25px; color: #666; padding: 2px; '+borderStyle,
					html: '<div style="height:20px">'+ timeOfDay[time]+'</div>'
				}, true);

				for(var day=0;day<this.days;day++)
				{	
					var dt = this.startDate.add(Date.DAY, day)

					this.gridCells[calendar_id][dt.format('Ymd')+time] = Ext.DomHelper.append(gridRow,{
						tag: 'td', 
						id: 'cal'+calendar_id+'_day'+dt.format('Ymd')+'_time'+time, 
						cls: 'x-viewGrid-cell x-viewGrid-cell-'+time,
						style:'width:'+columnWidth+'px; '+borderStyle
					}, true);
					
					
					this.gridCells[calendar_id][dt.format('Ymd')+time].on('click', this.onAddClick, this);

				}	
				
			}
		}
		
		
	},

	getCalendar : function(id){		
		for(var i=0;i<this.jsonData.results.length;i++){
			if(this.jsonData.results[i].calendar_id==id)
				return this.jsonData.results[i];
		}
		return false;
	},

	removeEventByRemoteId : function(remote_id){
		var domIds = this.getEventDomElements(remote_id);
		if(domIds){
			for(var i=0, max=domIds.length;i<max;i++){
				this.removeEvent(domIds[i]);
			}
		}
	},
	
	/*
	 * Removes a single event and it's associated dom elements
	 */
	removeEvent : function(domId){		
		var ids = this.getRelatedDomElements(domId);
		
		if(ids)
		{
			for(var i=0;i<ids.length;i++)
			{
				var el = Ext.get(ids[i]);
				if(el)
				{
					el.removeAllListeners();
					el.remove();
				}					
				this.unregisterDomId(ids[i]);
			}			
		}
	
		
	},
	
	unregisterDomId : function(domId)
	{
		delete this.remoteEvents[domId];
		
		var found = false;
		
		for(var e in this.domIds)
		{
			for(var i=0;i<this.domIds[e].length;i++)
			{
				if(this.domIds[e][i]==domId)
				{
					this.domIds[e].splice(i,1);
					found=true;
					break;
				}
			}
			if(found)
			{
				break;
			}
		}
		
	/*found=false;
		
		for(var e in this.eventIdToDomId)
		{
			for(var i=0;i<this.eventIdToDomId[e].length;i++)
			{
				if(this.eventIdToDomId[e][i]==domId)
				{
					this.eventIdToDomId[e].splice(i,1);
					found=true;
					break;
				}
			}
			if(found)
			{
				break;
			}
		}*/
	},
	
	setNewEventId : function(domIds, new_event_id){
		for(var i=0,max=domIds.length;i<max;i++){
			this.remoteEvents[domIds[i]].event_id=new_event_id;
		}
	},
  
	initDD :  function(){
		
		var dragZone = new GO.calendar.dd.ViewDragZone(this.body, {
			ddGroup: 'view-grid',
			scroll: false,
			viewGrid: this
		});
        
		var dropTarget = new GO.calendar.dd.ViewDropTarget(this.body, {
			ddGroup: 'view-grid',
			onNotifyDrop : function(dd, e, data) {
        		
				//number of seconds moved
				var dragTime = data.dragDate.format('U');
				var dropTime = data.dropDate.format('U');
	    		
				var offsetDays = Math.round((dropTime-dragTime)/86400);
	    		
				var actionData = {
					offsetDays:offsetDays,
					dragDate: data.dragDate,
					calendar_id: data.calendar_id
				};
	
				var remoteEvent = this.elementToEvent(data.item.id);
					
				if(remoteEvent['repeats'])
				{
					this.handleRecurringEvent("move", remoteEvent, actionData);
				}else
				{
					
						
					this.removeEvent(remoteEvent.domId);
					delete remoteEvent.domId;
					remoteEvent.repeats=false;
					remoteEvent.calendar_id=data.calendar_id;
					remoteEvent.startDate = remoteEvent.startDate.add(Date.DAY, offsetDays);
					remoteEvent.endDate = remoteEvent.endDate.add(Date.DAY, offsetDays);
					remoteEvent.start_time = remoteEvent.startDate.format('U');
					remoteEvent.end_time = remoteEvent.endDate.format('U');
					var domIds = this.addViewGridEvent(remoteEvent);

					this.fireEvent("move", this, remoteEvent, actionData, domIds);
				}
			},
			scope : this
		});
	},
	
	onResize : function(adjWidth, adjHeight, rawWidth, rawHeight){
		if(this.gridContainer)
		{
			this.gridContainer.setSize(adjWidth, adjHeight);
			this.headingsTable.setWidth(adjWidth);
			this.gridTable.setWidth(adjWidth);
		}

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
	
	mask : function()
	{
		if(this.rendered)
		{
			this.body.mask(t("Loading..."),'x-mask-loading');
		}
	},
	
	unmask : function()
	{
		if(this.rendered)
		{
			this.body.unmask();		
		}
	},
	
		


	
	getSelectedEvent : function()
	{
		if(this.selected)
		{
			return this.elementToEvent(this.selected[0].id);
		}
	},
	isSelected : function(eventEl)
	{
		for (var i=0;i<this.selected.length;i++)
		{
			if(this.selected[i].id==eventEl)
			{
				return true;
			}
		}
		return false;
	},
	
	clearSelection : function()
	{
		for (var i=0;i<this.selected.length;i++)
		{
			this.selected[i].removeClass('x-calGrid-selected');
		}
		this.selected=[];
	},
	
	selectEventElement : function(eventEl)
	{
		if(!this.isSelected(eventEl))
		{
			this.clearSelection();
			
			var elements = this.getRelatedDomElements(eventEl.id);
			
			for (var i=0;i<elements.length;i++)
			{			
				var element = Ext.get(elements[i]);
				if(element)
				{
					element.addClass('x-calGrid-selected');
					element.focus();
					this.selected.push(element);
				}
			}
		}

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

			this.contextMenu.on('updateEvent', function(obj, new_event_id, calendar_visible)
			{
				var event = obj.event;

				if(obj.isCopy)
				{
					if(calendar_visible)
					{
						if(event.repeats)
						{
							this.reload();
						}else
						{
							var newEvent = GO.util.clone(event);
							delete(newEvent.id);
							delete(newEvent.domId);

							newEvent.event_id = new_event_id;
							newEvent.startDate = Date.parseDate(newEvent.start_time, this.dateTimeFormat).add(Date.DAY, obj.offset);
							newEvent.endDate = Date.parseDate(newEvent.end_time, this.dateTimeFormat).add(Date.DAY, obj.offset);
							newEvent.start_time=newEvent.startDate.format(this.dateTimeFormat);
							newEvent.end_time=newEvent.endDate.format(this.dateTimeFormat);

							this.addViewGridEvent(newEvent);
							
						}
					}
				}else
				{
					if(obj.repeats)
					{
						this.reload();
					}else
					{
						this.removeEvent(event.domId);
						delete event.domId;

						if(calendar_visible)
						{
							event.startDate = Date.parseDate(event.start_time, this.dateTimeFormat).add(Date.DAY, obj.offset);
							event.endDate = Date.parseDate(event.end_time, this.dateTimeFormat).add(Date.DAY, obj.offset);
							event.start_time=event.startDate.format(this.dateTimeFormat);
							event.end_time=event.endDate.format(this.dateTimeFormat);

							this.addViewGridEvent(event);
						}
					}				
				}
			},this);
		}

		e.stopEvent();
		this.contextMenu.setParticipants(event.participant_ids);
		this.contextMenu.setEvent(event, this.view_id);
		this.contextMenu.showAt(e.getXY());
	},
	
	getTimeOfDay : function(eventData){

		var hour = eventData.startDate.format('G');
		var endhour = eventData.endDate.format('G');
		
		var date= eventData.startDate.format('Ymd');
		var enddate = eventData.endDate.format('Ymd');
		
		if(date!=enddate || endhour-hour>=6)
			return 'allday';
		else if(hour >= 0 && hour < 12)
			return "morning";
		else if(hour >= 12 && hour < 18)
			return "afternoon";
		else if(hour >= 18)
			return "evening";
	},
	
	addViewGridEvent : function (eventData)
	{
		if(eventData.id  == undefined)
		{
			eventData.id = this.nextId++;
		}		
		
		//the start of the day the event starts
		var eventStartDay = Date.parseDate(eventData.startDate.format('Ymd'),'Ymd');
		var eventEndDay = Date.parseDate(eventData.endDate.format('Ymd'),'Ymd');
		
		//get unix timestamps
		var eventStartTime = eventStartDay.format('U');
		var eventEndTime = eventEndDay.format('U');
			
		//ceil required because of DST changes!
		var daySpan = Math.round((eventEndTime-eventStartTime)/86400)+1;
		
		var domIds=[];;
		for(var i=0;i<daySpan;i++)
		{
			var date = eventStartDay.add(Date.DAY, i);
			
			
			var domId = eventData.domId ? eventData.domId : Ext.id();

			domIds.push(domId);
			
			//related events for dragging
			if(daySpan>1)
			{
				if(!this.domIds[eventData.id])
				{
					this.domIds[eventData.id]=[];
				}				
				this.domIds[eventData.id].push(domId);
			}

			// If the calendar_id is not given, then we cannot show the event/task in the calendar view
			if(eventData['calendar_id']){	
				var col = this.gridCells[eventData['calendar_id']][date.format('Ymd')+this.getTimeOfDay(eventData)];
			}
			
			if(col)
			{
				var text = '';
				if(eventData.startDate.format('G')!='0')
				{
					text += eventData.startDate.format(GO.settings.time_format)+'-'+eventData.endDate.format(GO.settings.time_format)+'&nbsp;';
				}				
				text += eventData['name'];
			
				var cls = "x-viewGrid-event-container  cal-event-partstatus-"+eventData.partstatus;

//				if(eventData.link_count>0){
//					cls +=' cal-has-links'
//				}

				if(eventData.link_count>0){
					text +='<span class="cal-has-links"></span>';
				}
				if (eventData["private_enabled"])
					text += '<span class="cal-is-private"></span>';
				if (eventData.has_reminder==1)
					text += '<span class="cal-has-reminders"></span>';
				if (eventData.repeats)
					text += '<span class="cal-recurring"></span>';

				if (!GO.util.empty(eventData.resources))
					text += '<span class="cal-resources"></span>';

				var event = Ext.DomHelper.append(col,
				{
					tag: 'div',
					id: domId,
					cls: cls,
					style:"background-color:#"+eventData.background,
					html: text,
					"ext:qtitle":Ext.util.Format.htmlEncode(eventData.name),
					"ext:qtip": GO.calendar.formatQtip(eventData),
					tabindex:0//tabindex is needed for focussing and events
				}, true);
					
				this.registerEvent(domId, eventData);
				
				
				
				event.on('click', function(e, eventEl){
				
					eventEl = Ext.get(eventEl).findParent('div.x-viewGrid-event-container', 2, true);
					
					this.selectEventElement(eventEl);					
					this.clickedEventId=eventEl.id;
					
					e.stopEvent();
		
				}, this);
				
				event.on('dblclick', function(e, eventEl){
					
					eventEl = Ext.get(eventEl).findParent('div.x-viewGrid-event-container', 2, true);
					
					//this.eventDoubleClicked=true;
					var event = this.elementToEvent(this.clickedEventId);
					
					if(event['repeats'] && event.permission_level>=GO.permissionLevels.write)
					{
						if(!event.read_only)
							this.handleRecurringEvent("eventDblClick", event, {});
					}else
					{
						
						this.fireEvent("eventDblClick", this, event, {
							singleInstance : event.permission_level>=GO.permissionLevels.write
						});
					}
					
					e.stopEvent();
					
				}, this);

				event.on('contextmenu', function(e, eventEl)
				{										
					var event = this.elementToEvent(this.clickedEventId);
					this.showContextMenu(e, event);
				}, this);
			}
		}
		
		return domIds;
	},
	
	onAddClick : function(e, target){

		var dateAndTime = target.id.split('_');

		//ID format: cal1_day20130318_timeallday
		var dateStr = dateAndTime[1].substring(3, dateAndTime[1].length);
		var timeOfDay = dateAndTime[2].substring(4, dateAndTime[2].length);
		var date = Date.parseDate(dateStr,'Ymd');
		var calendar_id = parseInt(dateAndTime[0].substring(3, dateAndTime[0].length));
		if(date){ // in firefox this event somehow also fires on events
			this.fireEvent('create', this, date, timeOfDay, calendar_id);
			e.stopEvent();
		}
	},

	removeEventFromArray : function (day, event_id)
	{
		for(var i=0;i<this.appointments[day].length;i++)
		{
			if(this.appointments[day][i].id==event_id)
			{
				return this.appointments[day].splice(i,1);				
			}
		}
		return false;
	},

	inAppointmentsArray : function (id, appointments)
	{
		for(var i=0;i<appointments.length;i++)
		{
			if(appointments[i].id==id)
			{
				return true;
			}
		}
		return false;
	},


	
	handleRecurringEvent : function(fireEvent, event, actionData){
		
		//store them here so the already created window can use these values
		this.currentRecurringEvent = event;
		this.currentFireEvent=fireEvent;
		this.currentActionData = actionData;
		
		if(!this.recurrenceDialog)
		{
			this.recurrenceDialog = new GO.calendar.RecurrenceDialog();

			this.recurrenceDialog.on('single', function()
			{
				this.currentActionData.singleInstance=true;

				var remoteEvent = this.currentRecurringEvent;
				var newEvent = GO.util.clone(remoteEvent);
				
				var domIds=[];

				if(this.currentActionData.offsetDays)
				{
					this.removeEvent(remoteEvent.domId);
					newEvent.calendar_id=this.currentActionData.calendar_id;
					newEvent.repeats=false;
					newEvent.startDate = newEvent.startDate.add(Date.DAY, this.currentActionData.offsetDays);
					newEvent.endDate = newEvent.endDate.add(Date.DAY, this.currentActionData.offsetDays);
					newEvent.start_time = newEvent.startDate.format('U');
					newEvent.end_time = newEvent.endDate.format('U');
					this.addViewGridEvent(newEvent);
				}

				this.fireEvent(this.currentFireEvent, this, remoteEvent , this.currentActionData, domIds);
		
				this.recurrenceDialog.hide();
			},this)

			this.recurrenceDialog.on('thisandfuture', function()
			{
				this.currentActionData.singleInstance= true;
				this.currentActionData.thisAndFuture = true;

				this.fireEvent(this.currentFireEvent, this, this.currentRecurringEvent, this.currentActionData);
				this.recurrenceDialog.hide();
			},this)

			this.recurrenceDialog.on('entire', function()
			{
				this.currentActionData.singleInstance=false;

				this.fireEvent(this.currentFireEvent, this, this.currentRecurringEvent, this.currentActionData);
				this.recurrenceDialog.hide();
			},this)

			this.recurrenceDialog.on('cancel', function()
			{
				this.recurrenceDialog.hide();
				this.reload(); 
			},this)
			
		}

		this.recurrenceDialog.thisAndFutureButton.setDisabled(this.currentRecurringEvent.recurring_start_time == this.currentRecurringEvent.start_time);

		this.recurrenceDialog.show();

		
	},


    
    
	clearGrid : function()
	{
		this.appointments=Array();		
		this.remoteEvents=Array();
		this.domIds=Array();
	},	
	
	setDays : function(days, load)
	{
		this.setDate(this.configuredDate, days, load);		
	},

	setDate : function(date, days, load)
	{  	
		if(days)
		{
			this.days=days;
		}
  	
		this.configuredDate = date;    	

		if(this.days>4)
		{
			this.startDate = this.getFirstDateOfWeek(date);
		}else
		{
			this.startDate = date;
		}

	    	
		this.endDate = this.startDate.add(Date.DAY, this.days);

		this.periodDisplay = t("Week")+' '+this.startDate.format('W');

		if(load)
			this.reload(); 
	},

	nextDate : function(){
		return this.startDate.add(Date.DAY, this.days>4 ? 7 : 1);
	},

	previousDate : function(){
		return this.startDate.add(Date.DAY, this.days>4 ? -7 : -1);
	},
  
	reload : function()
	{
		this.load();  	
	},
  
	load : function(params)
	{
  	
		if(!params)
		{
			params={};
		}

		params['view_id']=this.view_id;
		params['start_time']=this.startDate.format(this.dateTimeFormat);
		params['end_time']=this.endDate.format(this.dateTimeFormat);

		params['categories'] = this.categories;

		GO.request({
			maskEl:this.body,
			url: "calendar/event/viewStore",
			params: params,
			success: function(options, response, result)
			{								
				this.jsonData = result;

				this.clearGrid();

				this.renderView();


				var total=0;
				var mtime=0;
				//for(var calendar_id in this.jsonData)
				for(var n=0;n<this.jsonData.results.length;n++)
				{
					var events = this.jsonData.results[n].results;

					total += events.length;
					for(var i=0; i< events.length;i++)
					{
						var eventData = events[i];
						eventData['startDate'] = Date.parseDate(events[i]['start_time'], this.dateTimeFormat);
						eventData['endDate'] = Date.parseDate(events[i]['end_time'], this.dateTimeFormat);

						this.addViewGridEvent(eventData);

						if(eventData['mtime'] > mtime)
						{
							mtime = eventData['mtime'];
						}
					}
				}

				this.nextId = total;					

				this.fireEvent("storeload", this, total, mtime, params, response);

			},
			scope:this		
		});
	},
	/**
   * An array of domId=>database ID should be kept so that we can figure out
   * which event to update when it's modified.
   * @param {String} domId The unique DOM id of the element
   * @param {String} remoteId The unique database id of the element     
   * @return void
   */
	registerEvent : function(domId, eventData)
	{
		this.remoteEvents[domId]=eventData;
  	
	/*if(!this.domIds[eventData.event_id])
		{
			this.domIds[eventData.event_id]=[];
		}
	
		this.domIds[eventData.event_id].push(domId);*/
	},
  
	getEventDomElements : function(id)
	{
		return GO.util.clone(this.domIds[id]);
	},
  
	getRelatedDomElements : function(eventDomId)
	{
		var eventData = this.remoteEvents[eventDomId];
  	
		if(!eventData)
		{
			return false;
		}
		var domElements = this.getEventDomElements(eventData.id);
  	
		if(!domElements)
		{
			domElements = [eventDomId];
		}
		return domElements;
	},
  
	elementToEvent : function(elementId, allDay)
	{
		this.remoteEvents[elementId].domId=elementId;
		return this.remoteEvents[elementId];
	}/*,

    // private
    destroy : function(){
    	
    	this.store.un("beforeload", this.reload, this);
        this.store.un("datachanged", this.reload, this);
        this.store.un("clear", this.reload, this);
        
        this.el.update('');
		
		GO.grid.CalendarGrid.superclass.destroy.call(this);
		
		delete this.el;
		this.rendered=false;
		
    }*/

});


GO.calendar.dd.ViewDragZone = function(el, config) {
	config = config || {};
	Ext.apply(config, {
		ddel: document.createElement('div')
	});
	GO.calendar.dd.ViewDragZone.superclass.constructor.call(this, el, config);
};
 
Ext.extend(GO.calendar.dd.ViewDragZone, Ext.dd.DragZone, {
	onInitDrag: function(e) {
		this.ddel.innerHTML = this.dragData.item.dom.innerHTML;
		this.ddel.className = this.dragData.item.dom.className;
		this.ddel.style.width = this.dragData.item.getWidth() + "px";
		this.proxy.update(this.ddel);
	    
		this.eventDomElements = this.viewGrid.getRelatedDomElements(this.dragData.item.id);
	    
		//var td = Ext.get(this.dragData.item).findParent('td', 10, true);
	    
		//this.proxyCount = eventDomElements.length;
	    
		this.eventProxies=[];
		this.proxyDragPos = 0;
		for(var i=0;i<this.eventDomElements.length;i++)
		{
			this.eventProxies.push(Ext.DomHelper.append(document.body,
			{
				tag: 'div',
				id: Ext.id(),
				cls: "x-viewGrid-event-proxy",
				style: "width:"+this.ddel.style.width+"px;"
			},true));
			
			if (this.eventDomElements[i]==this.dragData.item.id)
			{
				this.proxyDragPos=i;
			}else
			{
				//hide event element
				var el = Ext.get(this.eventDomElements[i]);
				if(el)
					el.setStyle({
						'position' : 'absolute',
						'top':-10000,
						'display':'none'
					});
			}
		}
	},
	
	removeEventProxies : function(){
		var proxies = Ext.query('div.x-viewGrid-event-proxy');
		for (var i=0;i<proxies.length;i++)
		{
			Ext.get(proxies[i]).remove();
		}
		
		delete this.lastTdOverId;		
		
		//unhide event elements
		for(var i=0;i<this.eventDomElements.length;i++)
		{
			var el = Ext.get(this.eventDomElements[i]);
			if(el)
				el.setStyle({
					'position' : 'static',
					'top': '',
					'display':'block'
				});
		}
	},
	
	afterRepair : function(){
		GO.calendar.dd.ViewDragZone.superclass.afterRepair.call(this);
		
		this.removeEventProxies();
		
	},
	getRepairXY: function(e, data) {
		data.item.highlight('#e8edff');
		return data.item.getXY();
	},
	getDragData: function(e) {
		var target = Ext.get(e.getTarget());
    
		if(target.hasClass('x-viewGrid-event-container'))
		{
			var td = target.parent();
	    
			var dateIndex = td.id.indexOf('_day')+4;
			var calendar_id = td.id.substr(3,dateIndex-7);
			var calendar = this.viewGrid.getCalendar(calendar_id);
            
            var event = this.viewGrid.remoteEvents[target.id];
			
			if(!event['private'] && calendar.write_permission)
			{
				var dateStr = td.id.substr(dateIndex,8);
				var dragDate = Date.parseDate(dateStr,'Ymd');
		    
	     
				return {
					ddel:this.ddel,
					item:target, //DOM node
                    event:event, //Event properties
					dragDate: dragDate
				};
			}
			return false;
		}
            
	}
});


GO.calendar.dd.ViewDropTarget = function(el, config) {
	GO.calendar.dd.ViewDropTarget.superclass.constructor.call(this, el, config);
};
Ext.extend(GO.calendar.dd.ViewDropTarget, Ext.dd.DropTarget, {
	notifyDrop: function(dd, e, data) {
 		
		var td = Ext.get(e.getTarget()).findParent('td', 10, true);
		if(!td)
		{
			return false;
		}
		var dateIndex = td.id.indexOf('_day')+4;
	 		 
		var calendar_id = td.id.substr(3,dateIndex-7);
		var calendar = this.scope.getCalendar(calendar_id);
		    
		if(!calendar || !calendar.write_permission)
		{
			return false;
		}
	 		
	        
		var dateStr = td.id.substr(dateIndex,8);
		data.dropDate = Date.parseDate(dateStr,'Ymd');
	    
		data.calendar_id=td.id.substr(3,dateIndex-7);
	
		dd.removeEventProxies();
	 		   	
		this.el.removeClass(this.overClass);
		td.appendChild(data.item);
	    
	    
		if(this.onNotifyDrop)
		{
			if(!this.scope)
			{
				this.scope=this;
			}
				
			var onNotifyDrop = this.onNotifyDrop.createDelegate(this.scope);
			onNotifyDrop.call(this, dd, e, data);
		}
		return true;
	},
    
	notifyOver : function(dd, e, data){
		var tdOver = Ext.get(e.getTarget()).findParent('td.x-viewGrid-cell-'+this.scope.getTimeOfDay(data.event), 10, true);
         
		if(tdOver)
		{
			var dateIndex = tdOver.id.indexOf('_day');
			var calendar_id = tdOver.id.substr(3,dateIndex-3);

			var calendar = this.scope.getCalendar(calendar_id);
                        
			if(calendar && calendar.write_permission)
			{
				if(dd.lastTdOverId!=tdOver.id)
				{
					var currentTd=tdOver;
					for(var i=0;i<dd.proxyDragPos;i++)
					{
						if(currentTd)
						{
							var nextTd = currentTd.prev('td.x-viewGrid-cell-'+this.scope.getTimeOfDay(data.event));
							currentTd = nextTd;
						}
						if(nextTd)
						{
							//dd.eventProxies[i].insertAfter(nextTd.first());
							nextTd.insertFirst(dd.eventProxies[i].id);
							dd.eventProxies[i].setStyle({
								'position' : 'static',
								'top': '',
								'display':'block'
							});
						}else
						{
							dd.eventProxies[i].setStyle({
								'position' : 'absolute',
								'top':-10000,
								'display':'none'
							});
						}
					}
		        	
					tdOver.insertFirst(dd.eventProxies[i]);
					//dd.eventProxies[i].insertAfter(tdOver.first());
					var currentTd=tdOver;
					for(var i=dd.proxyDragPos+1;i<dd.eventProxies.length;i++)
					{
						if(currentTd)
						{
							var nextTd = currentTd.next('td.x-viewGrid-cell-'+this.scope.getTimeOfDay(data.event));
							currentTd = nextTd;
						}
		        		
						if(nextTd)
						{
							//dd.eventProxies[i].insertAfter(nextTd.first());
							nextTd.insertFirst(dd.eventProxies[i].id);
		        			 			
							dd.eventProxies[i].setStyle({
								'position' : 'static',
								'top': '',
								'display':'block'
							});
						}else
						{
							dd.eventProxies[i].setStyle({
								'position' : 'absolute',
								'top':-10000,
								'display':'none'
							});
						}
					}
		        	
				}
		        
				dd.lastTdOverId=tdOver.id;
				return this.dropAllowed;
			}
		}
		return false;
	}
    
});
