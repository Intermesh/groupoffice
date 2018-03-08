/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CalendarDialog.js 21247 2017-06-26 09:28:06Z devdevilnl $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.CalendarDialog = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	this.propertiesTab = new Ext.Panel({	
		title:GO.lang['strProperties'],
		layout:'form',
		anchor: '100% 100%',		
		cls:'go-form-panel',
		labelWidth: 120,
		items: [
		this.selectUser = new GO.form.SelectUser({
			fieldLabel: GO.lang.strUser,
			disabled : !GO.settings.has_admin_permission,
			value: GO.settings.user_id,
			anchor: '100%'
		}),
		this.name = new Ext.form.TextField({
			fieldLabel: GO.lang.strName,
			name: 'name',
			allowBlank:false,
			anchor: '100%'
		}),
		this.selectGroup = new GO.form.ComboBox({
			hiddenName:'group_id',
			fieldLabel:GO.calendar.lang.group,
			valueField:'id',
			value:1,
			displayField:'name',
			id:'resource_groups',
			emptyText: GO.lang.strPleaseSelect,
			store: new GO.data.JsonStore({
				url: GO.url("calendar/group/store"),
				fields:['id','name','user_name','fields','acl_id'],
				baseParams: {'limit': 0}
			}),
			
			mode:'local',
			triggerAction:'all',
			editable:false,
			selectOnFocus:true,
			allowBlank:true,
			forceSelection:true,
			anchor:'100%'
		}),{
			xtype:'xcheckbox',
			name:'show_bdays',
			boxLabel:GO.calendar.lang.show_bdays,
			hideLabel:true
		},{
			xtype:'xcheckbox',
			name:'show_holidays',
			boxLabel:GO.calendar.lang.show_holidays,
			hideLabel:true
		},{
			xtype:'xcheckbox',
			name:'show_completed_tasks',
			boxLabel:GO.calendar.lang.show_completed_tasks,
			hideLabel:true,
			hidden: !GO.tasks
		},{
			xtype:'textarea',
			fieldLabel:GO.lang.strComment,
			name:'comment',
			anchor:'100%',
			height:50
		},{
			xtype:'textarea',
			fieldLabel:GO.calendar.lang['tooltip'],
			name:'tooltip',
			anchor:'100%',
			height:50,
			maxLength: 127
		}
		]
	});

	if(GO.tasks)
	{
		this.tasklistsTab = new GO.base.model.multiselect.panel({
      title:GO.tasks.lang.visibleTasklists,
      url:'calendar/calendarTasklist',
      columns:[{header: GO.lang.strTitle, dataIndex: 'name'}],
      fields:['id','name'],
      model_id:0
    });
		
		this.selectTasklist = new GO.form.ComboBoxReset({
			fieldLabel:'CalDAV '+GO.tasks.lang.tasklist,
				store:new GO.data.JsonStore({
				url: GO.url('tasks/tasklist/store'),
				baseParams: {'permissionLevel': GO.permissionLevels.write},
				fields:['id','name','user_name'],
				remoteSort:true
			}),
			displayField: 'name',
			valueField: 'id',
			triggerAction:'all',
			hiddenName:'tasklist_id',
			mode:'remote',
			editable: true,
			selectOnFocus:true,
			forceSelection: true,
			typeAhead: true,
			emptyText:GO.lang.none,
			pageSize: parseInt(GO.settings.max_rows_list)
		});

		this.propertiesTab.add(this.selectTasklist);
	}

	this.propertiesTab.add([{
			xtype:'plainfield',
			fieldLabel:GO.calendar.lang.directUrl,
			name:'url',
			anchor:'100%'
		},{
			xtype:'xcheckbox',
			hideLabel:true,
			boxLabel:GO.calendar.lang.publishICS,
			hidden: GO.calendar.disablePublishing,
			name:'public'
		},{
			xtype:'plainfield',
			hidden: GO.calendar.disablePublishing,
			fieldLabel:'iCalendar URL',
			name:'ics_url',
			anchor:'100%'
		},
		this.exportButton = new Ext.Button({
			text:GO.lang.cmdExport,
			disabled:true,
			handler:function(){
				document.location=GO.url("calendar/calendar/exportIcs", {"calendar_id":this.calendar_id});
			},
			scope:this
		})
		,this.deleteAllItemsButton = new Ext.Button({
				style:'margin-top:10px',
				xtype:'button',
				text:GO.lang.deleteAllItems,
				handler:function(){
					Ext.Msg.show({
						title: GO.lang.deleteAllItems,
						icon: Ext.MessageBox.WARNING,
						msg: GO.lang.deleteAllItemsAreYouSure,
						buttons: Ext.Msg.YESNO,
						scope:this,
						fn: function(btn) {
							if (btn=='yes') {
								GO.request({
									timeout:300000,
									maskEl:Ext.getBody(),
									url:'calendar/calendar/truncate',
									params:{
										calendar_id:this.calendar_id
									},
									scope:this
								});
							}
						}
					});
				},
				scope:this
			}),
			this.removeDuplicatesButton =new Ext.Button({
				style:'margin-top:10px',
				xtype:'button',
				text:GO.lang.removeDuplicates,
				handler:function(){
					
					window.open(GO.url('calendar/calendar/removeDuplicates',{calendar_id:this.calendar_id}))
					
				},
				scope:this
			})
		])

	this.readPermissionsTab = new GO.grid.PermissionsPanel({	
	});
	
	this.uploadFile = new GO.form.UploadFile({
		inputName : 'ical_file',	   
		max:1 			
	});
	
	this.uploadFile.on('filesChanged', function(input, inputs){
		this.importButton.setDisabled(inputs.getCount()==1);
	}, this);
	
	
	this.categoriesGrid = new GO.calendar.CategoriesGrid({
		title:GO.calendar.lang.category,
		store: GO.calendar.categoriesStore
	});
	
	this.importTab = new Ext.Panel({		
		layout:'form',
		waitMsgTarget:true,
		disabled:true,
		title:GO.lang.cmdImport,
		items: [{
			xtype: 'panel',
			html: GO.calendar.lang.selectIcalendarFile,
			border:false
		},
		this.uploadFile,
		this.importButton = new Ext.Button({
			xtype:'button',
			disabled:true,
			text:GO.lang.cmdImport,
			handler: function(){
				this.formPanel.form.submit({
					waitMsg:GO.lang.waitMsgUpload,
					url: GO.url('calendar/calendar/importIcs'),
					params: {
//						task: 'import',
						calendar_id:this.calendar_id
					},
					success: function(form,action)
					{
						this.uploadFile.clearQueue();

						Ext.Msg.show({
							title: GO.lang.strSuccess,
							width : 600,
							height : 220,
							icon: Ext.MessageBox.INFO,
							msg: "<pre>"+action.result.feedback+"</pre>"
						});
						this.fireEvent('calendarimport', this);
						
					},
					failure: function(form, action) {
						GO.errorDialog.show(action.result.feedback);
					},
					scope: this
				});
			},
			scope: this
		})],
		cls: 'go-form-panel'
	});


	var items = [this.propertiesTab];
	
	if(GO.tasks)
	{
		items.push(this.tasklistsTab);
	}
	
	items.push(this.categoriesGrid);
	items.push(this.readPermissionsTab);
	items.push(this.importTab);

	if(GO.customfields && GO.customfields.types["GO\\Calendar\\Model\\Calendar"])
	{
		for(var i=0;i<GO.customfields.types["GO\\Calendar\\Model\\Calendar"].panels.length;i++)
		{
			var panel = GO.customfields.types["GO\\Calendar\\Model\\Calendar"].panels[i];
			panel.autoScroll = true;
			items.push(panel);
		}
	}

	this.tabPanel = new Ext.TabPanel({
		hideLabel:true,
		deferredRender:false,
		xtype:'tabpanel',
		activeTab: 0,
		border:false,
		anchor: '100% 100%',
		enableTabScroll: true,
		items:items
	});

	this.formPanel = new Ext.FormPanel({
		fileUpload:true,
		url: GO.url("calendar/calendar/load"),
		defaultType: 'textfield',
		waitMsgTarget:true,
		items:this.tabPanel
	});

	
	GO.calendar.CalendarDialog.superclass.constructor.call(this,{
		title: GO.calendar.lang.calendar,
		layout:'fit',
		modal:false,
		height:600,
		width:700,
		closeAction:'hide',
		items: this.formPanel,
		buttons:[
		{
			text:GO.lang.cmdOk,
			handler: function(){
				this.save(true)
			},
			scope: this
		},
		{
			text:GO.lang.cmdApply,
			handler: function(){
				this.save(false)
			},
			scope: this
		},

		{
			text:GO.lang.cmdClose,
			handler: function(){
				this.hide()
			},
			scope: this
		}
		]
	});

	this.addEvents({calendarimport:true});
}

Ext.extend(GO.calendar.CalendarDialog, GO.Window, {

	resource: 0,
    
	initComponent : function(){
		
		this.addEvents({
			'save' : true
		});
		
		GO.calendar.CalendarDialog.superclass.initComponent.call(this);	
		
	},				
	show : function (calendar_id, resource){		
		if(!this.rendered) {
			this.render(Ext.getBody());
		} else {
			this.selectGroup.store.reload()
		}
		if(GO.tasks)
		{
			this.tasklistsTab.setModelId(calendar_id);
		}
			
		this.propertiesTab.show();       

		if(resource && !this.selectGroup.store.loaded)
		{
			this.selectGroup.store.load({
				callback:function(){
					this.show(calendar_id, resource);
				},
				scope:this
			});
			return;
		}

		this.resource = (resource > 0) ? resource : 0;

		var title = (this.resource) ? GO.calendar.lang.resource : GO.calendar.lang.calendar;
		this.setTitle(title);

		this.removeDuplicatesButton.setDisabled(!calendar_id);
		this.deleteAllItemsButton.setDisabled(!calendar_id);

		if(calendar_id > 0)
		{
			if(calendar_id!=this.calendar_id)
			{
				this.loadCalendar(calendar_id);
			}else
			{
				GO.calendar.CalendarDialog.superclass.show.call(this);
			}                                   
		}else
		{
			this.calendar_id=0;
			this.formPanel.form.reset();
			
			
			

			if(resource){
				this.selectGroup.selectFirst();
			}else
			{
				this.selectGroup.setValue(1);
			}
            
			this.exportButton.setDisabled(true);
			this.importTab.setDisabled(true);	

			this.readPermissionsTab.setDisabled(true);

			this.showGroups(resource);
			
			this.categoriesGrid.setCalendarId(0);
			
			GO.calendar.CalendarDialog.superclass.show.call(this);
		}
	},
	hide : function() {
		this.uploadFile.clearQueue();
		
		GO.calendar.CalendarDialog.superclass.hide.call(this);
	},
	
	loadCalendar : function(calendar_id)
	{
		if(GO.tasks)
		{
			this.tasklistsTab.setModelId(calendar_id);
//			this.tasklistsTab.store.loaded = false;
//			this.tasklistsTab.store.baseParams.calendar_id = calendar_id;
		}
		
		this.categoriesGrid.setCalendarId(calendar_id);
		
		this.formPanel.form.load({
			url: GO.url("calendar/calendar/load"),
			params: {
				id:calendar_id				
			},
			waitMsg:GO.lang.waitMsgLoad,
			success: function(form, action) {
				this.calendar_id=calendar_id;
				this.selectUser.setRawValue(action.result.remoteComboTexts.user_id);
				this.readPermissionsTab.setAcl(action.result.data.acl_id);
				this.exportButton.setDisabled(false);
				this.importTab.setDisabled(false);

				if(GO.tasks && action.result.remoteComboTexts.tasklist_id)
					this.selectTasklist.setRemoteText(action.result.remoteComboTexts.tasklist_id);

				this.showGroups(action.result.data.group_id > 1);

				GO.calendar.CalendarDialog.superclass.show.call(this);
			},
			failure:function(form, action)
			{
				Ext.Msg.alert(GO.lang.strError, action.result.feedback)
			},
			scope: this
		});
	},
	save : function(hide)
	{        
		if(this.resource && this.name.getValue() && !this.selectGroup.getValue())
		{
			Ext.MessageBox.alert(GO.lang.strError, GO.calendar.lang.no_group_selected);
		}else
		{
			var tasklists = (GO.tasks && !this.resource) ? Ext.encode(this.tasklistsTab.getGridData()) : '';
		
			this.formPanel.form.submit({
				url:GO.url("calendar/calendar/submit"),
				params: {					
					'id': this.calendar_id,
					'tasklists':tasklists
				},
				waitMsg:GO.lang.waitMsgSave,
				success:function(form, action){

					if(action.result.id)
					{
						this.calendar_id=action.result.id;
						this.readPermissionsTab.setAcl(action.result.acl_id);
						this.exportButton.setDisabled(false);
						this.importTab.setDisabled(false);
					//this.loadAccount(this.calendar_id);
					}

					if(GO.tasks)
					{
						this.tasklistsTab.setModelId(action.result.id);
						this.tasklistsTab.store.commitChanges();
					}

					this.fireEvent('save', this, this.selectGroup.getValue());

					if(hide)
					{
						this.hide();
					}
				},

				failure: function(form, action) {
					var error = '';
					if(action.failureType=='client')
					{
						error = GO.lang.strErrorsInForm;
					}else
					{
						error = action.result.feedback;
					}

					Ext.MessageBox.alert(GO.lang.strError, error);
				},
				scope:this

			});
		}
			
	},
	showGroups : function(resource)
	{
		var f = this.formPanel.form.findField('resource_groups');
		f.container.up('div.x-form-item').setDisplayed(resource);

		f = this.formPanel.form.findField('show_bdays');
		f.container.up('div.x-form-item').setDisplayed(!resource);

		if(GO.tasks)
		{
			if(resource)
			{
				this.tabPanel.hideTabStripItem('calendar_visible_tasklists');
			}else
			{
				this.tabPanel.unhideTabStripItem('calendar_visible_tasklists');
			}
		}
	}
});
