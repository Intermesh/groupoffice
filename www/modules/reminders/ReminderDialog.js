/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ReminderDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.reminders.ReminderDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	enableOkButton : false,
	
	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'name',
			goDialogId:'reminder',
//			remoteModelIdName: 'reminder_id',
			title:GO.reminders.lang.reminder,
			formControllerUrl: 'reminders/reminder',
			collapsible: true,
			maximizable: true,
			modal: false,
			resizable: true,
			width: 700,
			height: 500,
			layout: 'fit'
		});
		
		GO.reminders.ReminderDialog.superclass.initComponent.call(this);
		
		this.addEvents({
			'save' : true
		});
	},
	
	buildForm : function () {


		this.usersStore = new Ext.data.JsonStore({
			baseParams: {
				reminder_id : 0
			},
			root: 'results',
			id: 'id',
			totalProperty: 'total',
			fields:['id','name'],
			url: GO.url('reminders/reminder/reminderUsers'),
			remoteSort:true
		});

		this.usersGrid = new GO.grid.GridPanel( {
			disabled:true,
			layout:'fit',
			title:GO.lang.users,
			tbar:[{
				iconCls: 'btn-delete',
				text: GO.lang['cmdDelete'],
				cls: 'x-btn-text-icon',
				handler: function(){
					this.usersGrid.deleteSelected();
				},
				scope: this
			},{
				iconCls: 'btn-add',
				text: GO.reminders.lang.addUsers,
				cls: 'x-btn-text-icon',
				handler: function(){

					if(!this.selectUsersWindow){
						this.selectUsersWindow = new GO.dialog.SelectUsers({
							scope:this,
							handler:function(grid){
								var records = grid.getSelectionModel().getSelections();

								var addUsers=[];
								for(var i=0,max=records.length;i<max;i++){
									addUsers.push(records[i].id);
								}
								this.usersStore.baseParams.add_users=Ext.encode(addUsers);
								this.usersStore.load();
								delete this.usersStore.baseParams.add_users;
							}
						});
					}
					this.selectUsersWindow.show();
			
				},
				scope: this
			},{
				iconCls: 'btn-add',
				text: GO.reminders.lang.addUserGroups,
				cls: 'x-btn-text-icon',
				handler: function(){
					if(!this.selectGroupsWindow){
						this.selectGroupsWindow = new GO.dialog.SelectGroups({
							scope:this,
							handler:function(grid){
								var records = grid.getSelectionModel().getSelections();

								var addGroups=[];
								for(var i=0,max=records.length;i<max;i++){
									addGroups.push(records[i].id);
								}
								this.usersStore.baseParams.add_groups=Ext.encode(addGroups);
								this.usersStore.load();
								delete this.usersStore.baseParams.add_groups;
							}
						});
					}
					this.selectGroupsWindow.show();
				},
				scope: this
			}
			],
			paging:true,
			border:true,
			store: this.usersStore,
			columns:[
			{
				header:GO.lang.strName,
				dataIndex: 'name',
				id:'name'
			}],
			autoExpandColumn:'name',
			sm: new Ext.grid.RowSelectionModel(),
			loadMask: true
		});

		this.propertiesPanel = new Ext.Panel({
			layout:'form',
			border: false,
			title:GO.lang.strProperties,
			bodyStyle:'padding:5px',
			items:[
			{
				xtype: 'textfield',
				name: 'name',
				anchor: '100%',
				fieldLabel: GO.lang.strName,
				allowBlank:false
			}
			,this.selectLink = new GO.form.SelectLink({
				anchor:'100%',
				listeners:{
					scope:this,
					select:function(cb,record, index){
						this.formPanel.form.findField('name').setValue(record.data.type_name);
					}
				}
			})
			,{
				xtype : 'compositefield',
				fieldLabel:GO.reminders.lang.time,
				anchor: '100%',
				items : [{
					xtype: 'datefield',
					name: 'date',
					value: new Date()
				},this.timeField = new GO.form.TimeField({
					xtype:'timefield',
					increment: 15,
					format:GO.settings.time_format,
					name:'time',
					width:80,
					hideLabel:true,
					autoSelect :true,
					forceSelection:true
				})]
			}
			,{
				xtype:'combo',
				anchor: '100%',
				fieldLabel: GO.reminders.lang.snoozeTime,
				hiddenName : 'snooze_time',
				store : new Ext.data.ArrayStore({
					idIndex:0,
					fields : ['value', 'text'],
					data : GO.checkerSnoozeTimes
				}),
				value:7200,
				valueField : 'value',
				displayField : 'text',
				mode : 'local',
				triggerAction : 'all',
				editable : false,
				selectOnFocus : true,
				forceSelection : true
			},{
				xtype:'htmleditor',
				name:'text',
				fieldLabel:GO.reminders.lang.text,
				anchor:'100% -105'
			}]
		});

		this.addPanel(this.propertiesPanel);
		this.addPanel(this.usersGrid);

	},
	
	beforeLoad : function(remoteModelId, config) {
		if (remoteModelId>0) {
			this.usersStore.baseParams.reminder_id=remoteModelId;
			this.usersStore.load();
			this.usersGrid.setDisabled(false);
		} else {
			this.usersGrid.setDisabled(true);
			this.usersStore.baseParams.reminder_id=0;
			this.usersStore.removeAll();
		}
	},
	
	afterShowAndLoad : function (remoteModelId, config){
		if (!(remoteModelId>0)) {
			var date = new Date();
			this.timeField.setValue(date.format(GO.settings.time_format));
		}
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.selectLink.setRemoteText(action.result.data.link_name);
	},
		
	afterSubmit : function(action){
		if (action.result.id) {
			this.usersStore.baseParams.reminder_id=action.result.id;
			this.usersStore.load();
			this.usersGrid.setDisabled(false);
			this._tabPanel.setActiveTab(1);
		}
	}

});