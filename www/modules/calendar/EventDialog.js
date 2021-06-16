/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: EventDialog.js 21560 2017-10-19 11:53:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.EventDialog = function(calendar) {
	this.calendar = calendar;

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
	this.recurrencePanel,
	this.optionsPanel,
	this.participantsPanel,
	this.resourcesPanel
	];

	if(GO.customfields && GO.customfields.types["GO\\Calendar\\Model\\Event"])
	{
		for(var i=0;i<GO.customfields.types["GO\\Calendar\\Model\\Event"].panels.length;i++)
		{
			items.push(GO.customfields.types["GO\\Calendar\\Model\\Event"].panels[i]);
		}
	}
	
	if(GO.comments){
		this.commentsGrid = new GO.comments.CommentsGrid({title:GO.comments.lang.comments});
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

	initWindow : function() {
		var focusSubject = function() {
			this.subjectField.focus();
		}

		var tbar = [this.linkBrowseButton = new Ext.Button({
			iconCls : 'btn-link',
			cls : 'x-btn-text-icon',
			text : GO.lang.cmdBrowseLinks,
			disabled : true,
			handler : function() {
				if(!GO.linkBrowser){
					GO.linkBrowser = new GO.LinkBrowser();
				}
				GO.linkBrowser.show({
					model_id : this.event_id,
					model_name : "GO\\Calendar\\Model\\Event",
					folder_id : "0"
				});
			},
			scope : this
		})];

		if (GO.files) {
			tbar.push(this.fileBrowseButton = new GO.files.FileBrowserButton({
				model_name:"GO\\Calendar\\Model\\Event"
			}));
			
			this.fileBrowseButton.on('click',function(){
			if (this.privateCB.getValue() && !GO.files.privateWarned) {
				GO.files.privateWarned=true;
				alert(GO.calendar.lang['eventPrivateChecked']);
			}
		},this);
		}
		
		tbar.push(this.checkAvailabilityButton = new Ext.Button({
			iconCls : 'btn-availability',
			text : GO.calendar.lang.checkAvailability,
			cls : 'x-btn-text-icon',
			handler : function() {
				this.checkAvailability();
			},
			scope : this
		}));

		this.win = new GO.Window({
			layout : 'fit',
			modal : false,
			tbar : tbar,
			resizable : true,
			collapsible:true,
			maximizable:true,
			width : 620,
			height : 450,
			id:'calendar_event_dialog',
			closeAction : 'hide',
			title : GO.calendar.lang.appointment,
			items : this.formPanel,
			focus : focusSubject.createDelegate(this),
			buttons : [{
				text : GO.lang.cmdOk,
				handler : function() {
					this.submitForm(true, { 
						'check_conflicts' : 1
					} );
				},
				scope : this
			}, {
				text : GO.lang.cmdApply,
				handler : function() {
					this.submitForm(false, { 
						'check_conflicts' : 1
					} );
				},
				scope : this
			}, {
				text : GO.lang.cmdClose,
				handler : function() {
					this.win.hide();
				},
				scope : this
			}]
		});
	},

	files_folder_id : 0,



	initialized : false,

	show : function(config) {

		config = config || {};

		GO.dialogListeners.apply(this);

		this.win.show();

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

		delete this.link_config;

		//tmpfiles on the server ({name:'Name',tmp_file:/tmp/name.ext} will be attached)
		this.formPanel.baseParams.tmp_files = config.tmp_files ? Ext.encode(config.tmp_files) : '';

		this.formPanel.form.reset();
        
		this.tabPanel.setActiveTab(0);

		if (!config.event_id) {
			config.event_id = 0;
		}		

		this.setEventId(config.event_id);	
		
		var params = config.params || {};
		
		if(!config.event_id){
			
			if(!GO.util.empty(config.calendar_id))
				params.calendar_id=config.calendar_id;

			if(config.values && config.values.start_date){
				params.start_date=config.values.start_date.format(GO.settings.date_format);				
				params.start_time=config.values.start_date.format(GO.settings.time_format);				
				params.end_date=config.values.end_date.format(GO.settings.date_format);				
				params.end_time=config.values.end_date.format(GO.settings.time_format);				
			}
		}		
		
		//These parameters are present when a user edits a single occurence of a repeating event
		params.exception_date=config.exception_date;
		
		
			// if the newMenuButton from another passed a linkTypeId then set this
		// value in the select link field
		if (config.link_config) {
			this.link_config = config.link_config;
			if (config.link_config.modelNameAndId) {
				this.selectLinkField.setValue(config.link_config.modelNameAndId);
				this.selectLinkField.setRemoteText(config.link_config.text);
				
				params.linkModelNameAndId= config.link_config.modelNameAndId;
			}		

			//if(this.subjectField.getValue()=='')
				//this.subjectField.setValue(config.link_config.text);
				
			params.name=config.link_config.text;			
		}
		

		//if (config.event_id > 0) {
			this.formPanel.load({
				params:params,
				url : config.url || GO.url('calendar/event/load'),
				waitMsg:GO.lang.waitMsgLoad,
				success : function(form, action) {
					//this.win.show();
					
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
					
					if(GO.comments){
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
					
					this.changeRepeat(action.result.data.freq);
					this.setValues(config.values);
					//this.setWritePermission(action.result.data.write_permission);
					//this.selectCalendar.setValue(action.result.data.calendar_id);
					this.selectCalendar.setRemoteText(action.result.remoteComboTexts.calendar_id);
					
					this.setPermissionLevel(action.result.data.permission_level);
					
					if(GO.customfields)
						GO.customfields.disableTabs(this.tabPanel, action.result);	

					if(action.result.group_id == 1)
					{
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

				},
				failure : function(form, action) {
					Ext.Msg.alert(GO.lang.strError, action.result.feedback)
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
	
//	updateResourcePanel : function()
//	{
//		var values = {};
//		var checked = [];		
//		
//		// save values before all items are removed (checkboxes + statuses)
//		if(this.win.isVisible())
//		{
//			if(GO.customfields && GO.customfields.types["GO\\Calendar\\Model\\Event"])
//			{
//				for(var i=0; i<this.resourceGroupsStore.data.items.length; i++)
//				{
//					var record = this.resourceGroupsStore.data.items[i].data;
//					var resources = record.resources;
//
//					for(var j=0; j<resources.length; j++)
//					{
//						var calendar_id = resources[j].id;
//						values['status_'+calendar_id] = this.formPanel.form.findField('status_'+calendar_id).getValue();
//
//						var p = this.resourcesPanel.getComponent('group_'+record.id);
//						var c = p.getComponent('resource_'+calendar_id);
//						if(!c.collapsed)
//						{
//							checked.push(calendar_id);
//						}
//
//						for(var k=0; k<record.fields.length; k++)
//						{
//							var field = record.fields[k];
//							if(field)
//							{
//								for(var l=0; l<GO.customfields.types["1"].panels.length; l++)
//								{
//									var cfield = 'cf_category_'+GO.customfields.types["1"].panels[l].category_id;
//									if(cfield == field)
//									{
//										var cf = GO.customfields.types["1"].panels[l].customfields;
//										for(var m=0; m<cf.length; m++)
//										{
//											var name = 'resource_options['+calendar_id+']['+cf[m].dataname+']';
//											var value = this.formPanel.form.findField(name).getValue();
//
//											values[name] = value;
//										}
//									}
//								}
//							}
//						}
//					}
//				}
//			}
//		}
//        
//		this.resourceGroupsStore.load({
//			callback:function()
//			{
//				if(this.win.isVisible())
//				{
//					if(checked)
//					{
//						this.toggleFieldSets(checked);
//					}
//
//					// after reload store set the values we saved earlier
//					this.setValues(values);
//
//					if(this.resourceGroupsStore.data.items.length == 0)
//					{
//						this.tabPanel.hideTabStripItem('resources-panel');
//						this.tabPanel.setActiveTab(0);
//					} else
//{
//						this.tabPanel.unhideTabStripItem('resources-panel');												
//					}
//				}
//			},
//			scope:this
//		});
//	},
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
					l.setValue(GO.calendar.lang.no_status);

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
		
		this.event_id = event_id;

		this.participantsPanel.setEventId(event_id);

		this.selectLinkField.container.up('div.x-form-item').setDisplayed(event_id == 0);

		this.linkBrowseButton.setDisabled(event_id < 1);
		if(this.fileBrowseButton)
			this.fileBrowseButton.setId(event_id);
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
			waitMsg : GO.lang.waitMsgSave,
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
				
				GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);

				if (this.link_config && this.link_config.callback) {
					this.link_config.callback.call(this);
				}

				if(action.result.feedback){
					Ext.MessageBox.alert(GO.lang.strError, action.result.feedback);
				}else	if (hide) {
					this.win.hide();
				}

				if (config && config.callback) {
					config.callback.call(this, this, true);
				}
				
				
				this.participantsPanel.store.loadData({results:action.result.participants});
				
				
				GO.calendar.handleMeetingRequest(action.result);

			},
			failure : function(form, action) {
				if (action.failureType == 'client') {
					var error = GO.lang.strErrorsInForm;
				} else {
					var error = action.result.feedback;
				}

				if (error=='Ask permission') {
					Ext.Msg.show({
						title: GO.calendar.lang.ignoreConflictsTitle,
						msg: GO.calendar.lang.ignoreConflictsMsg,
						buttons: Ext.Msg.YESNO,
						fn: this.handlePrompt,
						animEl: 'elId',
						icon: Ext.MessageBox.QUESTION
					});
				} else if (error=='Resource conflict') {
					error = GO.calendar.lang.resourceConflictMsg;
					if (config && config.callback) {
						config.callback.call(this, this, false);
					}
					error = error+'<ul>';
					for (var i in action.result.resources) {
						if (!isNaN(i))
							error = error+'<li> - '+action.result.resources[i]+'</li>';
					}
					error = error+'</ul>';
					Ext.MessageBox.alert(GO.calendar.lang.resourceConflictTitle, error);
				} else {
					if (config && config.callback) {
						config.callback.call(this, this, false);
					}
					Ext.MessageBox.alert(GO.lang.strError, error);
				}
			},
			scope : this
		});
	},

	handlePrompt : function(btn) {
		if (btn=='yes') {
			GO.calendar.eventDialog.submitForm(GO.calendar.eventDialog.hide,{
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
		
		if (this.repeatType.getValue() != "") {
			if (GO.util.empty(this.repeatEndDate.getValue())) {
				this.repeatForeverXCheckbox.setValue(true);
			} else {

				if (this.repeatEndDate.getValue() < eD) {
					this.repeatEndDate.setValue(eD.add(Date.DAY, 1));
				}
			}
		}

		this.participantsPanel.reloadAvailability();
	},

	buildForm : function() {

		this.selectLinkField = new GO.form.SelectLink({});

		this.subjectField = new Ext.form.TextField({
			//name : 'subject',
			name : 'name',
			allowBlank : false,
			fieldLabel : GO.lang.strSubject
		});

		this.locationField = new Ext.form.TextField({
			name : 'location',
			allowBlank : true,
			fieldLabel : GO.lang.strLocation
		});
		this.startDate = new Ext.form.DateField({
			name : 'start_date',
			width : 100,
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
			width:80,
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
			width:80,
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
			width : 100,
			format : GO.settings['date_format'],
			allowBlank : false,			
			listeners : {
				change : {
					fn : this.checkDateInput,
					scope : this
				}
			}
		});

		this.allDayCB = new Ext.ux.form.XCheckbox({
			boxLabel : GO.calendar.lang.allDay,
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
				['NEEDS-ACTION', GO.calendar.lang.statuses["NEEDS-ACTION"]],
				//['ACCEPTED', GO.calendar.lang.accepted],
				['CONFIRMED', GO.calendar.lang.statuses["CONFIRMED"]],
				//['DECLINED', GO.calendar.lang.declined],
				['TENTATIVE',	GO.calendar.lang.statuses["TENTATIVE"]],
				['CANCELLED',	GO.calendar.lang.statuses["CANCELLED"]]
//				['DELEGATED',	GO.calendar.lang.delegated]
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
			boxLabel : GO.calendar.lang.busy,
			name : 'busy',
			checked : true,
			width : 'auto',
			labelSeparator : '',
			hideLabel : true
		});

		this.selectCategory = new GO.form.ComboBoxReset({
			pageSize: parseInt(GO.settings.max_rows_list),
			hiddenName:'category_id',
			fieldLabel:GO.calendar.lang.category,
			value:'',
			valueField:'id',
			displayField:'name',
			store: GO.calendar.globalCategoriesStore,
			mode:'remote',
			triggerAction:'all',
			emptyText:GO.calendar.lang.selectCategory,
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
			boxLabel : GO.calendar.lang.privateEvent,
			name : 'private',
			checked : false,
			width : 'auto',
			labelSeparator : '',
			hideLabel : true
		});
		

		this.propertiesPanel = new Ext.Panel({
			hideMode : 'offsets',
			title : GO.lang.strProperties,
			defaults : {
				anchor : '-20'
			},
			// cls:'go-form-panel',waitMsgTarget:true,
			bodyStyle : 'padding:5px',
			layout : 'form',
			autoScroll : true,
			items : [
			this.subjectField,
			this.locationField,
			this.selectLinkField,
			{	
				xtype : 'compositefield',
				fieldLabel:GO.lang.strStart,
				items : [this.startDate,this.startTime,this.allDayCB
				]
			},{
				fieldLabel:GO.lang.strEnd,
				xtype : 'compositefield',				
				items : [this.endDate, this.endTime
				]
			},{
				xtype : 'compositefield',
				fieldLabel : GO.calendar.lang.status,
				items : [
				this.eventStatus,
				this.busy,
				this.privateCB
				]
			},
			this.selectCalendar = new GO.calendar.SelectCalendar({
				anchor : '-20',
				valueField : 'id',
				displayField : 'name',
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
						if(GO.customfields && record)
							GO.customfields.disableTabs(this.tabPanel, record.data);
						this.selectCategory.setCalendarId(newValue);
						this.selectCategory.reset();
						// Set the permissionlevel so we know if we have the right permissions
						if(record)
							this.setPermissionLevel(record.data.permissionLevel);
						
						this.participantsPanel.reloadOrganizer();
					}
				}
			}),
			this.selectCategory,
//			new GO.form.PlainField({
//				fieldLabel: GO.lang.strOwner,
//				value: GO.settings.name,
//				name:'user_name'
//			}),
			{
				xtype:'textarea',
				fieldLabel:GO.lang.strDescription,
				name : 'description',
				anchor:'-20 -240'
			}]

		});
		// Start of recurrence tab

		this.repeatEvery = new GO.form.NumberField({
			decimals:0,
			name : 'interval',
			minValue:1,
			width : 50,
			value : '1'
		});

		this.repeatType = new Ext.form.ComboBox({
			hiddenName : 'freq',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			width : 200,
			forceSelection : true,
			mode : 'local',
			value : '',
			valueField : 'value',
			displayField : 'text',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [['', GO.lang.noRecurrence],
				['DAILY', GO.lang.strDays],
				['WEEKLY', GO.lang.strWeeks],
				['MONTHLY_DATE', GO.lang.monthsByDate],
				['MONTHLY', GO.lang.monthsByDay],
				['YEARLY', GO.lang.strYears]]
			}),
			hideLabel : true

		});

		this.repeatType.on('select', function(combo, record) {
			this.checkDateInput();
			this.changeRepeat(record.data.value);
		}, this);

		this.monthTime = new Ext.form.ComboBox({
			hiddenName : 'bysetpos',
			triggerAction : 'all',
			selectOnFocus : true,
			disabled : true,
			width : 80,
			forceSelection : true,			
			mode : 'local',
			value : '1',
			valueField : 'value',
			displayField : 'text',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [['1', GO.lang.strFirst],
				['2', GO.lang.strSecond],
				['3', GO.lang.strThird],
				['4', GO.lang.strFourth],
				['-1', GO.calendar.lang.last]
			]
			})
		});

		var days = ['SU','MO','TU','WE','TH','FR','SA'];

		this.cb = [];
		for (var day = 0; day < 7; day++) {
			this.cb[day] = new Ext.form.Checkbox({
				boxLabel : GO.lang.shortDays[day],
				name : days[day],
				disabled : true,
				checked : false,
				width : 'auto',
				hideLabel : true,
				labelSeperator : ''
			});
		}

		this.repeatEndDate = new Ext.form.DateField({
			name : 'until',
			width : 100,
			disabled : true,
			format : GO.settings['date_format'],
			allowBlank : true,			
			listeners : {
				change : {
					fn : this.checkDateInput,
					scope : this
				}
			}
		});

		
		
		
		this.repeatForeverXCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : GO.calendar.lang.repeatForever,
			name : 'repeat_forever',
			checked: true,
			width : 'auto',
			hideLabel : true,
			listeners : {
				check : {
				fn : function(cb, checked){
					
						if(!checked && !this.repeatUntilDateXCheckbox.getValue() && !this.repeatCountXCheckbox.getValue()) {
							this.repeatForeverXCheckbox.setValue(true);
						} else {
							this.repeatUntilDateXCheckbox.setValue(false);
							this.repeatCountXCheckbox.setValue(false);
						}
					},
					scope : this
				}
			}
		});
		
		this.repeatUntilDateXCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : GO.calendar.lang.repeatUntilDate,
			name : 'repeat_UntilDate',
//			checked : true,
//			disabled : true,
			width : 'auto',
			hideLabel : true,
			listeners : {
				check : {
					fn : function(cb, checked){
					if(!checked && !this.repeatForeverXCheckbox.getValue() && !this.repeatCountXCheckbox.getValue()) {
							this.repeatUntilDateXCheckbox.setValue(true);
							return;
						} else {
							this.repeatForeverXCheckbox.setValue(false);
							this.repeatCountXCheckbox.setValue(false);

							this.repeatEndDate.setDisabled(!checked);
						}
					},
					scope : this
				}
			}
		});
		
		this.repeatNumber = new Ext.form.NumberField({
			name: 'count',
			disabled : true,
			maxLength: 1000,
			allowBlank:false,
			value: 1,
			minValue: 1,
			decimals:0
		});
		
		
		this.repeatCountXCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : GO.calendar.lang.repeatCount,
			name : 'repeat_count',
			width : 'auto',
			hideLabel : true,
			listeners : {
				check : {
					fn : function(cb, checked) {
						if(!checked && !this.repeatForeverXCheckbox.getValue() && !this.repeatUntilDateXCheckbox.getValue()) {
							this.repeatCountXCheckbox.setValue(true);
							return;
						} else {
							this.repeatForeverXCheckbox.setValue(false);
							this.repeatUntilDateXCheckbox.setValue(false);

							this.repeatNumber.setDisabled(!checked);
						}
					},
					scope : this
				}
			}
		});
		
		this.recurrencePanel = new Ext.Panel({
			title : GO.calendar.lang.recurrence,
			bodyStyle : 'padding: 5px',
			layout : 'form',
			hideMode : 'offsets',
			defaults:{
				forceLayout:true,
				border:false
			},
			items : [{
				fieldLabel : GO.calendar.lang.repeatEvery,
				xtype : 'compositefield',
				items : [this.repeatEvery,this.repeatType]
			}, {
				xtype : 'compositefield',
				fieldLabel : GO.calendar.lang.atDays,
				items : [this.monthTime,this.cb[1],this.cb[2],this.cb[3],this.cb[4],this.cb[5],this.cb[6],this.cb[0]]
			},{
//				fieldLabel : GO.calendar.lang.rangeRecurrence,
//				xtype : 'compositefield',
//				items : [
//					{
//						fieldLabel : GO.calendar.lang.repeatForever,
						hideLabel: true,
						xtype : 'compositefield',
						items : [this.repeatForeverXCheckbox]
					}, {
						hideLabel: true,
//						fieldLabel : GO.calendar.lang.repeatCount,
						xtype : 'compositefield',
						items : [this.repeatCountXCheckbox, this.repeatNumber,{xtype:'plainfield', value: GO.calendar.lang.times}]
					}, {
						hideLabel: true,
//						fieldLabel : GO.hideLabel: true,calendar.lang.repeatUntilDate,
						xtype : 'compositefield',
						items : [this.repeatUntilDateXCheckbox, this.repeatEndDate]
					}
//				]
//			}
			
			]
		});

		var reminderValues = [['0', GO.calendar.lang.noReminder]];

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
				data : [['60', GO.lang.strMinutes],
				['3600', GO.lang.strHours],
				['86400', GO.lang.strDays],
				['604800', GO.lang.strWeeks]

				]
			}),
			hideLabel : true,
			labelSeperator : ''
		});
		
		this.reminderComposite = new Ext.form.CompositeField({
			style:'margin-top:10px;',
			fieldLabel : GO.calendar.lang.reminder,
			items : [this.reminderValue,this.reminderMultiplier]
		});
		
		this.enableReminderCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : GO.calendar.lang.useReminder,
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
			title : GO.calendar.lang.options,
			bodyStyle : 'padding:5px 0',
			hideMode : 'offsets',
			border:false,
			items:[{
				xtype : 'fieldset',
				autoHeight : true,
				layout : 'form',
				title : GO.calendar.lang.reminder,
				items : [
					this.enableReminderCheckbox,
					this.reminderComposite
			]},this.colorField = new GO.form.ColorField({
				fieldLabel : GO.lang.color,
				value : "EBF1E2",
				name : 'background',
				colors : [
				'EBF1E2',
				'95C5D3',
				'FFFF99',
				'A68340',
				'82BA80',
				'F0AE67',
				'66FF99',
				'CC0099',
				'CC99FF',
				'996600',
				'999900',
				'FF0000',
				'FF6600',
				'FFFF00',
				'FF9966',
				'FF9900',
				'FF6666',
				'CCFFCC',
				/* Line 1 */
				'FB0467',
				'D52A6F',
				'CC3370',
				'C43B72',
				'BB4474',
				'B34D75',
				'AA5577',
				'A25E79',
				/* Line 2 */
				'FF00CC',
				'D52AB3',
				'CC33AD',
				'C43BA8',
				'BB44A3',
				'B34D9E',
				'AA5599',
				'A25E94',
				/* Line 3 */
				'CC00FF',
				'B32AD5',
				'AD33CC',
				'A83BC4',
				'A344BB',
				'9E4DB3',
				'9955AA',
				'945EA2',
				/* Line 4 */
				'6704FB',
				'6E26D9',
				'7033CC',
				'723BC4',
				'7444BB',
				'754DB3',
				'7755AA',
				'795EA2',
				/* Line 5 */
				'0404FB',
				'2626D9',
				'3333CC',
				'3B3BC4',
				'4444BB',
				'4D4DB3',
				'5555AA',
				'5E5EA2',
				/* Line 6 */
				'0066FF',
				'2A6ED5',
				'3370CC',
				'3B72C4',
				'4474BB',
				'4D75B3',
				'5577AA',
				'5E79A2',
				/* Line 7 */
				'00CCFF',
				'2AB2D5',
				'33ADCC',
				'3BA8C4',
				'44A3BB',
				'4D9EB3',
				'5599AA',
				'5E94A2',
				/* Line 8 */
				'00FFCC',
				'2AD5B2',
				'33CCAD',
				'3BC4A8',
				'44BBA3',
				'4DB39E',
				'55AA99',
				'5EA294',
				/* Line 9 */
				'00FF66',
				'2AD56F',
				'33CC70',
				'3BC472',
				'44BB74',
				'4DB375',
				'55AA77',
				'5EA279',
				/* Line 10 */
				'00FF00', '2AD52A',
				'33CC33',
				'3BC43B',
				'44BB44',
				'4DB34D',
				'55AA55',
				'5EA25E',
				/* Line 11 */
				'66FF00', '6ED52A', '70CC33',
				'72C43B',
				'74BB44',
				'75B34D',
				'77AA55',
				'79A25E',
				/* Line 12 */
				'CCFF00', 'B2D52A', 'ADCC33', 'A8C43B',
				'A3BB44',
				'9EB34D',
				'99AA55',
				'94A25E',
				/* Line 13 */
				'FFCC00', 'D5B32A', 'CCAD33', 'C4A83B',
				'BBA344', 'B39E4D',
				'AA9955',
				'A2945E',
				/* Line 14 */
				'FF6600', 'D56F2A', 'CC7033', 'C4723B',
				'BB7444', 'B3754D', 'AA7755',
				'A2795E',
				/* Line 15 */
				'FB0404', 'D52A2A', 'CC3333', 'C43B3B',
				'BB4444', 'B34D4D', 'AA5555', 'A25E5E',
				/* Line 16 */
				'FFFFFF', '949494', '808080', '6B6B6B',
				'545454', '404040', '292929', '000000']
			})]
		});

		this.resourcesPanel = new Ext.Panel({
			id:'resources-panel',
			title:GO.calendar.lang.resources,
			border:true,
			//layout:'accordion',
			forceLayout:true,
			autoScroll:true,
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
					fieldLabel: GO.calendar.lang.status
				});
				resourceOptions.push(pfieldStatus);
				this.formPanel.form.add(pfieldStatus);

					if(GO.customfields)
					{
						var enabled_categories = record.customfields.enabled_categories;
						var disable_categories = record.customfields.disable_categories;
					
						if (GO.customfields.types["GO\\Calendar\\Model\\Calendar"]) {
							for(var l=0; l<GO.customfields.types["GO\\Calendar\\Model\\Calendar"].panels.length; l++)
							{
									var cf = GO.customfields.types["GO\\Calendar\\Model\\Calendar"].panels[l].customfields;
									var formFields = [new GO.form.PlainField({
											hideLabel: true,
											value: '<b>'+GO.customfields.types["GO\\Calendar\\Model\\Calendar"].panels[l].title+'</b>'
										})];
									for(var m=0; m<cf.length; m++)
									{
										if (typeof(resources[j][cf[m].dataname])!='undefined') {
											if (cf[m].datatype=='checkbox' && resources[j][cf[m].dataname]==GO.lang.cmdNo) {
												continue;
											}
											if (cf[m].datatype=='html' && resources[j][cf[m].dataname]=='<br>') {
												continue;
											}
											newFormField = new GO.form.PlainField({
												fieldLabel: cf[m].name,
												value: resources[j][cf[m].dataname]
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
						}
						if (GO.customfields.types["GO\\Calendar\\Model\\Event"]) {
							resourceOptions.push({
								xtype: 'plainfield',
								value: '<br />'
							});
							var panels = GO.customfields.types["GO\\Calendar\\Model\\Event"].panels;
							for(var l=0; l<panels.length; l++)
							{
								var category_id = GO.customfields.types["GO\\Calendar\\Model\\Event"].panels[l].category_id;
								
									
									
								if(!disable_categories || enabled_categories.indexOf(category_id)>-1){									
		
									var cf = panels[l].customfields;
									for(var m=0; m<cf.length; m++)
									{
										
										newFormField = GO.customfields.getFormField(cf[m],{
											name:'resource_options['+resources[j].id+']['+cf[m].dataname+']',
											id:'resource_options['+resources[j].id+']['+cf[m].dataname+']'
										});


										/*
										 * Customfields might return a simple object instead of an Ext.component.
										 * So check if it has events otherwise create the Ext component.
										 */
										if(!newFormField.events){
											newFormField=Ext.ComponentMgr.create(newFormField, 'textfield');
										}

										resourceOptions.push(newFormField);
										this.formPanel.form.add(newFormField);
									}
								}
							}
						}
					}
					else
					{
						resourceOptions.push(new GO.form.PlainField({
							name:'no_fields_'+resources[j].id,
							hideLabel:true,
							value: GO.calendar.lang.no_custom_fields
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
					items:resourceOptions
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

	changeRepeat : function(value) {

		var repeatForever = this.repeatForeverXCheckbox.getValue();
		
		
		
		var form = this.formPanel.form;
		switch (value) {
			default :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(true);
				this.repeatCountXCheckbox.setDisabled(true);
				this.repeatUntilDateXCheckbox.setDisabled(true);
				this.repeatNumber.setDisabled(true);
				this.repeatEndDate.setDisabled(true);
				this.repeatEvery.setDisabled(true);
				break;

			case 'DAILY' :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);

				break;

			case 'WEEKLY' :
				this.disableDays(false);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);

				break;

			case 'MONTHLY_DATE' :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);

				break;

			case 'MONTHLY' :
				this.disableDays(false);
				this.monthTime.setDisabled(false);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);
				break;

			case 'YEARLY' :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);
				break;
		}
	},
	disableDays : function(disabled) {
		var days = ['SU','MO','TU','WE','TH','FR','SA'];
		for (var day = 0; day < 7; day++) {
			this.formPanel.form.findField(days[day])
			.setDisabled(disabled);
		}
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
				this.reloadAvailability();
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
