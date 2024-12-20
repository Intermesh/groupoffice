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
		const panels = GO.moduleManager.getAllPanelConfigs();
		let data = [];

		panels.forEach(function(p){
			data.push([p.moduleName, p.title]);
		});
		const moduleStore = new Ext.data.ArrayStore({
			fields: ['id', 'name'],
			idField: 'id',
			data: data
		});

		this.themeFieldset = new Ext.form.FieldSet({
			title: t('Theme', 'users', 'core'),
			items: [
				{xtype:'radiogroup',
				name:'theme',
				value: GO.settings.config.theme,
				items: [
					{inputValue: 'Paper', boxLabel: t('Paper')},
					{inputValue: 'Compact', boxLabel: t('Compact')}
				]},
				{xtype:'hidden', name: 'themeColorScheme', listeners: {
					'setvalue': me => {
						me.nextSibling().items.each(btn => {
							btn.toggle(btn.inputValue === me.value)
						});
						if(this.userId !== go.User.id) return;
						['light','dark','system'].forEach(name => {
							document.body.classList.remove(name);
						});
						document.body.classList.add(me.value);
						if(me.value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
							document.body.classList.add('dark');
						}

						if(document.body.classList.contains("dark")) {
							document.getElementsByTagName("meta")["theme-color"].content = "#202020";
						}
					}
					}},
				{xtype:'container', cls: 'go-theme-color', defaults: {
					enableToggle:true,
					handler: function(me,ev) {
						const hiddenField = this.ownerCt.previousSibling();
						hiddenField.setValue(me.inputValue);
					}
				},
				items: [
					{xtype:'button', inputValue: 'light', cls:'mode-light', tooltip:t('Light')},
					{xtype:'button', inputValue: 'dark', cls:'mode-dark', tooltip:t('Dark')},
					{xtype:'button', inputValue: 'system', cls:'mode-system', tooltip:t('System default')}
				]},
				{xtype:'container',layout: 'hbox',defaults: {style:'width:33%;text-align:center;'},items:[
						{html: t('Light')},
						{html: t('Dark')},
						{html: t('System')}
					]}
			]
		});
		
		
		this.globalFieldset = new Ext.form.FieldSet({
			title: t('Global','users','core'),
			labelWidth:dp(160),
			defaults: { 
				anchor: "100%"
			},
			items:[
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
					boxLabel: t("Use shortcut to send forms") + " (" + (Ext.isMac ? "⌘" : "Ctrl") + " + Enter)",
					name: 'enableSendShortcut'
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
				},{
					xtype: 'xcheckbox',
					name: 'confirmOnMove',
					hideLabel: true,
					boxLabel: t('Show confirmation dialog on move'),
					hint: t("When this is on and items are moved by dragging, confirmation is requested")
				}
			]
		});

		// if(GO.settings.config.allow_themes) {
		// 	this.globalFieldset.insert(0, this.themeCombo = new Ext.form.ComboBox({
		// 		fieldLabel: t("Theme", "users", "core"),
		// 		name: 'theme',
		// 		store: new GO.data.JsonStore({
		// 			url: GO.url('core/themes'),
		// 			fields:['theme','label'],
		// 			remoteSort: true,
		// 			autoLoad:true
		// 		}),
		// 		visible:GO.settings.config.allow_themes,
		// 		displayField:'label',
		// 		valueField: 'theme',
		// 		mode:'local',
		// 		triggerAction:'all',
		// 		editable: false,
		// 		selectOnFocus:true,
		// 		forceSelection: true,
		// 		value: GO.settings.config.theme
		// 	}));
		// }


		this.regionFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			defaults: { anchor: "100%" },
			title: t('Regional','users','core'),
			items:[
				this.languageCombo = new go.form.LanguageCombo({
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
			// labelWidth:dp(160),
			defaults: { width:dp(180) },
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
						fieldLabel: t("Thousand Separator", "users", "core"),
						name: 'thousandsSeparator'
					},
					{
						xtype: 'textfield', 
						fieldLabel: t("Decimal Separator", "users", "core"),
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

		if(GO.settings.config.allow_themes) {
			this.items.items[0].insert(0, this.themeFieldset);
		}
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
	onLoadStart : function(userId){
		this.userId = userId;
	},
	onLoadComplete : function(user){

	},
	
	onSubmitStart : function(values){

	},
	
	onSubmitComplete : function(result){

	}
});


