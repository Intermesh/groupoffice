/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: EventDialog.js 22352 2018-02-09 15:03:23Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.EventDialog = function(calendar) {
	this.calendar = calendar;

	this.linkEntityId = 0;
	this.linkEntity = false;

	this.buildForm();

	this.beforeInit();

	this.goDialogId='event';
	this.resourceGroupsStore = new GO.data.JsonStore({
		url:GO.url('calendar/group/groupsWithResources'),
		fields: ['id','resources','name','customfields'],
		remoteSort: true
	});

	this.resourceGroupsStore.on('load', function()
	{		
		this.buildAccordion();
	}, this);

	var items  = [
		this.propertiesPanel,
		this.optionsPanel,
		this.participantsPanel,
		this.resourcesPanel
	];

	//This field is added for filtering the custom field panels. It needs the group_id to be in the form values to filter on this value.
	this.propertiesPanel.add(new Ext.form.Hidden({name: "group_id"})); 
	
	
	if(go.Modules.isAvailable("legacy", "comments")){
		this.commentsGrid = new GO.comments.CommentsGrid({title:t("Comments", "comments")});
		items.push(this.commentsGrid);
	}
	
	this.tabPanel = new Ext.TabPanel({
		activeTab : 0,
		deferredRender : false,
		border : false,
		anchor : '100% 100%',
		hideLabel : true,
		enableTabScroll : true,
		items : items,
		defaults:{
			forceLayout:true
		}
	});
	
	
	go.customfields.CustomFields.getFormFieldSets("Event").forEach(function(fs) {
		//console.log(fs);
		if(fs.fieldSet.isTab) {
			fs.title = null;
			fs.collapsible = false;
			var pnl = new Ext.Panel({
				autoScroll: true,
				hideMode: 'offsets', //Other wise some form elements like date pickers render incorrectly.
				title: fs.fieldSet.name,
				items: [fs]
			});
			this.tabPanel.add(pnl);
		} else {
			this.propertiesPanel.add(fs);
		}
	}, this);

	this.formPanel = new Ext.form.FormPanel({
		waitMsgTarget : true,
		url : GO.url('calendar/event/load'),
		border : false,
		baseParams : {},
		items : this.tabPanel
	});

	this.initWindow();

	this.addEvents({
		'save' : true,
		'show' : true
	});

	this.win.render(Ext.getBody());

}

Ext.extend(GO.calendar.EventDialog, Ext.util.Observable, {
	resources_options : '',
	beforeInit : function(){

	},

	setLinkEntity: function(config) {
		this.linkEntity = config.entity;
		this.linkEntityId = config.entityId;

		//for project and contacts
		if(config.data.name) {
			this.on('load', function() {
				this.subjectField.setValue(config.data.name);
			}, this, {single: true});
			
		}
	},

	initWindow : function() {
		var focusSubject = function() {
			this.subjectField.focus();
		}
		
		this.fileBrowseButton = '';
		if(go.Modules.isAvailable("legacy", "files")) {
			this.fileBrowseButton = new GO.files.FileBrowserButton({
				text: t("Files", "files"),
				iconCls: 'ic-folder',
				model_name:"GO\\Calendar\\Model\\Event"
			});
			
			this.fileBrowseButton.on('click',function(){
				if (this.privateCB.getValue() && !GO.files.privateWarned) {
					GO.files.privateWarned=true;
					alert(t("Note that if the event is marked as private, the files of this event are still accessible by users who have permissions to this event's calendar.", "calendar"));
				}
			},this);
		}

		this.win = new GO.Window({
			layout : 'fit',
			modal : false,
			resizable : true,
			collapsible:true,
			maximizable:true,
			width : dp(672),
			height : dp(672),
			stateId:'calendar_event_dialog',
			closeAction : 'hide',
			title : t("Appointment", "calendar"),
			items : this.formPanel,
			focus : focusSubject.createDelegate(this),
			buttonAlign:'left',
			buttons : [
				this.createLinkButton = new go.links.CreateLinkButton(),
				this.fileBrowseButton,
				'->',{
					text : t("Apply"),
					handler : function() {
						this.submitForm(false, {
							'check_conflicts' : 1
						} );
					},
					scope : this
				},{
					text : t("Save"),
					iconCls: 'ic-done',
					handler : function() {
						this.submitForm(true, {
							'check_conflicts' : 1
						} );
					},
					scope : this
				}
			]
		});

		this.win.on("hide", function() {
			this.initialized = false;
			this.createLinkButton.reset();
		}, this);
	},

	files_folder_id : 0,



	initialized : false,
	
	isVisible : function() {
		return this.win.isVisible();
	},

	show : function(config) {
		config = config || {};

		GO.dialogListeners.apply(this);		
		
		this.win.show();

		if(!config.initialization) {
			this.setEventId(config.event_id);
		}

		if(!this.initialized){
			
			
			GO.request({
				url: 'core/multiRequest',
				maskEl:this.win.getEl(),
				params:{
					requests:Ext.encode({
						groups:{r:'calendar/group/store'},
						//categories:{r:'calendar/category/store'},
						resources:{r:'calendar/group/groupsWithResources'}						
					})
				},
				success: function(options, response, result)
				{
					GO.calendar.groupsStore.loadData(result.groups);
					this.resourceGroupsStore.loadData(result.resources);				
					
					this.initialized=true;
					config.initialization = true;
					this.show(config);
				},
				scope:this
			});
			return false;
		}
        
		if (config.oldDomId) {
			this.oldDomId = config.oldDomId;
		} else {
			this.oldDomId = false;
		}
		// propertiesPanel.show();



		//tmpfiles on the server ({name:'Name',tmp_file:/tmp/name.ext} will be attached)
		this.formPanel.baseParams.tmp_files = config.tmp_files ? Ext.encode(config.tmp_files) : '';

		this.formPanel.form.reset();
        
		this.tabPanel.setActiveTab(0);

		if (!config.event_id) {
			config.event_id = 0;
		}		

		var params = config.params || {};
		
		if(!config.event_id) {
			if(!GO.util.empty(config.calendar_id))
				params.calendar_id=config.calendar_id;

			if(config.values && config.values.start_date){
				params.start_date=config.values.start_date.format(GO.settings.date_format);				
				params.start_time=config.values.start_date.format(GO.settings.time_format);				
				params.end_date=config.values.end_date.format(GO.settings.date_format);				
				params.end_time=config.values.end_date.format(GO.settings.time_format);				
			}
			params.linkEntityId = this.linkEntityId;
			params.linkEntity = this.linkEntity;
		}
		
		//These parameters are present when a user edits a single occurence of a repeating event
		params.exception_date=config.exception_date;
		
		
			// if the newMenuButton from another passed a linkTypeId then set this
		// value in the select link field


		//if (config.event_id > 0) {
			this.formPanel.load({
				params:params,
				url : config.url || GO.url('calendar/event/load'),
				waitMsg:t("Loading..."),
				success : function(form, action) {
					//this.win.show();
					this.recurrencePanel.reset();
					
					this.setData(action);
					
					if(action.result.data.enable_reminder){
						this.reminderComposite.setDisabled(false);
					} else {
						this.reminderComposite.setDisabled(true);
					}
					
					// If this is a recurrence and the following is true (action.result.data.exception_for_event_id and action.result.data.exception_date are set and not empty)
					if(action.result.data.exception_date){
						this.formPanel.form.baseParams['thisAndFuture'] = config.thisAndFuture;
						this.setEventId(0);
						this.formPanel.form.baseParams['exception_for_event_id'] = action.result.data.exception_for_event_id;
						this.formPanel.form.baseParams['exception_date'] = action.result.data.exception_date;
					} 
					
					// Disable the recurrence panel when an event is an exception of an other event or if it is a recurrence item itself.
					if(action.result.data.exception_date || action.result.data.exception_for_event_id > 0){
						this.recurrencePanel.setDisabled(true);
					} else {
						this.recurrencePanel.setDisabled(false);
					}
					
					if(go.Modules.isAvailable("legacy", "comments")){
						if(action.result.data['id'] > 0){
							if (!GO.util.empty(action.result.data['action_date'])) {
								this.commentsGrid.actionDate = action.result.data['action_date'];
							} else {
								this.commentsGrid.actionDate = false;
							}
							this.commentsGrid.setLinkId(action.result.data['id'], 'GO\\Calendar\\Model\\Event');
							this.commentsGrid.store.load();
							this.commentsGrid.setDisabled(false);
						} else {
							this.commentsGrid.setDisabled(true);
						}
					}
								
					this.recurrencePanel.setStartDate(this.startDate.getValue());
					this.recurrencePanel.changeRepeat(action.result.data.freq);
					this.recurrencePanel.setDaysButtons(action.result.data);
					
					this.setValues(config.values);
					//this.setWritePermission(action.result.data.write_permission);
					//this.selectCalendar.setValue(action.result.data.calendar_id);
					this.selectCalendar.setRemoteText(action.result.remoteComboTexts.calendar_id);
					
					this.setPermissionLevel(action.result.data.permission_level);

					if(action.result.group_id == 1) {
						//TODO
						this.toggleFieldSets(action.result.data.resources_checked);
					}

					this.selectCategory.setCalendarId(action.result.data.calendar_id);
					this.selectCategory.setRemoteText(action.result.remoteComboTexts.category_id);
					//this.selectCategory.store.load();

					//this.selectCategory.container.up('div.x-form-item').setDisplayed(this.formPanel.form.baseParams['group_id']==1);
					
					if(action.result.data.category_name)
						this.selectCategory.setRemoteText(action.result.data.category_name);

//					this.has_other_participants=action.result.data.has_other_participants;					
					if(this.resourceGroupsStore.data.items.length == 0 || action.result.group_id != '1') {
						this.tabPanel.hideTabStripItem('resources-panel');
						
						
					} else {
						this.tabPanel.unhideTabStripItem('resources-panel'); 
					}
					
					if(action.result.group_id != '1' && !action.result.data.resourceGroupAdmin) {
						this.eventStatus.disable();
					}
										
					this.participantsPanel.store.loadData(action.result.participants);
					
					//hide participants for resources
					if(action.result.group_id != '1')
						this.tabPanel.hideTabStripItem(this.participantsPanel);
					else
						this.tabPanel.unhideTabStripItem(this.participantsPanel);


					this.fireEvent('load', this);

				},
				failure : function(form, action) {
					Ext.Msg.alert(t("Error"), action.result.feedback)
				},
				scope : this

			});

		this.fireEvent('show', this);
	},
	
	
	/**
	 * Dummy funtion that is used to create a sequence in other modules.
	 * 
	 * @param array data
	 * @returns {undefined}
	 */
	setData : function(data){
		
	},
	
	setPermissionLevel : function(permissionLevel){
		// Disable the eventStatus select box and set it to the default "NEEDS-ACTION" value
		if(this.event_id == 0 && permissionLevel == GO.permissionLevels.create){
			this.eventStatus.setValue('NEEDS-ACTION');
			this.eventStatus.setDisabled(true);
		}else{
			this.eventStatus.setDisabled(false);
		}
	},
	
	toggleFieldSets : function(resources_checked)
	{
		for(var i=0; i<this.resourceGroupsStore.data.items.length; i++)
		{
			var record = this.resourceGroupsStore.data.items[i].data;
			var resources = record.resources;

			for(var j=0; j<resources.length; j++)
			{
				var p = this.resourcesPanel.getComponent('group_'+record.id);
				var r = 'resource_'+resources[j].id;
				var c = p.getComponent(r);

				if(resources_checked && (resources_checked.indexOf(resources[j].id) != -1))
				{
					c.expand();
				}else
				{
					var l = c.getComponent('status_'+resources[j].id);
					l.setValue(t("New", "calendar"));

					c.collapse();
				}
			}
		}
	},
//	setWritePermission : function(writePermission) {
//		this.win.buttons[0].setDisabled(!writePermission);
//		this.win.buttons[1].setDisabled(!writePermission);
//	},

	setValues : function(values) {
		if (values) {
			for (var key in values) {
				var field = this.formPanel.form.findField(key);
				if (field) {
					field.setValue(values[key]);
				}
			}
		}
	},

	setEventId : function(event_id) {		
		this.formPanel.form.baseParams['id'] = event_id;
		
		delete this.formPanel.form.baseParams['exception_for_event_id'];
		delete this.formPanel.form.baseParams['exception_date'];
		
		this.event_id = this.currentId = event_id; //currentId is for LinkToButton
		
		this.entity = "event"; //for linktobutton

		this.participantsPanel.setEventId(event_id);

//		this.linkBrowseButton.setDisabled(event_id < 1);
		if(this.fileBrowseButton)
			this.fileBrowseButton.setId(event_id);

		this.createLinkButton.setEntity("Event", event_id);
	},

	setCurrentDate : function() {
		var formValues = {};

		var date = new Date();

		formValues['start_date'] = date.format(GO.settings['date_format']);
		formValues['start_time'] = date.format(GO.settings.time_format);
		
		formValues['end_date'] = date.format(GO.settings['date_format']);
		formValues['end_time'] = date.add(Date.HOUR, 1).format(GO.settings.time_format);
		
		this.formPanel.form.setValues(formValues);
	},

//	has_other_participants:0,
	submitForm : function(hide, config) {

		if(!config)
		{
			config = {};
		}

		this.hide = hide;

		var params = {
			'task' : 'save_event',
			'submitresources':true,
			'check_conflicts' : typeof(config.check_conflicts)!='undefined' ? config.check_conflicts : null
		};

//		if(this.participantsPanel.store.loaded)
//		{
		var gridData = this.participantsPanel.getGridData();
		params.participants=Ext.encode(gridData);
		
		this.formPanel.form.submit({
			url : GO.url('calendar/event/submit'),
			params : params,
			waitMsg : t("Saving..."),
			success : function(form, action) {

				if (action.result.id) {
					this.files_folder_id = action.result.files_folder_id;
					this.setEventId(action.result.id);
				}

				var startDate = this.getStartDate();

				var endDate = this.getEndDate();

				var newEvent = {
					id : Ext.id(),
					calendar_id : this.selectCalendar.getValue(),
					calendar_name : Ext.util.Format.htmlEncode(this.selectCalendar.getRawValue()),
					event_id : this.event_id,
					name : Ext.util.Format.htmlEncode(this.subjectField.getValue()),
					start_time : startDate.format('Y-m-d H:i'),
					end_time : endDate.format('Y-m-d H:i'),
					startDate : startDate,
					endDate : endDate,
					description : Ext.util.Format.htmlEncode(GO.util.nl2br(this.formPanel.form
						.findField('description').getValue()).replace(/\n/g,'')),
					background : this.formPanel.form.findField('background')
					.getValue(),
					location : Ext.util.Format.htmlEncode(this.formPanel.form.findField('location')
					.getValue()),
					repeats : this.formPanel.form.findField('freq')
					.getValue() !="",
					'private_enabled' : this.formPanel.form.findField('private').getValue(),
					'has_reminder':!GO.util.empty(this.reminderValue.getValue()),
					
					model_name:"GO\\Calendar\\Model\\Event",
					all_day_event:this.formPanel.form.findField('all_day_event').getValue() ? true : false,
					exception_event_id : this.formPanel.form.baseParams['exception_event_id']
//					has_other_participants: this.participantsPanel.invitationRequired()
				};
				
				if(action.result.background){
					newEvent.background=action.result.background;
				}

				if(action.result.permission_level){
					newEvent.permission_level=action.result.permission_level;
				}

				if(!GO.util.empty(action.result.status_color))
					newEvent.status_color = action.result.status_color;
				
				if(!GO.util.empty(action.result.status))
					newEvent.status = action.result.status;
				
				if(!GO.util.empty(action.result.is_organizer))
					newEvent.is_organizer = action.result.is_organizer;
					
				this.fireEvent('save', newEvent, this.oldDomId, action);
				
				this.createLinkButton.save();
				
				GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);

			

				if(action.result.feedback){
					Ext.MessageBox.alert(t("Error"), action.result.feedback);
				}else	if (hide) {
					this.win[this.win.closeAction]();	
				}

				if (config && config.callback) {
					config.callback.call(this, this, true);
				}

				if(this.win.closeAction != "close" && action.result.participants) {
					this.participantsPanel.store.loadData({results:action.result.participants});
				}
				
				
				GO.calendar.handleMeetingRequest(action.result);
				
				if(this.win.closeAction == "close") {	
					this.purgeListeners();					
				}

			},
			failure : function(form, action) {
				if (action.failureType == 'client') {
					var error = t("You have errors in your form. The invalid fields are marked.");
				} else {
					var error = action.result.feedback;
				}
				
				var me = this;

				if (error.indexOf('Ask permission') != -1) {
					Ext.Msg.show({
						title: t("Ignore conflict?", "calendar"),
						msg: t("This event conflicts with another event in your calender. Save this event anyway?", "calendar"),
						buttons: Ext.Msg.YESNO,
						fn: function(btn) {
							me.handlePrompt(btn, me);
						},
						animEl: 'elId',
						icon: Ext.MessageBox.QUESTION
					});
				} else if (error.indexOf('Resource conflict') != -1) {
					error = t("One or more resources in this event are already in use at the same time:</br>", "calendar");
					if (config && config.callback) {
						config.callback.call(this, this, false);
					}
					error = error+'<ul>';
					for (var i in action.result.resources) {
						if (!isNaN(i))
							error = error+'<li> - '+action.result.resources[i]+'</li>';
					}
					error = error+'</ul>';
					Ext.MessageBox.alert(t("Resource conflict", "calendar"), error);
				} else {
					if (config && config.callback) {
						config.callback.call(this, this, false);
					}
					Ext.MessageBox.alert(t("Error"), error);
				}
			},
			scope : this
		});
	},

	handlePrompt : function(btn, dlg) {
		if (btn=='yes') {
			dlg.submitForm(dlg.hide,{
				'check_conflicts':'0'
			});
		}
	},

	getStartDate : function() {

		var startDate = this.startDate.getValue();
//		if (!this.formPanel.form.findField('all_day_event').getValue()) {
			startDate = Date.parseDate(startDate.format('Y-m-d')+' '+this.formPanel.form.findField('start_time').getValue(),'Y-m-d '+GO.settings.time_format);
//		}

		return startDate;
	},

	getEndDate : function() {
		var endDate = this.endDate.getValue();
//		if (!this.formPanel.form.findField('all_day_event').getValue()) {
			endDate = Date.parseDate(endDate.format('Y-m-d')+' '+this.formPanel.form.findField('end_time').getValue(),'Y-m-d '+GO.settings.time_format);
//		}
		return endDate;
	},

	checkDateInput : function() {

		var eD = this.endDate.getValue();
		var sD = this.startDate.getValue();

		if(!sD) return;

		if (sD > eD) {
			this.endDate.setValue(sD);
		}

		if (sD.getElapsed(eD) == 0) {
			
			var sdWithTime = sD.format('Y-m-d')+' '+this.startTime.getValue();
			var sT = Date.parseDate(sdWithTime, 'Y-m-d '+GO.settings.time_format);

			var edWithTime = eD.format('Y-m-d')+' '+this.endTime.getValue();
			var eT = Date.parseDate(edWithTime, 'Y-m-d '+GO.settings.time_format);

			if(sT>=eT){
				
				var ed = sT.add(Date.HOUR, 1);
				
				this.endTime.setValue(ed.format(GO.settings.time_format));
				this.endDate.setValue(ed);
			}
		}
		
		this.participantsPanel.reloadAvailability();
	},

	buildForm : function() {

	
		this.subjectField = new Ext.form.TextField({
			//name : 'subject',
			name : 'name',
			allowBlank : false,
			fieldLabel : t("Subject")
		});

		this.locationField = new Ext.form.TextField({
			name : 'location',
			allowBlank : true,
			fieldLabel : t("Location")
		});
		this.startDate = new Ext.form.DateField({
			name : 'start_date',
			width : 120,
			format : GO.settings['date_format'],
			allowBlank : false,			
			listeners : {
				change : {
					fn : this.checkDateInput,
					scope : this
				}
			}
		});

		this.startTime = new Ext.form.TimeField({
			increment: 15,
			format:GO.settings.time_format,
			name:'start_time',
			width:dp(120),
			hideLabel:true,
			autoSelect :true,			
			listeners : {
				change : {
					fn : this.checkDateInput,
					scope : this
				}
			}
		});

		this.endTime = new Ext.form.TimeField({
			increment: 15,
			format:GO.settings.time_format,
			name:'end_time',
			width:dp(120),
			hideLabel:true,
			autoSelect :true,			
			listeners : {
				change : {
					fn : this.checkDateInput,
					scope : this
				}
			}
		});	

		this.endDate = new Ext.form.DateField({
			name : 'end_date',
			width : 120,
			format : GO.settings['date_format'],
			allowBlank : false,			
			listeners : {
				change : {
					fn : this.checkDateInput,
					scope : this
				}
			}
		});
		
		this.checkAvailabilityButton = new Ext.Button({
			iconCls : 'ic-event-available',
			text : t("Check availability", "calendar"),
			handler : function() {
				this.checkAvailability();
			},
			scope : this
		})

		this.allDayCB = new Ext.ux.form.XCheckbox({
			boxLabel : t("Time is not applicable", "calendar"),
			name : 'all_day_event',
			checked : false,
			width : 'auto',
			labelSeparator : '',
			hideLabel : true
		});

		this.allDayCB.on('check', function(checkbox, checked) {
			this.startTime.setDisabled(checked);
			this.endTime.setDisabled(checked);
			
		}, this);

		this.eventStatus = new Ext.form.ComboBox({
			hiddenName : 'status',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			width : 148,
			forceSelection : true,			
			mode : 'local',
			value : 'CONFIRMED',
			valueField : 'value',
			displayField : 'text',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [
				['NEEDS-ACTION', t("statuses", "calendar")["NEEDS-ACTION"]],
				//['ACCEPTED', t("Accepted", "calendar")],
				['CONFIRMED', t("statuses", "calendar")["CONFIRMED"]],
				//['DECLINED', t("Declined", "calendar")],
				['TENTATIVE',	t("statuses", "calendar")["TENTATIVE"]],
				['CANCELLED',	t("statuses", "calendar")["CANCELLED"]]
//				['DELEGATED',	t("Delegated", "calendar")]
			]
			}),
			listeners: {
				scope:this,
				change:function(cb, newValue){
					if(this.formPanel.form.baseParams['group_id']>1){
						if(newValue=='CONFIRMED'){
							this.colorField.setValue('CCFFCC');
						}else
						{
							this.colorField.setValue('FF6666');
						}
					}
				}
			}
		});

		this.busy = new Ext.ux.form.XCheckbox({
			boxLabel : t("Show as busy", "calendar"),
			name : 'busy',
			checked : true,
			width : 'auto',
			labelSeparator : '',
			hideLabel : true
		});

		this.selectCategory = new GO.form.ComboBoxReset({
			pageSize: parseInt(GO.settings.max_rows_list),
			hiddenName:'category_id',
			fieldLabel:t("Category", "calendar"),
			value:'',
			valueField:'id',
			displayField:'name',
			store: GO.calendar.globalCategoriesStore,
			mode:'remote',
			triggerAction:'all',
			emptyText:t("Select category", "calendar"),
			editable:false,
			selectOnFocus:true,
			forceSelection:true,
			allowBlank: !GO.calendar.categoryRequired,
			setCalendarId : function(calendar_id){
				this.clearLastSearch();
				this.store.baseParams.calendar_id=calendar_id;
			},
			tpl:'<tpl for="."><div class="x-combo-list-item"><div style="float:left;width:20px;margin-right:5px;background-color:#{color}">&nbsp;</div>{name}</div></tpl>'
		});

		this.selectCategory.on('select', function(combo, record)
		{			
			this.colorField.setValue(record.data.color);
		}, this);
		
		
		this.privateCB = new Ext.ux.form.XCheckbox({
			boxLabel : t("Private", "calendar"),
			name : 'private',
			checked : false,
			width : 'auto',
			labelSeparator : '',
			hideLabel : true
		});
		

		this.propertiesPanel = new Ext.Panel({
			hideMode : 'offsets',
			title : t("Properties"),
			cls:'go-form-panel',
			layout : 'form',
			autoScroll : true,
			defaults: { anchor: '0'},
			items : [
				{
				xtype: 'compositefield',
				items: [this.selectCalendar = new GO.calendar.SelectCalendar({
					dontQueryCalendar :  true,
					valueField : 'id',
					displayField : 'name',
					flex:1,
					typeAhead : true,
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					forceSelection : true,
					allowBlank : false,
					listeners:{
						scope:this,
						select:function(sc, record){

							var newValue = record.data.id;

							var record = sc.store.getById(newValue);

							this.selectCategory.setCalendarId(newValue);
							this.selectCategory.reset();
							// Set the permissionlevel so we know if we have the right permissions
							if(record)
								this.setPermissionLevel(record.data.permissionLevel);

							this.participantsPanel.reloadOrganizer();

							go.customfields.CustomFields.filterFieldSets(this.formPanel);
						}
					}
				}),
				this.colorField = new GO.form.ColorField({
					hideLabel : true,
					name : 'background',
					value : "EBF1E2"
				})]
			},
			this.subjectField,
			this.locationField,
			{	
				xtype : 'compositefield',
				fieldLabel:t("Start"),
				items : [this.startDate,this.startTime,this.allDayCB
				]
			},{
				fieldLabel:t("End"),
				xtype : 'compositefield',				
				items : [this.endDate, this.endTime,this.checkAvailabilityButton
				]
			},{
				xtype : 'compositefield',
				fieldLabel : t("Status", "calendar"),
				items : [
				this.eventStatus,
				this.busy,
				this.privateCB
				]
			},
			this.selectCategory,
//			new GO.form.PlainField({
//				fieldLabel: t("Owner"),
//				value: GO.settings.name,
//				name:'user_name'
//			}),
			{
				xtype:'textarea',
				fieldLabel:t("Description"),
				name : 'description',
				grow: true
			}]

		});
		// Start of recurrence tab
		this.recurrencePanel = new go.form.RecurrenceFieldset();

		var reminderValues = [['0', t("No reminder", "calendar")]];

		for (var i = 1; i < 60; i++) {
			reminderValues.push([i, i]);
		}
		
		this.reminderValue = new GO.form.NumberField({
			decimals:0,
			name : 'reminder_value',
//			minValue:1,
			width : 50,
			value : GO.calendar.defaultReminderValue
		});

		this.reminderMultiplier = new Ext.form.ComboBox({
			hiddenName : 'reminder_multiplier',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			width : 148,
			forceSelection : true,
			mode : 'local',
			value : GO.calendar.defaultReminderMultiplier,
			valueField : 'value',
			displayField : 'text',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [['60', t("Minutes")],
				['3600', t("Hours")],
				['86400', t("Days")],
				['604800', t("Weeks")]

				]
			}),
			hideLabel : true,
			labelSeperator : ''
		});
		
		this.reminderComposite = new Ext.form.CompositeField({
			style:'margin-top:10px;',
			fieldLabel : t("Reminder", "calendar"),
			items : [this.reminderValue,this.reminderMultiplier]
		});
		
		this.enableReminderCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : t("Enable reminder for this event", "calendar"),
			name : 'enable_reminder',
			width : 'auto',
			hideLabel : true,
			listeners : {
				check : {
					fn : function(cb, checked) {
						this.reminderComposite.setDisabled(!checked);
					},
					scope : this
				}
			}
		});

		this.participantsPanel = new GO.calendar.ParticipantsPanel(this);

		this.optionsPanel = new Ext.Panel({
			layout:"form",
			title : t("Options", "calendar"),
			hideMode : 'offsets',
			border:false,
			items:[
				this.recurrencePanel,{
					xtype : 'fieldset',
					autoHeight : true,
					layout : 'form',
					title : t("Reminder", "calendar"),
					items : [
						this.enableReminderCheckbox,
						this.reminderComposite
					]
				}
			]
		});

		this.resourcesPanel = new Ext.Panel({
			id:'resources-panel',
			title:t("Resources", "calendar"),
			border:true,
			//layout:'accordion',
			forceLayout:true,
			autoScroll:true,
			tbar: [
				this.checkAvailabilityButton = new Ext.Button({
					iconCls : 'ic-event-available',
					text : t("Check availability", "calendar"),
					handler : function() {
						this.checkAvailability();
					},
					scope : this
				})
			],
//			layoutConfig:{
//				titleCollapse:true,
//				animate:false,
//				activeOnTop:false
//			},
			defaults:{
				forceLayout:true,
				border:false
			}
		});
		this.resourcesPanel.on('show', function(){
			this.tabPanel.doLayout();
		},this);

        
	},
	

	buildAccordion : function()
	{
		this.resourcesPanel.removeAll(true);
		this.resourcesPanel.forceLayout=true;
		
		var newFormField;
		for(var i=0; i<this.resourceGroupsStore.getCount(); i++)
		{
			var record = this.resourceGroupsStore.data.items[i].data;
			var resourceFieldSets = [];
			var resources = record.resources;

			for(var j=0; j<resources.length; j++)
			{
				var resourceOptions = [];

				var pfieldStatus = new GO.form.PlainField({
					id:'status_'+resources[j].id,
					name:'status_'+resources[j].id,
					fieldLabel: t("Status", "calendar")
				});
				resourceOptions.push(pfieldStatus);
				this.formPanel.form.add(pfieldStatus);

					if(go.Modules.isAvailable("core", "customfields"))
					{
						var fieldSets = go.customfields.CustomFields.getFormFieldSets("Calendar");
			
						for(var l=0; l < fieldSets.length; l++)
						{
								var cf = go.customfields.CustomFields.getFormFields(fieldSets[l].fieldSet.id);
								var formFields = [new GO.form.PlainField({
										hideLabel: true,
										value: '<b>'+fieldSets[l].fieldSet.name+'</b>'
									})];
								for(var m=0; m<cf.length; m++)
								{
									if (typeof(resources[j][cf[m].field.databaseName]) != 'undefined') {
//										if (cf[m].type == 'checkbox' && resources[j][cf[m].dataname]==t("No")) {
//											continue;
//										}
//										if (cf[m].type == 'html' && resources[j][cf[m].dataname]=='<br>') {
//											continue;
//										}
										newFormField = new GO.form.PlainField({
											fieldLabel: cf[m].field.name,
											value: resources[j][cf[m].field.databaseName]
										});
										formFields.push(newFormField);
									}
								}
								if (formFields.length>1) {
									for (var n=0; n<formFields.length; n++) {
										resourceOptions.push(formFields[n]);
									}
								}
						}
						
						
						var fieldSets = go.customfields.CustomFields.getFormFieldSets("Event"), length = fieldSets.length;
						
						if(length) {
							for(var l=0; l<length; l++)
							{		
								var cf = go.customfields.CustomFields.getFormFields(fieldSets[l].fieldSet.id);
								resourceOptions.push(new GO.form.PlainField({
										hideLabel: true,
										value: '<b>'+fieldSets[l].fieldSet.name+'</b>'
									}));
								for(var m=0; m<cf.length; m++)
								{

									cf[m].name = 'resource_options['+resources[j].id+']['+cf[m].field.databaseName+']';
									cf[m].id ='resource_options['+resources[j].id+']['+cf[m].field.databaseName+']';


									/*
									 * Customfields might return a simple object instead of an Ext.component.
									 * So check if it has events otherwise create the Ext component.
									 */
									if(!cf[m].events){
										cf[m] = Ext.ComponentMgr.create(cf[m], 'textfield');
									}

									resourceOptions.push(cf[m]);
									this.formPanel.form.add(cf[m]);
								}
								
							}
						}
					}
					else
					{
						resourceOptions.push(new GO.form.PlainField({
							name:'no_fields_'+resources[j].id,
							hideLabel:true,
							value: t("There are no extra options available.", "calendar")
						}));
					}
				

				resourceFieldSets.push({
					xtype:'fieldset',
					checkboxToggle:true,
					checkboxName:'resources['+resources[j].id+']',
					title:resources[j].name,
					id:'resource_'+resources[j].id,
					autoHeight:true,
					collapsed:true,
					forceLayout:true,
					items:resourceOptions,
					bodyStyle: 'padding-left: 20px'
				});
			}
			
			
			var resourcePanel = new Ext.Panel({
				cls:'go-form-panel',
				id:'group_'+record.id,
				layout:'form',
				autoScroll:true,
				forceLayout:true,
				title:record.name,
				items:resourceFieldSets
			});
            
			this.resourcesPanel.add(resourcePanel);			
		}		
		this.tabPanel.doLayout();
	},

	getResourceIds : function() {
		var components = this.formPanel.findBy(function(component,container){
			if (!GO.util.empty(component.checkboxName) && component.checkboxName.substring(0,10)=='resources[' && !component.collapsed) {
				return true;
			}
			return false;
		}, this);
		
		var idsArr = new Array();
		
		for (var i=0; i<components.length; i++) {
			var stringArr = components[i].id.substring(9).split(']');
			idsArr.push(stringArr[0]);
		}
		
		return idsArr;
	},
						
	checkAvailability : function() {
		if (!this.availabilityWindow) {
			this.availabilityWindow = new GO.calendar.AvailabilityCheckWindow();
			this.availabilityWindow.on('select', function(dataview, index, node) {
				this.startDate.setValue(Date.parseDate(
					dataview.store.baseParams.date,
					GO.settings.date_format));
				this.endDate.setValue(Date.parseDate(
					dataview.store.baseParams.date,
					GO.settings.date_format));
					
				var oldStartTime = Date.parseDate(this.startTime.getValue(), GO.settings.time_format);
				var oldEndTime = Date.parseDate(this.endTime.getValue(), GO.settings.time_format);
				var elapsed = oldEndTime.getElapsed(oldStartTime);

				var time = Date.parseDate(node.id.substr(4), 'G:i');
				this.startTime.setValue(time.format(GO.settings.time_format));
				this.endTime.setValue(time.add(Date.MILLI, elapsed).format(GO.settings.time_format));
				
				this.tabPanel.setActiveTab(0);
				this.participantsPanel.reloadAvailability();
				this.availabilityWindow.hide();
			}, this);
		}
		
		this.availabilityWindow.show({
			participantData:Ext.encode(this.participantsPanel.getParticipantData()),
			date : this.startDate.getRawValue(),
			event_id : this.event_id,
			resourceIds : Ext.encode(this.getResourceIds())
		});
	}
	
});
