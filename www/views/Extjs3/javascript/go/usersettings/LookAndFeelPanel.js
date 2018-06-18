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
		this.globalFieldset = new Ext.form.FieldSet({
			title: t('Global'),
			labelWidth:dp(160),
			defaults: { 
				width:dp(200),
			},
			items:[
				this.themeCombo = new Ext.form.ComboBox({
					fieldLabel: t("Theme", "users"),
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
					fieldLabel: t("Start in module", "users"),
					name: 'start_module_name',
					hiddenName: 'start_module',
					store: new GO.data.JsonStore({
						url: GO.url('core/modules'),
						baseParams: {user_id:0},
						fields:['id','name'],
						remoteSort: true
					}),
					displayField:'name',
					valueField: 'id',
					mode:'remote',
					triggerAction:'all',
					editable: false,
					selectOnFocus:true,
					forceSelection: true,
					value: GO.settings.start_module
				}),{
					xtype:'combo',
					fieldLabel: t("Maximum number of rows in lists", "users"),
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
					fieldLabel: t("Sort names by", "users"),
					store: new Ext.data.SimpleStore({
						fields: ['value', 'text'],
						data : [
						['first_name',t("First name", "users")],
						['last_name',t("Last name", "users")]
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
					boxLabel: t("Show smilies", "users"),
					name: 'show_smilies'
				},{
					xtype:'xcheckbox',
					hideLabel: true,
					boxLabel: t("Capital after punctuation", "users"),
					name: 'auto_punctuation'
				}
			]
		});
		
		this.regionFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			defaults: { width:dp(200) },
			title: t('Regional'),
			items:[
				this.languageCombo = new Ext.form.ComboBox({
					fieldLabel: t('Language'),
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
					fieldLabel: t('Timezone'),
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
					fieldLabel: t('Date format'),
					name: 'dateFormat',
					store: new Ext.data.SimpleStore({
						fields: ['id', 'dateformat'],
						data : [
						['d-m-Y', t("Day-Month-Year", "users")],
						['m/d/Y', t("Month/Day/Year", "users")],
						['d/m/Y', t("Day/Month/Year", "users")],
						['d.m.Y', t("Day.Month.Year", "users")],
						['Y-m-d', t("Year-Month-Day", "users")],
						['Y.m.d', t("Year.Month.Day", "users")]
						]
					}),
					displayField: 'dateformat',
					valueField: 'id',
					hiddenName: 'dateFormat',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					forceSelection: true
				}),
				
				this.timeFormatCombo = new Ext.form.ComboBox({
					fieldLabel: t('Time format'),
					name: 'time_format_name',
					store: new Ext.data.SimpleStore({
						fields: ['id', 'time_format'],		
						data : [
						['G:i', t('24 hour format')],
						['h:i a', t('12 hour format')]
						]
					}),
					displayField: 'time_format',
					valueField: 'id',
					hiddenName: 'timeFormat',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					forceSelection: true
				}),
					
				this.firstWeekdayCombo = new Ext.form.ComboBox({
					fieldLabel: t('First day of week'),
					name: 'first_weekday_name',
					store: new Ext.data.SimpleStore({
						fields: ['id', 'first_weekday'],		
						data : [
							['0', t('Sunday')],
							['1', t('Monday')]
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
					fieldLabel: t('Holidays'),
					name: 'holidayset',
					store:  new GO.data.JsonStore({
						url: GO.url("core/holidays"),
						fields: ['filename', 'label'],
						remoteSort: true
					}),
					displayField:'label',
					valueField: 'filename',
					hiddenName:'holidayset',
					mode:'remote',
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
			title: t('Formatting'),
			items:[
				{
						xtype: 'textfield', 
						fieldLabel: t("List separator", "users"), 
						name: 'listSeparator'
					},{
						xtype: 'textfield', 
						fieldLabel: t("Text separator", "users"), 
						name: 'textSeparator'
					},{
						xtype: 'textfield', 
						fieldLabel: t("Thousand Seperator", "users"), 
						name: 'thousandsSeparator'
					},
					{
						xtype: 'textfield', 
						fieldLabel: t("Decimal Seperator", "users"), 
						name: 'decimalSeparator'
					},
					{
						xtype: 'textfield', 
						fieldLabel: t("Currency", "users"), 
						name: 'currency'
					}
			]
		});
		
		this.soundsFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			title: t('Sounds'),
			items:[
				
				this.cbMuteAll = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Mute all sounds", "users"),
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
					boxLabel: t("Mute reminder sounds", "users"),
					name: 'mute_reminder_sound'
				}),

				this.cbMuteNewMailSound = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Mute new mail sounds", "users"),
					name: 'mute_new_mail_sound'
				})
			]
		});
		
		this.notificationsFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			title: t('Notifications'),
			items:[
				this.cbPopupReminders = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Show a popup window when a reminder becomes active", "users"),
					name: 'popup_reminders',
					listeners: {
						check: function(cb,checked) {
							if(checked) {
								var options = {
									body: t("Desktop notifications active", "users"),
									icon: 'views/Extjs3/themes/Group-Office/images/groupoffice.ico'
								}
								// Let's check if the browser supports notifications
								if (!("Notification" in window)) {
									// Browser does not support desktop notification and will show a popup instead
								} else if (Notification.permission !== 'granted' && (Notification.permission !== 'denied' || Notification.permission === "default")) {
									Notification.requestPermission(function (permission) {
									// If the user accepts, let's create a notification
									if (permission === "granted") {
										 var notification = new Notification(t("Desktop notifications active", "users"),options);
									} else {
										cb.setValue(false);
									}
									});
								}
							} 
						}
					}
				}),
				this.cbPopupEmailNotifications = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Show a popup window when an e-mail arrives", "users"),
					name: 'popup_emails',
					listeners: {
						check: function (cb, checked) {
							if (checked) {
								var options = {
									body: t("Desktop notifications active", "users"),
									icon: 'views/Extjs3/themes/Group-Office/images/groupoffice.ico'
								}
								// Let's check if the browser supports notifications
								if (!("Notification" in window)) {
									// Browser does not support desktop notification and will show a popup instead
								} else if (Notification.permission !== 'granted' && (Notification.permission !== 'denied' || Notification.permission === "default")) {
									Notification.requestPermission(function (permission) {
										// If the user accepts, let's create a notification
										if (permission === "granted") {
											var notification = new Notification(t("Desktop notifications active", "users"), options);
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
					boxLabel: t("Mail reminders", "users"),
					name: 'mail_reminders'
				})
				
			]
		});
	
		Ext.apply(this,{
			title:t('Look & feel'),
			autoScroll:true,
			iconCls: 'ic-style',
			layout:'column',
			items: [
				{
					columnWidth: .5,//left
					items:[this.globalFieldset,this.regionFieldset]
				},{
					columnWidth: .5,//right
					items: [this.formattingFieldset,this.soundsFieldset,this.notificationsFieldset]
				}
			]
		});
		
		go.usersettings.LookAndFeelPanel.superclass.initComponent.call(this);
	},
	
	onLoadComplete : function(data){

	},
	
	onSubmitStart : function(values){

	},
	
	onSubmitComplete : function(result){

	}
});


