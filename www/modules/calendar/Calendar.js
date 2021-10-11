/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: Calendar.js 22335 2018-02-06 16:25:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

Ext.ns("GO.calendar.dd");
 
GO.calendar.formatQtip = function(data,verbose)
{
	if (typeof(verbose)=='undefined')
		verbose = true;
	
	var df = 'Y-m-d H:i';
	
	if(!data.startDate)
		data.startDate = Date.parseDate(data.start_time, df);
	
	if(!data.endDate)
		data.endDate = Date.parseDate(data.end_time, df);
	
	if(!data.creationDate)
		data.creationDate = data.ctime ? Date.parseDate(data.ctime, df) : new Date();
	if(!data.modifyDate)
		data.modifyDate = data.mtime ? Date.parseDate(data.mtime, df) : new Date();
	
	var new_df = GO.settings.time_format;
	if(data.startDate.format('Ymd')!=data.endDate.format('Ymd'))
	{
		new_df = GO.settings.date_format+' '+GO.settings.time_format;
	}

	var str = t("Starts at", "calendar")+': '+data.startDate.format(new_df)+'<br />'+
	t("Ends at", "calendar")+': '+data.endDate.format(new_df);

	if(!GO.util.empty(data.duration))
		str += '<br />'+t("Timespan", "calendar")+': '+data.duration;

	if(!GO.util.empty(data.status))
	{
		str += '<br />'+t("Status", "calendar")+': ';
		
		if(t("statuses", "calendar")[data.status]){
			str+=Ext.util.Format.htmlEncode(t("statuses", "calendar")[data.status]);
		}else
		{
			str+=data.status;
		}
	}

	if(!GO.util.empty(data.calendar_name))
	{
		str += '<br />'+t("Calendar", "calendar")+': '+Ext.util.Format.htmlEncode(data.calendar_name);
	}

	if(!GO.util.empty(data.username))
	{
		str += '<br />'+t("Owner")+': '+Ext.util.Format.htmlEncode(data.username);
	}

	str += '<br />'+t("Created at")+': '+data.creationDate.format(GO.settings.date_format+' '+GO.settings.time_format);
	if (verbose)
		str += '<br />'+t("Modified at")+': '+data.modifyDate.format(GO.settings.date_format+' '+GO.settings.time_format);
		
	if(verbose && !GO.util.empty(data.musername))
	{
		str += '<br />'+t("Modified by")+': '+Ext.util.Format.htmlEncode(data.musername);
	}
	
	if(!GO.util.empty(data.location))
	{
		str += '<br />'+t("Location", "calendar")+': '+Ext.util.Format.htmlEncode(data.location);
	}
	
	if(!GO.util.empty(data.description))
	{
		str += '<br /><br />'+GO.util.nl2br(data.description);
	}
	if (!GO.util.empty(data.resources)) {
		str += '<br />'+t("Used resources", "calendar")+':';
		for (var i in data.resources)
			str += '<br /> - '+Ext.util.Format.htmlEncode(data.resources[i]);
	}
	
	if (!GO.util.empty(data.resourced_calendar_name))
		str += '<br />'+t("Resource used in", "calendar")+': '+Ext.util.Format.htmlEncode(data.resourced_calendar_name);
	
	return str;
//	return Ext.util.Format.htmlEncode(str);
}

GO.calendar.MainPanel = function(config){
	
	if(!config)
	{
		config = {};
	}

	this.datePicker = new Ext.DatePicker({
		flex: 1,
		cls:'cal-date-picker',
		showToday:false,
		internalRender:true,
		showPrevMonth : function() {
			this.update(this.activeDate.add('mo', -1));
			// Do not show a selection when calendar updates
			for(var i = 0; i < 42; i++) {
				this.cells.elements[i].classList.remove('x-date-selected');
			}
		},
		showNextMonth: function() {
			this.update(this.activeDate.add('mo', 1));
			for(var i = 0; i < 42; i++) {
				this.cells.elements[i].classList.remove('x-date-selected');
			}
		}
	});
	
	this.datePicker.on("select", function(datePicker, DateObj){
		this.setDisplay({
			date: DateObj
		});
	},this);
		
	GO.calendar.calendarsStore = this.calendarsStore = new GO.data.JsonStore({
		url:GO.url("calendar/calendar/store"),
		fields:['id','name','comment','user_name','group_id', 'group_name','checked', 'project_id','tooltip'],
		remoteSort:true
	});


	this.viewsStore = new GO.data.JsonStore({
		url: GO.url('calendar/view/store'),
		baseParams: {
			limit:parseInt(GO.settings['max_rows_list'])
		},
		fields:['id','name','user_name','merge','owncolor'],
		remoteSort:true
	});

	GO.calendar.resourcesStore = this.resourcesStore = new Ext.data.GroupingStore({
		baseParams: {			
			resourcesOnly : 1,
			limit:parseInt(GO.settings['max_rows_list'])
		},
		reader: new Ext.data.JsonReader({
			root: 'results',
			id: 'id',
			totalProperty: 'total',
			fields:['id','name','comment','user_name','group_id', 'group_name','tooltip']
		}),
		proxy: new Ext.data.HttpProxy({
			url: GO.url("calendar/calendar/calendarsWithGroup")
		}),
		sortInfo:{
			field: 'name',
			direction: "ASC"
		},
		groupField:'group_name'
//		,remoteSort:true
	});

	this.calendarsStore.on('load', function(){
		if(this.state.displayType!='view' && this.group_id==1) {
			this.state.applyFilter=true;
			this.setDisplay(this.state);
		}
	}, this);

	this.viewsStore.on('load', function(){
		this.viewsList.setVisible(this.viewsStore.data.length);
		this.calendarListPanel.doLayout();
		
		if(this.state.displayType=='view' && this.viewsStore.data.length)
		{
			var displayConfig = {'view_id':this.state.view_id};
			this.setDisplay(displayConfig);
		}
	}, this);

	this.resourcesStore.on('load', function(){

		this.resourcesList.setVisible(this.resourcesStore.data.length);
		this.calendarListPanel.doLayout();

		if(this.state.displayType!='view' && this.group_id>1 && this.resourcesStore.data.length)
		{
			this.setDisplay(this.state);
		}
	}, this);

	

	this.calendarList = new GO.grid.MultiSelectGrid({
		title:t("Calendars", "calendar"),
		store: this.calendarsStore,
		allowNoSelection:true,
		tools: [{
			id:'home',
			qtip: t("My calendar"),
			handler : function() {
				this.setDisplay({
					group_id: 1,
					project_id:0,
					applyFilter:true,
					calendars: [GO.calendar.defaultCalendar['id']]
				});
			},
			scope : this
		},{
			text:t("colors", "calendar"),
			id:'gear',
			qtip:t("Calendar color", "calendar"),
			handler:function(){
				if(!GO.calendar.colorPickerDialog){
					GO.calendar.colorPickerDialog = new GO.calendar.ColorPickerDialog();
				}
				GO.calendar.colorPickerDialog.show();
				GO.calendar.colorPickerDialog.on("hide", function(){
					this.refresh();
				},this);
			},
			scope: this
		},{
			text:t("Select all"),
			id:'plus',
			qtip:t("Select all"),
			handler:function(){this.calendarList.selectAll();},
			scope: this
		}],
		bbar: new GO.SmallPagingToolbar({
			store:this.calendarsStore,
			pageSize:GO.settings.config.nav_page_size
		})
	});

	this.calendarList.getBottomToolbar().add('->');
	this.calendarList.getBottomToolbar().add({
		xtype: 'tbsearch',
		store: this.calendarsStore
	});

	this.viewsList = new GO.grid.GridPanel({
		border: false,
		layout:'fit',
		title:t("Views", "calendar"),
		store: this.viewsStore,
		cls: 'go-grid3-hide-headers',
		autoScroll:true,
		columns:[{
			header:t("Name"),
			dataIndex: 'name',
			id:'name',
			width:188
		}],
		view:new Ext.grid.GridView({
			forceFit:true,
			autoFill:true
		}),
		sm: new Ext.grid.RowSelectionModel({singleSelect:true})
	});
	
	this.resourcesList = new GO.grid.GridPanel({
		border: false,
		title:t("Resources", "calendar"),
		layout:'fit',
		store: this.resourcesStore,
		cls: 'go-grid3-hide-headers',
		autoScroll:true,
    paging:GO.settings.config.nav_page_size,
    bbar: new GO.SmallPagingToolbar({
			store:this.resourcesStore,
			pageSize:GO.settings.config.nav_page_size
		}),
		columns:[{
			header:t("Name"),
			dataIndex: 'name',
			id:'name',
			width:188,
			renderer:function(value, p, record){
				if(!GO.util.empty(record.data.tooltip)) {
					p.attr = 'ext:qtip="'+Ext.util.Format.htmlEncode(record.data.tooltip)+'"';
				}
				return value;
			}
		},{
			header:t("Group", "calendar"),
			dataIndex: 'group_name',
			id:'group_name',
			width:188,
			renderer:function(value, p, record){
				if(!GO.util.empty(record.data.tooltip)) {
					p.attr = 'ext:qtip="'+Ext.util.Format.htmlEncode(record.data.tooltip)+'"';
				} else {
					p.attr = 'ext:qtip="'+Ext.util.Format.htmlEncode(record.data.name)+'"';
				}
				return value;
			}
		}],
		view: new Ext.grid.GroupingView({
			forceFit:true,
			hideGroupedColumn:true,
			groupTextTpl: '{text} ({[values.rs.length]})'
		}),
		sm: new Ext.grid.RowSelectionModel({singleSelect:true})
	});

	var changeCalendar = function(grid, calendars, records)
	{
		if(records.length){
			var cal_ids = [];

			for (var i=0,max=records.length;i<max;i++) {
				cal_ids[i] = records[i].data.id;
			}

			var config = {
				calendars: cal_ids,
				group_id:1,
				merge:true,
				owncolor:true,
				project_id:records[0].data.project_id
			};

			this.setDisplay(config);
		}

	}
   
	this.calendarList.on('change', changeCalendar, this);

	if(this.projectCalendarsList)
		this.projectCalendarsList.on('change', changeCalendar, this);
	
	this.viewsList.on('rowclick', function(grid, rowIndex)
	{
		this.setDisplay({
				view_id: grid.store.data.items[rowIndex].id	
			});
	}, this);	

	this.resourcesList.on('rowclick', function(grid, rowIndex)
	{
        
		this.setDisplay({
			calendars: [grid.store.data.items[rowIndex].id],
			group_id: grid.store.data.items[rowIndex].data.group_id
		});		
	}, this);

	this.calendarListPanel = new Ext.Panel({
		region:'center',
		layoutConfig:{hideCollapseTool:true},
		layout:'accordion',
		items: [
		this.calendarList
		]
	});

	if(this.projectCalendarsList)
		this.calendarListPanel.add(this.projectCalendarsList);

	this.calendarListPanel.add(this.viewsList);
	this.calendarListPanel.add(this.resourcesList);
	
	var storeFields=['id','event_id','name','start_time','end_time', 'recurring_start_time', 'description', 'repeats', 'private','private_enabled','status','location', 'background', 'status_color', 'read_only','is_virtual', 'task_id', 'contact_id','calendar_name','calendar_id','all_day_event','username','duration', 'link_count','has_reminder', 'has_other_participants','participant_ids','ctime','mtime','musername', 'is_organizer', 'partstatus','model_name','permission_level','resources','resourced_calendar_name'];

	this.daysGridStore = new GO.data.JsonStore({
		url:GO.url('calendar/event/store'),
		root: 'results',
		id: 'id',
		fields:storeFields
	});
	
	this.daysGridStore.on('load', this.setCalendarBackgroundColors, this);
	
	this.monthGridStore = new GO.data.JsonStore({
		url:GO.url('calendar/event/store'),
		fields:storeFields
	});
	
	this.monthGridStore.on('load', this.setCalendarBackgroundColors, this);

	GO.calendar.daysGrid = this.daysGrid = new GO.grid.CalendarGrid(
	{
		id: 'days-grid',
		store: this.daysGridStore, 
		border: false,
		firstWeekday: parseInt(GO.settings.first_weekday),
		keys:[ {
				key:  Ext.EventObject.DELETE,
				fn: function(){
					this.deleteHandler();
				},
				scope: this
		}]
	});	
	
	this.monthGrid = new GO.grid.MonthGrid({
		id: 'month-grid',
		store: this.monthGridStore,
		border: false,
		layout:'fit',
		firstWeekday: parseInt(GO.settings.first_weekday),
		keys:[ {
				key:  Ext.EventObject.DELETE,
				fn: function(){
					this.deleteHandler();
				},
				scope: this
		}]
	});	
	
	this.viewGrid = new GO.grid.ViewGrid({
		id: 'view-grid',
		border: false,
		firstWeekday: parseInt(GO.settings.first_weekday),
		keys:[ {
				key:  Ext.EventObject.DELETE,
				fn: function(){
					this.deleteHandler();
				},
				scope: this
		}]
	});
	
	this.viewGrid.on('zoom', function(conf){
		conf.applyFilter=true;
		this.setDisplay(conf);
	}, this);	
	
	this.listGrid = new GO.calendar.ListGrid({
		id: 'list-grid',
		border: false,
		firstWeekday: parseInt(GO.settings.first_weekday)
	});
	
	this.listGrid.store.on('load', this.setCalendarBackgroundColors, this);

	this.daysGrid.store.on('load', function(){
	    GO.checker.params.calendar_calendars = this.daysGrid.store.baseParams.calendars;
	    GO.checker.params.calendar_start_time = this.daysGrid.store.baseParams.start_time;
	    GO.checker.params.calendar_end_time = this.daysGrid.store.baseParams.end_time;
	    
	    GO.calendar.activePanel = this.getActivePanel();

		this.setCalendarInfo(GO.calendar.activePanel.store.reader.jsonData.title,GO.calendar.activePanel.store.reader.jsonData.comment);

		this.calendar_name = GO.calendar.activePanel.store.reader.jsonData.calendar_name;
		this.calendar_id = GO.calendar.activePanel.store.reader.jsonData.calendar_id;

	},this);

	this.monthGrid.store.on('load', function(){
	    GO.checker.params.calendar_calendars = this.monthGrid.store.baseParams.calendars;
	    GO.checker.params.calendar_start_time = this.monthGrid.store.baseParams.start_time;
	    GO.checker.params.calendar_end_time = this.monthGrid.store.baseParams.end_time;

	    GO.calendar.activePanel = this.getActivePanel();

		this.setCalendarInfo(GO.calendar.activePanel.store.reader.jsonData.title,GO.calendar.activePanel.store.reader.jsonData.comment);

		this.calendar_name = GO.calendar.activePanel.store.reader.jsonData.calendar_name;
		this.calendar_id = GO.calendar.activePanel.store.reader.jsonData.calendar_id;
	},this);

	this.listGrid.store.on('load', function(){
	    GO.checker.params.calendar_calendars = this.listGrid.store.baseParams.calendars;
	    GO.checker.params.calendar_start_time = this.listGrid.store.baseParams.start_time;
	    GO.checker.params.calendar_end_time = this.listGrid.store.baseParams.end_time;

	    GO.calendar.activePanel = this.getActivePanel();

		this.setCalendarInfo(GO.calendar.activePanel.store.reader.jsonData.title,GO.calendar.activePanel.store.reader.jsonData.comment);

		this.calendar_name = GO.calendar.activePanel.store.reader.jsonData.calendar_name;
		this.calendar_id = GO.calendar.activePanel.store.reader.jsonData.calendar_id;
	},this);

	this.viewGrid.on('storeload', function(grid, count, mtime, params, response){
	    GO.checker.params.calendar_start_time = params.start_time;
	    GO.checker.params.calendar_end_time = params.end_time;
	    GO.checker.params.calendar_view_id = params.view_id;
	    
	    GO.calendar.activePanel = this.getActivePanel();
	    GO.calendar.activePanel.count = count;
	    GO.calendar.activePanel.mtime = mtime;
		 
		this.setCalendarInfo(grid.jsonData.title,grid.jsonData.comment);

		this.calendar_name = grid.jsonData.calendar_name;
		this.calendar_id = grid.jsonData.calendar_id;
	}, this);


	this.daysGrid.on('deleteEvent', function(menuItem)
	{
		this.deleteHandler(menuItem);
	},this);
	this.monthGrid.on('deleteEvent', function()
	{
		this.deleteHandler();
	},this);
	this.listGrid.on('deleteEvent', function()
	{
		this.deleteHandler();
	},this);
	this.viewGrid.on('deleteEvent', function()
	{
		this.deleteHandler();
	},this);

	this.listStore = this.listGrid.store;

	this.displayPanel = new Ext.Panel({
		region:'center',
		titlebar: false,
		autoScroll:false,
		layout: 'card',
		activeItem: 0,
		split: true,
		cls: 'cal-display-panel',
		items: [this.daysGrid, this.monthGrid, this.viewGrid, this.listGrid]
	});

	var me = this;
			
	var tbar = [{
		iconCls: 'ic-add',
		cls: "primary",
		text: t("Add"),
		handler: function(){
							
			GO.calendar.showEventDialog({
				calendar_id: this.calendar_id,
				calendar_name: this.calendar_name
			});
										
		},
		scope: this
	},{	
		iconCls: 'ic-delete',
		tooltip: t("Delete"),
		handler: this.deleteHandler,
		scope: this
	},'-',{
		iconCls: 'ic-refresh',
		tooltip: t("Refresh"),
		handler: function(){
			this.init();
		},
		scope: this
	},{
		iconCls: 'ic-settings',
		tooltip: t("Administration"),
		handler: function(){
			this.showAdminDialog();
		},
		scope: this
	},{
		iconCls:'ic-today',
		tooltip : t("Today"),
		handler : function(){
			this.setDisplay({
				date: new Date().clearTime()
			});
		},
		scope : this
	},{
	xtype:'buttongroup',
	items: [
		this.dayButton = new Ext.Button({
			iconCls: 'ic-view-day',
			text: t("Day"),
			handler: function(){

				this.setDisplay({
					days:1,
					displayType: this.displayType == 'view' ? 'view' : 'days',
					calendar_name: this.calendar_name,
					view_id : this.view_id
				});
			},
			scope: this
		}),
		this.workWeekButton = new Ext.Button({
			iconCls: 'ic-view-week',
			text: t("5 Days", "calendar"),
			handler: function(){

				this.setDisplay({
					days: 5,
					displayType: this.displayType == 'view' ? 'view' : 'days',
					calendar_name: this.calendar_name,
					view_id : this.view_id
				});
			},
			scope: this
		}),
		this.weekButton = new Ext.Button({
			iconCls: 'ic-view-week',
			text: t("Week"),
			handler: function(){

				this.setDisplay({
					days: 7,
					displayType: this.displayType == 'view' ? 'view' : 'days',
					calendar_name: this.calendar_name,
					view_id : this.view_id
				});
			},
			scope: this
		}),this.monthButton= new Ext.Button({
			iconCls: 'ic-view-module',
			text: t("Month"),
			handler: function(){

				this.setDisplay({
					displayType:'month',
					calendar_name: this.calendar_name,
					view_id : this.view_id
				});
			},
			scope: this
		}),
		this.listButton= new Ext.Button({
			iconCls: 'ic-view-list',
			text: t("List", "calendar"),
			handler: function(item, pressed){

				this.setDisplay({
					displayType:'list',
					calendar_name: this.calendar_name,
					view_id : this.view_id
				});

			},
			scope: this
		})]
   },
	'->',
	this.calendarTitle = new Ext.Button({
		iconCls: 'ic-info',
		disabled: true,
		tooltip: 'Calendar'
	}),
	this.printButton = new Ext.Button({
		iconCls: 'btn-print',
		tooltip:t("Print"),
		menu:new Ext.menu.Menu({
				items:[{		
					text: t("Print current view", "calendar"),
					iconCls: 'btn-print',
					handler: function(){

						var sD = this.getActivePanel().startDate;
						var eD = this.getActivePanel().endDate;

						var urlParams = {};

						urlParams.start_time = sD.format('Y-m-d');
						urlParams.end_time = eD.format('Y-m-d');
						urlParams.print	= true;

						if(!GO.util.empty(this.view_id))
							urlParams.view_id = this.view_id;
						else
							urlParams.calendars = Ext.encode(this.calendars);

						var url = GO.util.empty(this.view_id) ? GO.url('calendar/event/store',urlParams) : GO.url('calendar/event/viewStore',urlParams);

						window.open(url);
					},
					scope: this
				},{		
					text: t("Print count per category", "calendar"),
					handler: function(){
						if(!GO.calendar.printCategoryCountDialog){
							GO.calendar.printCategoryCountDialog = new GO.calendar.PrintCategoryCountDialog();
						}	

						GO.calendar.printCategoryCountDialog.show(0,{});
					},
					scope: this
				},'-',
				{
					text: t("Day"),
					iconCls: 'ic-view-day',
					handler: function () {
						var urlParams = {
							calendars: Ext.encode(this.calendars),
							date: +this.datePicker.getValue()/1000
						};
						window.open(GO.url('calendar/report/day',urlParams));
					},
					scope: this
				},
				{
					text: t("5 Days", "calendar"),
					iconCls: 'ic-view-week',
					handler: function () {
						var urlParams = {
							calendars: Ext.encode(this.calendars),
							date: +this.datePicker.getValue()/1000
						};
						window.open(GO.url('calendar/report/workweek',urlParams));
					},
					scope: this
				},
				{
					text: t("Week"),
					iconCls: 'ic-view-week',
					handler: function () {
						var urlParams = {
							calendars: Ext.encode(this.calendars),
							date: +this.datePicker.getValue()/1000
						};
						window.open(GO.url('calendar/report/week',urlParams));
					},
					scope: this
				},
				{
					text: t("Month"),
					iconCls: 'ic-view-module',
					handler: function () {
						var urlParams = {
							calendars: Ext.encode(this.calendars),
							date: +this.datePicker.getValue()/1000
						};
						window.open(GO.url('calendar/report/month',urlParams));
					},
					scope: this
				}]
			})
		}),
		{
			xtype: "container",
			layout: "toolbar",
			addComponentToMenu: function(menu, cmp) {

				function updatePeriod(cal, period) {
					me.periodInfoPanel2.update(period);
				}

				menu.add({
					xtype: "container",
					overflowComponent: true,
					layout: "toolbar",
					items: [
						{
							xtype:"button",
							iconCls: 'ic-keyboard-arrow-left',
							handler: function(){
								me.setDisplay({
									date: this.getActivePanel().previousDate()
								});
							},
							scope: this
						},me.periodInfoPanel2 = new Ext.Container({
							html: me.periodInfoPanel.getEl().dom.innerText,
							plain:true,
							border:false,
							cls:'cal-period'
						}),{
							xtype:"button",
							iconCls: 'ic-keyboard-arrow-right',
							important: true,
							handler: function(){
								me.setDisplay({
									date: me.getActivePanel().nextDate()
								});
							},
							scope: this
						}
					]

				})

				me.periodInfoPanel2.mon(me, "periodchange", updatePeriod);


			},
			items: [
				{
					xtype:"button",
					iconCls: 'ic-keyboard-arrow-left',
					handler: function(){
						this.setDisplay({
							date: this.getActivePanel().previousDate()
						});
					},
					scope: this
				},this.periodInfoPanel = new Ext.Container({
					html: '',
					plain:true,
					border:false,
					cls:'cal-period'
				}),{
					xtype:"button",
					iconCls: 'ic-keyboard-arrow-right',
					important: true,
					handler: function(){
						this.setDisplay({
							date: this.getActivePanel().nextDate()
						});
					},
					scope: this
				}
			]

		}

	];
	
	
							
	for(var i=0;i<GO.calendar.extraToolbarItems.length;i++)
	{
		tbar.push(GO.calendar.extraToolbarItems[i]);
	}
		
		
	config.layout='border';
	
	config.items=[
		this.westPanel = new Ext.Panel({
			region:'west',
			width: dp(224),
			boxMinWidth: dp(224),
			stateId: 'cal-west',
			cls:'go-sidenav',
			split:true,
			layout:'border',
			items:[
			new Ext.Panel({
				region:'north',
				border:true,
				height:dp(241),
				split:false,
				baseCls:'x-plain',
				layout: {
					type: 'vbox',
					align: 'center'
				},
				items:[
					this.datePicker
				]
			}),
			this.calendarListPanel]
		}),
		this.centerPanel = new Ext.Panel({
			layout:'fit',
			region:'center',
			tbar: {enableOverflow: true,items: tbar},
			items: [this.displayPanel]
		})
	];		
		
	GO.calendar.MainPanel.superclass.constructor.call(this, config);	
}

Ext.extend(GO.calendar.MainPanel, Ext.Panel, {
	/*
	 * The type of display. Can be days, month or view
	 */
	displayType : 'days',
	lastCalendarDisplayType : 'days',
	state : false,
	calendarId : 0,
	viewId : 0,
	group_id: 1,
	
	route : function(id, entity) {
		GO.calendar.showEventDialog({event_id: id}).on("load", function(dlg) {
			var date = dlg.startDate.getValue();

			GO.mainLayout.getModulePanel('calendar').show();
			GO.mainLayout.getModulePanel('calendar').setDisplay({
				date: date
			});

		}, this, {single: true});
	},
	
	setCalendarInfo: function(title, comment) {
		this.calendarTitle.setTooltip({title:title,text:comment});
	},
	
	setCalendarBackgroundColors : function(){

		
		var view = this.calendarList.getView();
		view.refresh();
		
		var store = this.getActivePanel().store;
		
		if(store.reader.jsonData.backgrounds){
			
			
			var rowIndex;
			
			for(var cal_id in store.reader.jsonData.backgrounds){					
				rowIndex = this.calendarList.store.indexOfId(parseInt(cal_id));		
				if(rowIndex>-1){
					var rowEl = Ext.get(view.getCell(rowIndex, 0));		
					if(rowEl)
						rowEl.applyStyles("color: #"+store.reader.jsonData.backgrounds[cal_id]);				
				}
			}
		}
	},


	onShow : function(){        
		GO.calendar.MainPanel.superclass.onShow.call(this);
		this.daysGrid.scrollToLastPosition();

		if(GO.calendar.activePanel){
			if(GO.calendar.activePanel.id != 'view-grid')
			{
				GO.calendar.activePanel.store.reload();
			}else
			{
				GO.calendar.activePanel.reload();
			}
		}
	},
	afterRender : function(){
		GO.calendar.MainPanel.superclass.afterRender.call(this);

		if(go.Modules.isAvailable("legacy", "tasks")){
			GO.dialogListeners.add('tasks',{
				scope:this,
				save:function(){
					if(this.isVisible()){
						this.refresh();
					}
				}
			});
		}		

		GO.dialogListeners.add('event',{
			scope:this,
			save:function(newEvent, oldDomId){

				if(this.displayType=='list')
				{
					this.setDisplay();
				}else
				{
					var activeGrid = this.getActivePanel();

					//var oldDomId = activeGrid.domIds[newEvent.event_id] ? activeGrid.domIds[newEvent.event_id][0] : false;
					//reload grid if old or new event repeats. Do not reload if an occurence of a repeating event is modified
					if(newEvent.repeats || !oldDomId || !activeGrid.remoteEvents[oldDomId] || activeGrid.remoteEvents[oldDomId].repeats)
					{
						if(this.displayType=='view')
							activeGrid.reload();
						else
							activeGrid.store.reload();
					}else
					{
						//var remove_id = newEvent.exception_event_id ? newEvent.exception_event_id : newEvent.event_id;
						
						activeGrid.removeEvent(oldDomId);

						switch(this.displayType)
						{
							case 'month':
								
								for(var i=0,found=false; i<this.calendars.length && !found; i++)
								{
									if(this.calendars[i] == newEvent.calendar_id)
									{
									
										var domIds = this.monthGrid.addMonthGridEvent(newEvent);

										GO.calendar.eventDialog.oldDomId=domIds[0];
									}
								}
								break;
							case 'days':								
								for(var i=0,found=false; i<this.calendars.length && !found; i++)
								{
									if(this.calendars[i] == newEvent.calendar_id)
									{
										var eventRecord = new GO.calendar.CalendarEvent(newEvent);
										this.daysGridStore.add(eventRecord);
										GO.calendar.eventDialog.oldDomId=this.daysGrid.lastDomId;

										found = true;
									}
								}							
								break;

							case 'view':
								GO.calendar.eventDialog.oldDomId=this.viewGrid.addViewGridEvent(newEvent);
								break;
						}
					}
				}
			}
		});
		
		GO.calendar.groupDialog = new GO.calendar.GroupDialog();
		GO.calendar.groupDialog.on('save', function(e, group_id, fields)
		{			
			if(group_id == 1)
			{
				GO.calendar.defaultGroupFields = fields;
			}			
			GO.calendar.groupsGrid.store.load({
				callback:function(){
					if(GO.calendar.eventDialog)
						GO.calendar.eventDialog.resourceGroupsStore.reload();
				},
				scope:this
			});
			
								
		},this);
		

//		if(GO.calendar.openState){
//			this.state=GO.calendar.openState;
//			if(!this.state.calendars && !this.state.view_id)
//				this.state.calendars=[GO.calendar.defaultCalendar.id];
//		}else
//		{
			this.state = Ext.state.Manager.get('calendar-state');
			if(!this.state)
			{
				this.state = {
					displayType:'days',
					days: 5,
					calendars:[GO.calendar.defaultCalendar.id],
					view_id: 0
				};
			}else
			{
				this.state = Ext.decode(this.state);
			}
			
			
//			console.log(this.state);

			if(this.state.displayType=='view')
				this.state.displayType='days';

			if(go.util.empty(this.state.calendars)) {
				this.state.calendars=[GO.calendar.defaultCalendar.id];
			}
			
			//this.state.view_id=0;
			//this.state.group_id=1;
//		}

		if(GO.calendar.openState)
			this.state = Ext.apply(this.state, GO.calendar.openState);


			console.warn(this.state);

		/*this.state.applyFilter=true;
		this.calendarsStore.on('load', function(){
			this.state.applyFilter=false;
		}, this, {single:true});*/
				
		this.init();	
		this.createDaysGrid();

		/*this.on('show', function(){
			this.refresh();
		}, this);		*/
	},
	
	init : function(){

		GO.request({
			maskEl:this.getEl(),
			url: "core/multiRequest",
			params:{
				requests:Ext.encode({
					views:{r:"calendar/view/store", start:0, limit:GO.settings.config.nav_page_size},				
					calendars:{r:"calendar/calendar/store", start:0, limit:GO.settings.config.nav_page_size},
					//categories:{r:"calendar/category/store", start:0, fetch_all: 1, limit:GO.settings.config.nav_page_size},
					resources:{r:"calendar/calendar/calendarsWithGroup","resourcesOnly":1, start:0, limit:GO.settings.config.nav_page_size}
				})
			},
			
			success: function(options, response, result)
			{
				this.calendarsStore.loadData(result.calendars);
				this.viewsStore.loadData(result.views);
				//this.categoryStore.loadData(result.categories);
				this.resourcesStore.loadData(result.resources);				
			},
			scope:this
		});
	},
	
	deleteHandler : function(menuItem){
			
		switch(this.displayType)
		{
			case 'days':
				var event = this.daysGrid.getSelectedEvent();
				var callback = function(event, refresh){					
					if(refresh)
					{
						this.daysGrid.store.reload();
					}else
					{
						this.daysGrid.removeEvent(event.domId);						
					}
				};			
				break;
			
			case 'month':
				var event = this.monthGrid.getSelectedEvent();
				var callback = function(event, refresh){
					if(refresh)
					{
						this.monthGrid.store.reload();
					}else
					{
						this.monthGrid.removeEvent(event.domId);
					}
				};			
				break;
			
			case 'view':
				var event = this.viewGrid.getSelectedEvent();
				var callback = function(event, refresh){
					if(refresh)
					{
						this.viewGrid.reload();
					}else
					{
						this.viewGrid.removeEvent(event.domId);
					}
				};			
				break;
			
			case 'list':
				var event = this.listGrid.getSelectedEvent();
				var callback = function(event, refresh){
					if(refresh)
					{
						this.listGrid.store.reload();
					}else
					{
						this.listGrid.removeEvent();//will remove the selected row.
					}					
				};			
				break;
		}
		
		// If the event is a "all day" event and you are invited by somebody else, then deleting the 
		if(!event && menuItem && menuItem.contextMenu.event){
			event = menuItem.contextMenu.event;
			this.deleteEvent(event, callback);
			return;
		}
		
		if(event && (!event.read_only || !event.is_organizer) && !event.task_id & !event.contact_id)				
//		console.log(event);
//		if(event && !event.task_id & !event.contact_id)
		{
			this.deleteEvent(event, callback);
		}
	},
	
	getActivePanel : function(){
		switch(this.displayType)
		{
			case 'days':
				return this.daysGrid;			
				break;
			
			case 'month':
				return this.monthGrid;			
				break;
			
			case 'view':
				return this.viewGrid;			
				break;
			
			case 'list':
				return this.listGrid;			
				break;
		}
		
	},
	
	updatePeriodInfoPanel : function (){
		
		/*var html = '';
		var displayDate = this.getActivePanel().configuredDate;
		
		if(this.displayType=='month')
		{
			html = displayDate.format('F, Y');
		}else
		{
			if(this.days<8){
				html = t("Week")+' '+displayDate.format('W');
			}else
			{
				html = displayDate.format('W')+' - '+displayDate.add(Date.DAY,this.days).format('W');
			}
		}*/
		
		this.periodInfoPanel.getEl().update(this.getActivePanel().periodDisplay);
		this.fireEvent("periodchange", this, this.getActivePanel().periodDisplay)
	},
	
	
	deleteEvent : function(event, callback){
		
		//store them here so the already created window can use these values
		if(event.repeats)
		{
			this.currentDeleteEvent = event;
			this.currentDeleteCallback = callback;
			
				
			if(!this.recurrenceDialog)
			{
				this.recurrenceDialog = new GO.calendar.RecurrenceDialog({
					forDelete:true
				});

				this.recurrenceDialog.on('single', function()
				{
					var params={
						exception_date: this.currentDeleteEvent.startDate.format("U"),
						id: this.currentDeleteEvent.event_id
					};

					this.sendDeleteRequest(params, this.currentDeleteCallback, this.currentDeleteEvent);

					this.recurrenceDialog.hide();
				},this)

				this.recurrenceDialog.on('thisandfuture', function()
				{
					var params={
						exception_date: this.currentDeleteEvent.startDate.format("U"),
						thisAndFuture: true,
						id: this.currentDeleteEvent.event_id
					};

					this.sendDeleteRequest(params, this.currentDeleteCallback, this.currentDeleteEvent);
					this.recurrenceDialog.hide();
				},this)

				this.recurrenceDialog.on('entire', function()
				{
					var params={						
						id: this.currentDeleteEvent.event_id
					};

					this.sendDeleteRequest(params, this.currentDeleteCallback, this.currentDeleteEvent, true);

					this.recurrenceDialog.hide();
				},this)

				this.recurrenceDialog.on('cancel', function()
				{
					this.recurrenceDialog.hide();
				},this)
			}

			this.recurrenceDialog.thisAndFutureButton.setDisabled(event.recurring_start_time == event.start_time);
			this.recurrenceDialog.show();
		}else
		{
			Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to delete the selected item?"), function(btn){
				if(btn=='yes')
				{
					var params={
						//task: 'delete_event',
						id: event.event_id
					};

					this.sendDeleteRequest(params, callback, event);
				}
			}, this);
		}
	},
	
	sendDeleteRequest : function(params, callback, event, refresh)
	{
		GO.request({
			maskEl:this.getEl(),
			url: 'calendar/event/delete',
			params: params,
			success:function(options, response,result){
				if(!result.success)
				{
					Ext.MessageBox.alert(t("Error"), result.feedback);
				}else
				{					
					if(result.askForCancelNotice){
						
						var msg = result.is_organizer ? 
							t("Would you like to send a cancellation notice to the participants?", "calendar") :
							t("Would you like to notify the organizer that you will not attend by e-mail?", "calendar")
						
						Ext.Msg.show({
							title:t("Send notification?", "calendar"),
							msg: msg,
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, config){
								params.send_cancel_notice=buttonId=='yes'?1:0;
								this.sendDeleteRequest(params, callback, event, refresh);
							},
							//animEl: 'elId',
							icon: Ext.MessageBox.QUESTION,
							scope:this
					 });
					}else
					{
						callback.call(this, event, refresh);
					}				
				}
			},
			scope:this
		});
	},

	/*
	 * 
	 * displayType: 'days', 'month', 'view'
	 * days: number of days to display in days grid
	 * calendar_id: calendar to display
	 * view_id: view to display
	 * 
	 * date: the date to display
	 * 
	 * 
	 */
	
	setDisplay : function(config){
		if(!config)
		{
			config = {};
		}
		
		if(config.calendar_id)
			config.calendars=[config.calendar_id];

		if(config.group_id)
		{
			this.group_id=config.group_id;
		}

		if(typeof(config.project_id) !='undefined')
		{
			this.project_id=config.project_id;
		}

		config.title = '';
		var record;
		if(config.view_id){
			
			this.state.displayType="view";
			this.state.view_id=config.view_id;
			
			if(!this.viewsStore.loaded){
				this.viewsStore.load({
					callback:function(){
						this.setDisplay(config);
					},
					scope:this
				});
				return;
			} else {
			
				record = this.viewsStore.getById(config.view_id);

				if(record) {
					config.merge = record.get('merge');
					config.owncolor = record.get('owncolor');
				} else
				{
					delete config.view_id;
					delete this.state.view_id;
				}
			}
		}

		if(config.displayType)
		{							
			this.displayType=config.displayType;
		}else if(config.calendars)
		{
			this.displayType=this.lastCalendarDisplayType;
		}else if(config.view_id)
		{
			if (config.merge=='0')
				this.displayType='view';
			else
				this.displayType=this.lastCalendarDisplayType;
		}
	
		
		this.state.displayType=this.displayType;
			
		if(this.displayType!='view')
		{
			this.lastCalendarDisplayType=this.displayType;
		}
		
		switch(this.displayType)
		{
			case 'month':
				this.displayPanel.getLayout().setActiveItem(1);
				break;
			
			case 'days':					
				this.displayPanel.getLayout().setActiveItem(0);
				break;
			
			case 'view':
				this.displayPanel.getLayout().setActiveItem(2);
				break;
			
			case 'list':
				this.displayPanel.getLayout().setActiveItem(3);
				break;
		}
		
		this.monthButton.setDisabled(this.displayType=='view');
		this.listButton.setDisabled(this.displayType=='view');

		if (config.calendars) {
			this.view_id=0;
			this.calendar_id = config.calendars.indexOf(GO.calendar.defaultCalendar.id)>-1 ? GO.calendar.defaultCalendar.id : config.calendars[0];
			this.calendars=config.calendars;
			this.daysGridStore.baseParams['calendars']=Ext.encode(config.calendars);
			this.monthGridStore.baseParams['calendars']=Ext.encode(config.calendars);
			this.listGrid.store.baseParams['calendars']=Ext.encode(config.calendars);
		}
		
		if (typeof(config.merge)!='undefined'){
			this.merge=config.merge;
			this.owncolor = config.owncolor;
		}		

		if(config.calendar_name)
		{
			this.calendar_name=config.calendar_name;
		}

		if(config.view_id)
		{
			this.view_id=config.view_id;
			this.viewGrid.setViewId(config.view_id);
		}

		

		this.daysGridStore.baseParams['owncolor']=this.owncolor;
		this.monthGridStore.baseParams['owncolor']=this.owncolor;
		this.listGrid.store.baseParams['owncolor']=this.owncolor;

		if (this.merge=='1' && this.view_id) {
			this.daysGridStore.baseParams['view_id']=this.view_id;
			this.monthGridStore.baseParams['view_id']=this.view_id;
			this.listGrid.store.baseParams['view_id']=this.view_id;
			
		} else {
			this.daysGridStore.baseParams['view_id']=null;
			this.monthGridStore.baseParams['view_id']=null;
			this.listGrid.store.baseParams['view_id']=null;			
		}
		
		if(config.unixtime){
			config.date = Date.parseDate(config.unixtime,'U');
		}

		if(config.date)
		{
			this.datePicker.setValue(config.date);
			
			if(!config.days)
			{				
				config.days = this.type=='days' ?  this.daysGrid.days : this.viewGrid.days;
			}
			this.daysGrid.setDate(config.date,config.days,this.displayType=='days');
			this.monthGrid.setDate(config.date,this.displayType=='month');
			this.viewGrid.setDate(config.date,config.days, this.displayType=='view');
			this.listGrid.setDate(config.date,config.days, this.displayType=='list');
			
			this.days=config.days;
		}else if(config.days && this.displayType!='month')
		{
			this.daysGrid.setDays(config.days, this.displayType=='days');
			this.viewGrid.setDays(config.days, this.displayType=='view');
			this.listGrid.setDays(config.days, this.displayType=='list');
			
			this.days=config.days;
		}else
		{
			if(config.days)
			{
				this.days=config.days;
			}
			
			switch(this.displayType)
			{				
				case 'month':
					this.monthGridStore.reload();
					break;
				
				case 'days':					
					this.daysGridStore.reload();
					break;
				
				case 'view':
					this.viewGrid.load();
					break;
				
				case 'list':
					this.listGrid.store.reload();
					break;
			}
		}
		
		
		this.dayButton.toggle(this.displayType=='days' && this.days==1);
		this.workWeekButton.toggle(this.displayType=='days' && this.days==5);
		this.weekButton.toggle(this.displayType=='days' && this.days==7);
		
		this.monthButton.toggle(this.displayType=='month');
		this.listButton.toggle(this.displayType=='list');
		
		this.updatePeriodInfoPanel();
		

		this.state={
			displayType:this.displayType,
			days: this.days,
			calendars:this.calendars,
			view_id: this.view_id,
			merge:this.merge,
			owncolor:this.owncolor
		};


		this.saveState();
			
		
		this.clearGrids(config);
	},
	
	clearGrids : function(config){
		var selectGrid, clearGrids=[];
		if(this.view_id>0){
			selectGrid = this.viewsList;

			selectGrid.expand();

			this.resourcesList.getSelectionModel().clearSelections();
			
			var sr = selectGrid.getStore().getById(config.view_id);
			var sr_index = selectGrid.getStore().indexOf(sr);

			selectGrid.getSelectionModel().selectRow(sr_index);
//			selectGrid.getSelectionModel().selectRecords(rr);
			
			clearGrids.push(this.calendarList);
			if(this.projectCalendarsList)
				clearGrids.push(this.projectCalendarsList);
		}else
		{
			this.viewsList.getSelectionModel().clearSelections();
			
			if(this.group_id==1){
				
				selectGrid = this.calendarList;				
				this.resourcesList.getSelectionModel().clearSelections();

				selectGrid.expand();

				if(config.applyFilter)
					selectGrid.applyFilter(this.calendars, true);
			}else
			{
				clearGrids.push(this.calendarList);
				selectGrid = this.resourcesList;

				var records=[];
				for(var i=0,max=this.calendars.length;i<max;i++){
					records.push(selectGrid.store.getById(this.calendars[i]));
				}
				selectGrid.getSelectionModel().selectRecords(records);
				selectGrid.expand();
			}					
		}

		for(var i=0,max=clearGrids.length;i<max;i++){
			//clearGrids[i].allowNoSelection=true;
			clearGrids[i].applyFilter('clear', true);
			//clearGrids[i].allowNoSelection=false;
		}
	},
	
	saveState : function()
	{
		var state = {
			displayType: this.displayType,
			days: this.days,
			calendars: this.calendars,
			view_id: this.view_id,
			group_id: this.group_id
		};
		
		var old = Ext.state.Manager.get('calendar-state'), newState = Ext.encode(state);
		
		if(old != newState) {
			Ext.state.Manager.set('calendar-state', newState);
		}
	},
	
	refresh : function() {
		this.setDisplay();
	},
      
	createDaysGrid : function()
	{
		
		this.daysGrid.on("eventResize", function(grid, event, actionData, domIds){

			var params = {
				duration_end_time : actionData.end_time
			};

//			if(event.has_other_participants)
//				params.send_invitation=confirm(t("Would you like to send updated meeting information to participants?", "calendar")) ? 1 : 0;
			
			if(actionData.singleInstance)
			{				
				params['exception_date']=actionData.dragDate.format("U");
				params['exception_for_event_id']=event['event_id'];
			}else
			{
				params.id=event['event_id'];
			}
  		
			GO.request({
				url: 'calendar/event/submit',
				params: params,
				success: function(options,  response, result)
				{					
					if(event.repeats)
					{
						grid.store.reload();
					}

					GO.calendar.handleMeetingRequest(result);					
				}
			});
				
		}, this);
		
		
		this.daysGrid.on("create", function(CalGrid, newEvent){
			var formValues={};
				
			formValues['start_date'] = newEvent['startDate'];//.format(GO.settings['date_format']);
			formValues['start_time'] = newEvent['startDate'].format(GO.settings.time_format);
				
			formValues['end_date'] = newEvent['endDate'];//.format(GO.settings['date_format']);
			formValues['end_time'] = newEvent['endDate'].format(GO.settings.time_format);
			
				
			GO.calendar.showEventDialog({
				values: formValues,
				calendar_id: this.calendar_id
			});
				
		}, this);
			
		this.monthGrid.on("create", function(grid, date){
		
			var now = new Date.parseDate(new Date().format("H"), "H");
		
			var i = parseInt(new Date().format("i"));
			if (i > 30) {
				i = 45;
			} else if (i > 15) {
				i = 30;
			} else if (i > 0) {
				i = 15;
			} else {
				i = 0;
			}			
			now = now.add(Date.MINUTE, i);
			
			var formValues={
				start_date: date,
				end_date: date,
				start_time: now.format(GO.settings.time_format),
				end_time: now.add(Date.HOUR, 1).format(GO.settings.time_format)				
			};
				
			GO.calendar.showEventDialog({
				values: formValues,
				calendar_id: this.calendar_id
			});
		}, this);
		
		this.monthGrid.on('changeview', function(grid, days, date){
			this.setDisplay({
				displayType:'days',
				days:days,
				date: date
			});
		}, this);
		
		this.daysGrid.on("eventDblClick", this.onDblClick, this);
		this.monthGrid.on("eventDblClick", this.onDblClick, this);
		this.viewGrid.on("eventDblClick", this.onDblClick, this);
		
		
		this.monthGrid.on("move", this.onEventMove,this);
		this.daysGrid.on("move", this.onEventMove,this);

		this.viewGrid.on("move", function(grid, event, actionData, domIds){

			var params = {				
				id : event['event_id']
			};

//			if(event.has_other_participants)
//				params.send_invitation=confirm(t("Would you like to send updated meeting information to participants?", "calendar")) ? 1 : 0;
			
			if(actionData.offset)
				params['offset']=actionData.offset;
			
			if(actionData.offsetDays)
				params['offset_days']=actionData.offsetDays;
			
			if(event.repeats && actionData.singleInstance)
			{
				params['exception_date']=actionData.dragDate.format(grid.dateTimeFormat);				
			}
			
			if(actionData.calendar_id)
			{
				params['calendar_id']=actionData.calendar_id;
			}
			 		
			Ext.Ajax.request({
				url: GO.url('calendar/event/submit'),
				params: params,
				callback: function(options, success, response)
				{
					var responseParams = Ext.decode(response.responseText);
					if(!responseParams.success)
					{
						Ext.MessageBox.alert(t("Error"), responseParams.feedback);
					}else
					{
						if(event.repeats && !actionData.singleInstance)
						{
							grid.reload();
						}else if(responseParams.new_event_id)
						{
							grid.setNewEventId(domIds, responseParams.new_event_id);
						}
						
						GO.calendar.handleMeetingRequest(responseParams);
					}
				}
			});	  		
	  		
		},this);
		
		
		this.viewGrid.on("create", function(grid, date, timeOfDay, calendar_id){

			var formValues={
				start_date: date,
				end_date: date
			};
			
			switch(timeOfDay){
				case 'allday':
					formValues.all_day_event=true;
				break;
				
				case 'morning':
					formValues.start_time = new Date.parseDate("08","H").format(GO.settings.time_format);
					formValues.end_time = new Date.parseDate("09","H").format(GO.settings.time_format);
				break;
				
				case 'afternoon':
					formValues.start_time = new Date.parseDate("12","H").format(GO.settings.time_format);
					formValues.end_time = new Date.parseDate("13","H").format(GO.settings.time_format);
				break;
				
				case 'evening':
					formValues.start_time = Date.parseDate("18","H").format(GO.settings.time_format);
					formValues.end_time = new Date.parseDate("19","H").format(GO.settings.time_format);
				break;
			}
			
			
				
			GO.calendar.showEventDialog({
				values: formValues,
				calendar_id: calendar_id
			});
		}, this);
	},
	  
	onDblClick : function(grid, event, actionData){
		
	
		switch(event.model_name){
			case "GO\\Tasks\\Model\\Task":
				GO.tasks.showTaskDialog({
					task_id : event.task_id
				})	
			break;
			
			case "GO\\Adressbook\\Model\\Contact":
				go.Router.goto("contact/" + event['contact_id']);
			break;
			
			case "GO\\Calendar\\Model\\Event":
				if(event.permission_level<GO.permissionLevels.write)
					return;
		
				if(!event.is_organizer){
					// You are not authorised to edit this event because you are not the organizer.
					// Show message to the user
					//Ext.Msg.alert(t("You are not the organizer", "calendar"), t("You are not authorised to edit this event because you are not the organizer.", "calendar"));

					if(!this.attendanceWindow){
						this.attendanceWindow = new GO.calendar.AttendanceWindow ();
						this.attendanceWindow.on('save', function(){
							this.refresh();
						}, this);
					}			
					this.attendanceWindow.show(event.event_id);
					if(event.repeats && actionData.singleInstance)
					{
						this.attendanceWindow.setExceptionDate(event['startDate'].format("U"));
					}else
					{
						this.attendanceWindow.setExceptionDate(false);
					}
					return;
				}

				if(event.read_only && !event.contact_id && !event.task_id)
					return false;

				if(event.repeats && actionData.singleInstance)
				{
					GO.calendar.showEventDialog({
						exception_date: event['startDate'].format("U"),
						thisAndFuture: actionData.thisAndFuture || false,
						event_id: event['event_id'],
						oldDomId : event.domId
					});
				}else
				{
					GO.calendar.showEventDialog({
						event_id: event['event_id'],
						oldDomId : event.domId
					});		
				}
			break;			
		}
	},
    
	onEventMove : function(grid, event, actionData, domIds){

		var params = {
			//task : 'update_grid_event',
			//id : event['event_id']
		};

		if(actionData.offset)
			params['offset']=actionData.offset;

		if(actionData.offsetDays)
			params['offset_days']=actionData.offsetDays;

		if(event.repeats && actionData.singleInstance)
		{			
			params['exception_date']=actionData.dragDate.format("U");
			params['thisAndFuture'] = actionData.thisAndFuture || false,
			params['exception_for_event_id']=event['event_id'];
			params['repeats']=true;
		}else
		{
			params['id']=event['event_id'];
		}

		if(actionData.calendar_id)
		{
			params['calendar_id']=actionData.calendar_id;
		}

//		if(event.has_other_participants)
//			params.send_invitation=confirm(t("Would you like to send updated meeting information to participants?", "calendar")) ? 1 : 0;

		GO.request({
			url: 'calendar/event/submit',
			params: params,
			success: function(response, options, result)
			{

//				if(event.repeats && !actionData.singleInstance)
//				{
//					grid.store.reload();
//				}else if(responseParams.id)
//				{
//					grid.setNewEventId(domIds, responseParams.id);
//				}
				if(event.repeats)
					grid.store.reload();

				GO.calendar.handleMeetingRequest(result);
				
			},
			fail : function(response, options, result){
				grid.store.reload();
			}
			
		});
	},

	showAdminDialog : function() {
		
		if(!this.adminDialog)
		{
			
			this.writableCalendarsStore = new GO.data.JsonStore({
				url: GO.url("calendar/calendar/store"),
				baseParams: {
					permissionLevel: GO.permissionLevels.write
				},
				fields:['id','name','user_name'],
				remoteSort:true,
				sortInfo: {
					field: 'name',
					direction: 'ASC'
				}
			});

			
			this.writableViewsStore = new GO.data.JsonStore({
				
				url: GO.url("calendar/view/store"),
				baseParams:{
					permissionLevel:GO.permissionLevels.write
				},
				fields:['id','name','user_name','merge'],
				remoteSort:true,
				sortInfo: {
					field: 'name',
					direction: 'ASC'
				}
			});

			this.writableResourcesStore = new Ext.data.GroupingStore({
				baseParams: {
					resourcesOnly : '1',
					permissionLevel:GO.permissionLevels.write
				},
				reader: new Ext.data.JsonReader({
					root: 'results',
					id: 'id',
					totalProperty: 'total',
					fields:['id','name','user_name','group_name']
				}),
				proxy: new Ext.data.HttpProxy({
					url: GO.url("calendar/calendar/calendarsWithGroup")
				}),
				groupField:'group_name',
				remoteSort:true,
				remoteGroup:true,
				sortInfo: {
					field: 'name',
					direction: 'ASC'
				}
			}),

            
			
			this.calendarDialog = GO.calendar.calendarDialog = new GO.calendar.CalendarDialog();
			this.calendarDialog.on('save', function(e, group_id)
			{
				this.adminDialog.madeChanges=true;
				if(group_id > 1)
				{
					this.writableResourcesStore.reload();
				} else
				{
					this.writableCalendarsStore.reload();
				}
			}, this);

			this.calendarDialog.on('calendarimport', function(){this.adminDialog.madeChanges=true;}, this);

			var tbar = [{
				iconCls: 'ic-add',
				text: t("Add"),
				disabled: !GO.settings.modules.calendar.write_permission,
				handler: function(){
					this.calendarDialog.show(0, false);
				},
				scope: this
			},{				
				iconCls: 'ic-delete',
				text: t("Delete"),
				disabled: !GO.settings.modules.calendar.write_permission,
				handler: function(){
					this.calendarsGrid.deleteSelected();
				},
				scope:this
			},'-']

			tbar.push(new Ext.Button({
				iconCls: 'ic-settings',
				disabled: !GO.settings.modules.calendar.write_permission,
				text: t("Custom fields", "customfields"),
				handler: function()
				{
					GO.calendar.groupDialog.show(1);
				},
				scope: this
			}));

			tbar.push('->');
			tbar.push(new go.toolbar.SearchButton({
				store: this.writableCalendarsStore
			}));

			this.calendarsGrid = new GO.grid.GridPanel( {
				title: t("Calendars", "calendar"),
				paging: true,
				border: false,
				store: this.writableCalendarsStore,
				deleteConfig: {
					callback:function(){
						this.adminDialog.madeChanges=true;
					},
					scope:this
				},
				columns:[{
					header:t("Name"),
					dataIndex: 'name',
					sortable:true
				},{
					header:t("Owner"),
					dataIndex: 'user_name'
				}],
				view:new  Ext.grid.GridView({
					autoFill:true
				}),
				sm: new Ext.grid.RowSelectionModel(),
				loadMask: true,
				tbar:tbar
				
			});		
            
			this.calendarsGrid.on("rowdblclick", function(grid, rowClicked, e)
			{
				this.calendarDialog.show(grid.selModel.selections.keys[0], false);
			}, this);
			
			this.viewDialog = new GO.calendar.ViewDialog();
			
			this.viewDialog.on('save', function(){
				this.writableViewsStore.reload();
				this.adminDialog.madeChanges=true;
			}, this);

			this.viewsGrid = new GO.grid.GridPanel( {
				title: t("Views", "calendar"),
				paging: true,
				border: false,
				store: this.writableViewsStore,
				deleteConfig: {
					callback:function(){
						this.adminDialog.madeChanges=true;
					},
					scope:this
				},
				columns:[{
					header:t("Name"),
					dataIndex: 'name',
					sortable:true
				},{
					header:t("Owner"),
					dataIndex: 'user_name'
				}
				],
				view:new  Ext.grid.GridView({
					autoFill:true
				}),
				sm: new Ext.grid.RowSelectionModel(),
				loadMask: true,
				tbar: [{					
					iconCls: 'ic-add',
					text: t("Add"),
//					disabled: !GO.settings.modules.calendar.write_permission,
					handler: function(){
						this.viewDialog.show();
					},
					scope: this
				},{
					iconCls: 'ic-delete',
					text: t("Delete"),
//					disabled: !GO.settings.modules.calendar.write_permission,
					handler: function(){
						this.viewsGrid.deleteSelected();
					},
					scope:this
				},
				'->',
				this.searchField = new go.toolbar.SearchButton({
					store: this.writableViewsStore
				})]
			});
			
			this.viewsGrid.on("rowdblclick", function(grid, rowClicked, e){
				this.viewDialog.show(grid.selModel.selections.keys[0]);
			}, this);
			
			this.viewsGrid.on('show', function(){
				this.writableViewsStore.load();
			},this, {
				single:true
			});
			
			GO.calendar.groupsGrid = this.groupsGrid = new GO.calendar.GroupsGrid({
				title:t("Resource groups", "calendar"),
				layout:'fit',
				store:GO.calendar.groupsStore,
				deleteConfig: {
					callback:function(){						
						this.adminDialog.madeChanges=true;
					},
					scope:this
				}
			});
			            
			this.resourcesGrid = new GO.calendar.ResourcesGrid({
				title:t("Resources", "calendar"),
				layout:'fit',
				store:this.writableResourcesStore,
				deleteConfig: {
					callback:function(){						                   
						this.adminDialog.madeChanges=true;
					},
					scope:this
				}
			});

			this.categoriesGrid = new GO.calendar.CategoriesGrid({
				title:t("Global categories", "calendar"),
				layout:'fit',
				store:GO.calendar.globalOnlyCategoriesStore
			});

			GO.calendar.categoryDialog = new GO.calendar.CategoryDialog();
			GO.calendar.categoryDialog.on('save', function()
			{
				GO.calendar.categoriesStore.reload();		
			},this);

			var items = [this.calendarsGrid,this.viewsGrid];
			if(GO.settings.modules.calendar.write_permission)
			{
				items.push(this.groupsGrid);
			}

			items.push(this.resourcesGrid);
			if(GO.settings.modules.calendar.write_permission)
			{				
				items.push(this.categoriesGrid);
			}
            
			this.adminDialog = new Ext.Window({
				title: t("Administration", "calendar"),
				layout:'fit',
				modal:true,
				minWidth:dp(440),
				minHeight:dp(616),
				height:dp(616),
				width:dp(784),
				closeAction:'hide',
				madeChanges:false,//used for reloading other stuff in the calendar
				items: new Ext.TabPanel({
					border:false,
					activeTab:0,
					items:items
				})
			});

			this.adminDialog.on('hide', function(){
				if(this.adminDialog.madeChanges){

					this.init();

					if(GO.calendar.eventDialog){
						GO.calendar.eventDialog.initialized=false;
					}					
					this.adminDialog.madeChanges=false;
				}
			}, this);
			
		}
		this.writableCalendarsStore.load();
		this.adminDialog.show();			
	}
});


GO.calendar.extraToolbarItems = [];



go.Modules.register("legacy", 'calendar', {
	mainPanel: GO.calendar.MainPanel,
	title : t("Calendar", "calendar"),
	iconCls : 'go-tab-icon-calendar',
	entities: [{
			name: "Event",
			
			links: [{
				iconCls: 'entity Event orange',

				linkWindow: function() {
					return GO.calendar.showEventDialog();
				},
				linkDetail: function() {
					return new GO.calendar.EventPanel();
				},
				linkDetailCards: function() {
					var forth = new go.links.DetailPanel({
						link: {
							title: t("Forthcoming events"),
							iconCls: 'icon ic-event orange',
							entity: "Event",
							filter: null
						}
					});

					forth.store.setFilter('forthcomming', {forthComingEvents: true});

					var past = new go.links.DetailPanel({						
						link: {
							title: t("Past events"),
							iconCls: 'icon ic-event orange',
							entity: "Event",
							filter: null
						}
					});

					past.store.setFilter('past', {pastEvents: true});

					return [forth, past];
				}				
		}],
		customFields: {
			fieldSetDialog: "GO.calendar.CustomFieldSetDialog"
		}
	}, {
		name: "Calendar",
		customFields: {
			fieldSetDialog: "GO.calendar.CustomFieldSetDialog"
		}
		
	}],
	userSettingsPanels: ["GO.calendar.SettingsPanel"]
	
});



GO.mainLayout.onReady(function(){
	GO.calendar.groupsStore = new GO.data.JsonStore({
		url: GO.url("calendar/group/store"),
		fields:['id','name','user_name','fields','acl_id'],
		remoteSort: true
	}),

	GO.calendar.categoriesStore = new GO.data.JsonStore({
		url : GO.url('calendar/category/store'),
		baseParams : {
			calendar_id:0
		},
		root : 'results',
		totalProperty : 'total',
		id : 'id',
		fields : ['id', 'name','color','calendar_id'],
		remoteSort : true
	}),
	
	GO.calendar.globalOnlyCategoriesStore = new GO.data.JsonStore({
		url : GO.url('calendar/category/store'),
		baseParams : {
			calendar_id:0,
			global_categories:1
		},
		root : 'results',
		totalProperty : 'total',
		id : 'id',
		fields : ['id', 'name','color','calendar_id'],
		remoteSort : true
	}),
	
	GO.calendar.globalCategoriesStore = new GO.data.JsonStore({
		url : GO.url('calendar/category/store'),
		baseParams : {
			calendar_id:0,
			global_categories:1
		},
		root : 'results',
		totalProperty : 'total',
		id : 'id',
		fields : ['id', 'name','color','calendar_id'],
		remoteSort : true
	});

//	GO.newMenuItems.push({
//		text: t("Appointment", "calendar"),
//		iconCls: 'go-model-icon-GO\\Calendar\\Model\\Event',
//		handler:function(item, e){
//
//			var eventShowConfig = item.parentMenu.eventShowConfig || {};
//			eventShowConfig.link_config=item.parentMenu.link_config
//
//			GO.calendar.showEventDialog(eventShowConfig);
//		}
//	});

	//GO.checker is not available in some screens like accept invitation from calendar
	if(go.Modules.isAvailable("legacy", "checker")){

		GO.checker.on('check', function(checker, data){
			var tp = GO.mainLayout.getModulePanel('calendar');
			if(tp && tp.isVisible() && data.calendar)
			{			
				if(GO.calendar.activePanel.id != 'view-grid')
				{
					if((GO.calendar.activePanel.store.reader.jsonData.count_events_only != data.calendar.count) || (GO.calendar.activePanel.store.reader.jsonData.mtime != data.calendar.mtime))
					{
						GO.calendar.activePanel.store.reload();
					}
				}else
				{
					if((GO.calendar.activePanel.count != data.calendar.count) || (GO.calendar.activePanel.mtime != data.calendar.mtime))
					{
						GO.calendar.activePanel.reload();
					}
				}
			}
		});
	}
});

GO.calendar.showEventDialog = function(config){

	if(!GO.calendar.eventDialog)
		GO.calendar.eventDialog = new GO.calendar.EventDialog();	

	GO.calendar.eventDialog.show(config);

	return GO.calendar.eventDialog;
}


GO.calendar.openCalendar = function(displayConfig){
		var mp = GO.mainLayout.initModule('calendar');
		displayConfig.applyFilter=true;
		if(mp.rendered){
			mp.setDisplay(displayConfig);
			mp.show();
		}else
		{
			GO.calendar.openState=displayConfig;
			mp.show();
		}
//	}else
//	{
//		GO.calendar.openState=displayConfig;
//		GO.mainLayout.on('render', function(){
//			 GO.mainLayout.openModule('calendar');
//		});
//	}
	
}


GO.calendar.handleMeetingRequest=function(responseResult){
	
	if (responseResult.askForMeetingRequestForNewParticipants) {
		Ext.Msg.show({
			title:t("Notify participants?", "calendar"),
			msg: t("Do you want to notify by e-mail only the participants that you just added?", "calendar"),
			buttons: {
				yes:t("New participants", "calendar"),
				no:t("All participants", "calendar"),
				cancel:t("No participants", "calendar")
			},
			fn: function(buttonId, text, config){
				if(buttonId=='yes'){
					GO.request({
						url:"calendar/event/sendMeetingRequest",
						params:{
							event_id:responseResult.id,
							new_participants_only: true,
							is_update:responseResult.is_update
						}
					})
				} else if (buttonId=='no') {
					GO.request({
						url:"calendar/event/sendMeetingRequest",
						params:{
							event_id:responseResult.id,
							is_update:responseResult.is_update
						}
					})
				} else {
					GO.request({
						url:"calendar/participant/clearNewParticipantsSession"
					});
				}
			},
			scope: this,
			icon: Ext.MessageBox.QUESTION
	 });
	} else if (responseResult.askForMeetingRequest){
		
		Ext.Msg.show({
			title:t("Notify participants?", "calendar"),
			msg: t("Would you like to notify the participants by e-mail?", "calendar"),
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, config){
				if(buttonId=='yes'){
					GO.request({
						url:"calendar/event/sendMeetingRequest",
						params:{
							event_id:responseResult.id,
							is_update:responseResult.is_update
						}
					})
				} else {
					GO.request({
						url:"calendar/participant/clearNewParticipantsSession"
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
	 });
	} else {
		GO.request({
			url:"calendar/participant/clearNewParticipantsSession"
		});
	}
}

GO.calendar.showInfo = function (eventId) {

	var eventPanel = new GO.calendar.EventPanel();
	var win = new go.Window({
		title: t("Appointment"),
		collapsible: true,
		maximizable: true,
		resizable: true,
		width: 500,
		height: 10000,
		layout:'fit',
		items:[eventPanel]
	});
	win.show();
	eventPanel.load(eventId);
	
}

GO.calendar.importIcs = function(config) {
	GO.calendar.showEventDialog({
		url: GO.url('calendar/event/loadICS'),
		params: {
			file_id: config.id
			// account_id: panel.account_id,
			// mailbox: panel.mailbox,
			// uid: panel.uid,
			// number: attachment.number,
			// encoding: attachment.encoding
		}
	});
}