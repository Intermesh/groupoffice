/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ReminderDialog.js 22112 2018-01-12 07:59:41Z mschering $
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
			title:t("Popup reminder", "reminders"),
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
			title:t("Users"),
			tbar:[{
				iconCls: 'btn-delete',
				text: t("Delete"),
				cls: 'x-btn-text-icon',
				handler: function(){
					this.usersGrid.deleteSelected();
				},
				scope: this
			},{
				iconCls: 'btn-add',
				text: t("Add users", "reminders"),
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
				text: t("Add user groups", "reminders"),
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
				header:t("Name"),
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
			title:t("Properties"),
			bodyStyle:'padding:5px',
			items:[
			{
				xtype: 'textfield',
				name: 'name',
				anchor: '100%',
				fieldLabel: t("Name"),
				allowBlank:false
			}
			,{
				xtype : 'compositefield',
				fieldLabel:t("Time", "reminders"),
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
				fieldLabel: t("Snooze time", "reminders"),
				hiddenName : 'snooze_time',
				store : new Ext.data.ArrayStore({
					idIndex:0,
					fields : ['value', 'text'],
					data : [
						[300,'5 '+t("Minutes")],
						[600, '10 '+t("Minutes")],
						[1200, '20 '+t("Minutes")],
						[1800, '30 '+t("Minutes")],
						[3600, '1 '+t("Hour")],
						[7200, '2 '+t("Hours")],
						[10800, '3 '+t("Hours")],
						[14400, '4 '+t("Hours")],
						[86400, '1 '+t("Day")],
						[2*86400, '2 '+t("Days")],
						[3*86400, '3 '+t("Days")],
						[4*86400, '4 '+t("Days")],
						[5*86400, '5 '+t("Days")],
						[6*86400, '6 '+t("Days")],
						[7*86400, '7 '+t("Days")]
					]
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
				xtype:'xhtmleditor',
				name:'text',
				fieldLabel:t("Text", "reminders"),
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
	
		
	afterSubmit : function(action){
		if (action.result.id) {
			this.usersStore.baseParams.reminder_id=action.result.id;
			this.usersStore.load();
			this.usersGrid.setDisabled(false);
			this._tabPanel.setActiveTab(1);
		}
	}

});
