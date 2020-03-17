/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LookAndFeelPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
go.usersettings.LookAndFeelPanel = Ext.extend(Ext.Panel, {

	initComponent: function () {
		
		var panels = GO.moduleManager.getAllPanels(), data = [];
		
		panels.forEach(function(p){
			data.push([p.moduleName, p.title]);
		});
		var moduleStore = new Ext.data.ArrayStore({
			fields: ['id', 'name'],
			idField: 'id',
			data: data
		});
		
		
		
		this.globalFieldset = new Ext.form.FieldSet({
			title: t('Global','users','core'),
			labelWidth:dp(160),
			defaults: { 
				anchor: "100%",
			},
			items:[
				this.themeCombo = new Ext.form.ComboBox({
					fieldLabel: t("Theme", "users", "core"),
					name: 'theme',
					store: new GO.data.JsonStore({
						url: GO.url('core/themes'),
						fields:['theme','label'],
						remoteSort: true,
						autoLoad:true
					}),
					visible:GO.settings.config.allow_themes,
					displayField:'label',
					valueField: 'theme',		
					mode:'local',
					triggerAction:'all',
					editable: false,
					selectOnFocus:true,
					forceSelection: true,
					value: GO.settings.config.theme
				}),
				this.startModuleField = new GO.form.ComboBox({
					fieldLabel: t("Start in module", "users", "core"),
					name: 'start_module_name',
					hiddenName: 'start_module',
					store: moduleStore,
					displayField:'name',
					valueField: 'id',
					mode:'local',
					triggerAction:'all',
					editable: false,
					selectOnFocus:true,
					forceSelection: true,
					value: GO.settings.start_module
				}),{
					xtype:'combo',
					fieldLabel: t("Maximum number of rows in lists", "users", "core"),
					store: new Ext.data.SimpleStore({
						fields: ['value'],
						data : [
						['10'],
						['15'],
						['20'],
						['25'],
						['30'],
						['50']
						]
					}),
					displayField:'value',
					valueField: 'value',
					name:'max_rows_list',
					mode:'local',
					triggerAction:'all',
					editable: false,
					selectOnFocus:true,
					forceSelection: true,
					value: 20
				},{
					xtype:'combo',
					fieldLabel: t("Sort names by"),
					store: new Ext.data.SimpleStore({
						fields: ['value', 'text'],
						data : [
						['first_name',t("First name")],
						['last_name',t("Last name")]
						]
					}),
					displayField:'text',
					valueField: 'value',
					hiddenName:'sort_name',
					mode:'local',
					triggerAction:'all',
					editable: false,
					selectOnFocus:true,
					forceSelection: true,
					value: GO.settings.sort_name
				},{
					xtype:'xcheckbox',
					hideLabel: true,
					boxLabel: t("Show smilies"),
					name: 'show_smilies'
				},{
					xtype:'xcheckbox',
					hideLabel: true,
					boxLabel: t("Capital after punctuation"),
					name: 'auto_punctuation'
				}
			]
		});
		
		this.regionFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			defaults: { anchor: "100%" },
			title: t('Regional','users','core'),
			items:[
				this.languageCombo = new Ext.form.ComboBox({
					fieldLabel: t('Language','users','core'),
					name: 'language_id',
					store:  new Ext.data.SimpleStore({
						fields: ['id', 'language'],
						data : GO.Languages
					}),
					displayField:'language',
					valueField: 'id',
					hiddenName:'language',
					mode:'local',
					triggerAction:'all',
					editable: false,
					selectOnFocus:true,
					forceSelection: true
				}),
				
				this.timezoneCombo = new Ext.form.ComboBox({
					fieldLabel: t('Timezone','users','core'),
					name: 'timezone',
					store: new Ext.data.SimpleStore({
						fields: ['timezone'],
						data : go.TimeZones
					}),
					displayField: 'timezone',
					mode: 'local',
					triggerAction: 'all',
					selectOnFocus: true,
					forceSelection: true,
					value: GO.settings.timezone
				}),
				
				this.dateFormatCombo = new Ext.form.ComboBox({
					fieldLabel: t('Date format','users','core'),
					name: 'dateFormat',
					store: go.util.Format.dateFormats,
					displayField: 'label',
					valueField: 'format',
					hiddenName: 'dateFormat',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					forceSelection: true
				}),
				
				this.timeFormatCombo = new Ext.form.ComboBox({
					fieldLabel: t('Time format','users','core'),
					name: 'time_format_name',
					store: go.util.Format.timeFormats,
					displayField: 'label',
					valueField: 'format',
					hiddenName: 'timeFormat',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					forceSelection: true
				}),
				{
					xtype: "xcheckbox",
					name: "shortDateInList",
					checked: true,
					hideLabel: true,
					boxLabel: t("Use short format for date and time in lists",'users','core'),
					anchor: "100%"
				},
					
				this.firstWeekdayCombo = new Ext.form.ComboBox({
					fieldLabel: t('First day of week','users','core'),
					name: 'first_weekday_name',
					store: new Ext.data.SimpleStore({
						fields: ['id', 'first_weekday'],		
						data : [
							['0', t('Sunday','users','core')],
							['1', t('Monday','users','core')]
						]
					}),
					displayField: 'first_weekday',
					valueField: 'id',
					hiddenName: 'firstWeekday',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					forceSelection: true,
					value: GO.settings.first_weekday
				}),
				
				this.holidaysetCombo = new GO.form.ComboBox({
					fieldLabel: t('Holidays','users','core'),
					name: 'holidayset',
					store:  new Ext.data.JsonStore({
						data: GO.lang.holidaySets,
						fields: ['iso', 'label'],
						remoteSort: true
					}),
					displayField:'label',
					valueField: 'iso',
					hiddenName:'holidayset',
					mode:'local',
					triggerAction:'all',
					editable: false,
					selectOnFocus:true,
					forceSelection: true
				})
			]
		});
		
				
		this.formattingFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			defaults: { width:dp(50) },
			title: t('Formatting','users','core'),
			items:[
				{
						xtype: 'textfield', 
						fieldLabel: t("List separator", "users", "core"), 
						name: 'listSeparator'
					},{
						xtype: 'textfield', 
						fieldLabel: t("Text separator", "users", "core"), 
						name: 'textSeparator'
					},{
						xtype: 'textfield', 
						fieldLabel: t("Thousand Seperator", "users", "core"), 
						name: 'thousandsSeparator'
					},
					{
						xtype: 'textfield', 
						fieldLabel: t("Decimal Seperator", "users", "core"), 
						name: 'decimalSeparator'
					},
					{
						xtype: 'textfield', 
						fieldLabel: t("Currency", "users", "core"), 
						name: 'currency'
					}
			]
		});
		
		this.soundsFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			title: t('Sounds','users','core'),
			items:[
				
				this.cbMuteAll = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Mute all sounds", "users", "core"),
					name: 'mute_sound',
					listeners:{
						check: function(cb, val){
							if(val)	{
								this.cbMuteNewMailSound.disable();
								this.cbMuteReminderSound.disable();
							}	else {
								this.cbMuteNewMailSound.enable();
								this.cbMuteReminderSound.enable();
							}
						},scope:this
					}
				}),
				
				this.cbMuteReminderSound = new Ext.ux.form.XCheckbox({
					hideLabel:true,
					boxLabel: t("Mute reminder sounds", "users", "core"),
					name: 'mute_reminder_sound'
				}),

				this.cbMuteNewMailSound = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Mute new mail sounds", "users", "core"),
					name: 'mute_new_mail_sound'
				})
			]
		});
		
		this.notificationsFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			title: t('Notifications','users','core'),
			items:[
				this.cbPopupReminders = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Show a popup window when a reminder becomes active", "users", "core"),
					name: 'popup_reminders',
					listeners: {
						check: function(cb,checked) {
							if(checked) {
								var options = {
									body: t("Desktop notifications active"),
									icon: 'views/Extjs3/themes/Group-Office/images/groupoffice.ico'
								}
								// Let's check if the browser supports notifications

								if (!("Notification" in window)) {
									// Browser does not support desktop notification and will show a popup instead
								} else if (Notification.permission !== 'granted' && (Notification.permission !== 'denied' || Notification.permission === "default")) {
									Notification.requestPermission(function (permission) {
									// If the user accepts, let's create a notification
									try {
										if (permission === "granted") {
											var notification = new Notification(t("Desktop notifications active"), options);
											return true;
										}
									} catch(e) {
										// ignore failure on android
									}
									cb.setValue(false);

									});
								}
							} 
						}
					}
				}),
				this.cbPopupEmailNotifications = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Show a popup window when an e-mail arrives", "users", "core"),
					name: 'popup_emails',
					listeners: {
						check: function (cb, checked) {
							if (checked) {
								var options = {
									body: t("Desktop notifications active"),
									icon: 'views/Extjs3/themes/Group-Office/images/groupoffice.ico'
								}
								// Let's check if the browser supports notifications
								if (!("Notification" in window)) {
									// Browser does not support desktop notification and will show a popup instead
								} else if (Notification.permission !== 'granted' && (Notification.permission !== 'denied' || Notification.permission === "default")) {
									Notification.requestPermission(function (permission) {
										// If the user accepts, let's create a notification
										if (permission === "granted") {
											var notification = new Notification(t("Desktop notifications active"), options);
										} else {
											cb.setValue(false);
										}
									});
								}
							}
						}
					}
				}),
				this.cbEmailReminders = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Mail reminders", "users", "core"),
					name: 'mail_reminders'
				})
				
			]
		});
	
		Ext.apply(this,{
			title:t('Look & feel','users','core'),
			autoScroll:true,
			iconCls: 'ic-style',
			layout:'column',
			items: [
				{
					columnWidth: .5,//left
					mobile: {
						columnWidth: 1
					},
					items:[this.globalFieldset,this.regionFieldset
						
						,{
						xtype:'button',
						style:'margin-left:14px',
						handler:this.resetState,
						scope:this,
						text:t('Reset windows and grids'),
						anchor:''
					}
				]
				},{
					columnWidth: .5,//right
					mobile: {
						columnWidth: 1
					},
					items: [this.formattingFieldset,this.soundsFieldset,this.notificationsFieldset]
				}
			]
		});
		
		go.usersettings.LookAndFeelPanel.superclass.initComponent.call(this);
	},
	
	// OLD FRAMEWORK CODE, refactor when clientSettings: {} property is available for User
	resetState : function(){
		if(confirm(t('Are you sure you want to reset all grid columns, windows, panel sizes etc. to the factory defaults?'))){
			GO.request({
				maskEl:Ext.getBody(),
				url:'maintenance/resetState',
				params:{
					user_id:this.ownerCt.ownerCt.ownerCt.currentUserId
				},
				success:function(){
					document.location.reload();
				},
				scope:this
			});
		}
	},
	
	onLoadComplete : function(data){

	},
	
	onSubmitStart : function(values){

	},
	
	onSubmitComplete : function(result){

	}
});


