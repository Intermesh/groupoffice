/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: RegionalSettingsPanel.js 14977 2013-06-03 14:22:02Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.RegionalSettingsPanel = function(config)
{
	if(!config)
	{
		config={};
	}
	
	this.autoScroll=true;
	
	

	

//	/*  dateformat */
//	var dateFormatData = new Ext.data.SimpleStore({
//		fields: ['id', 'date_format'],		
//		data : [
//		['dmY', GO.users.lang.dmy],
//		['mdY', GO.users.lang.mdy],
//		['Ymd', GO.users.lang.jmd]
//		]
//	});
//
//	/* dateseparator */
//	var dateSeperatorData = new Ext.data.SimpleStore({
//		fields: ['id', 'date_separator'],
//		data : [
//		['-', '-'],
//		['.', '.'],
//		['/', '/']
//		]
//	});
	
	/* dateformat */
	var dateFormatData = new Ext.data.SimpleStore({
		fields: ['id', 'dateformat'],
		data : [
		['-:dmY', GO.users.lang.dashdmy],
		['/:mdY', GO.users.lang.slashmdy],
		['/:dmY', GO.users.lang.slashdmy],
		['.:dmY', GO.users.lang.dotdmy],
		['-:Ymd', GO.users.lang.slashymd],
		['.:Ymd', GO.users.lang.dotymd]
		]
	});
	

	/* timeformat */
	var 	timeFormatData = new Ext.data.SimpleStore({
		fields: ['id', 'time_format'],		
		data : [
		['H:i', GO.users.lang.fullhourformat],
		['h:i a', GO.users.lang.halfhourformat]
		]
	});

	/* timeformat */
	var 	firstWeekdayData = new Ext.data.SimpleStore({
		fields: ['id', 'first_weekday'],		
		data : [
		['0', GO.users.lang.sunday],
		['1', GO.users.lang.monday]
		]
	});
	

var dateFormat = GO.settings.date_format.substring(0,1)+GO.settings.date_format.substring(2,3)+GO.settings.date_format.substring(4,5);
	
	config.border=false;
	config.hideLabel=true;
	config.title = GO.users.lang.regionalSettings;
	config.layout='column';
	config.defaults={columnWidth:.5, cls: 'go-form-panel', border:false};
	config.labelWidth=190;
	config.items=[{
			columnWidth:1,
			items: [{
				border:false,
				layout:'form',				
				autoHeight:true,
				defaults: { anchor:'100%' },
				items:[
				this.languageCombo = new Ext.form.ComboBox({
					fieldLabel: GO.users.lang['cmdFormLabelLanguage'],
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
//					value: GO.settings.language
				}),
				new Ext.form.ComboBox({
					fieldLabel: GO.users.lang.cmdFormLabelTimezone,
					name: 'timezone',
					store: new Ext.data.SimpleStore({
							fields: ['timezone'],
							data : GO.users.TimeZones
						}),
					displayField: 'timezone',
					mode: 'local',
					triggerAction: 'all',
					selectOnFocus: true,
					forceSelection: true,
					value: GO.settings.timezone
				}),
//				new Ext.form.ComboBox({
//					fieldLabel: GO.users.lang['cmdFormLabelDateFormat'],
//					name: 'date_format',
//					store: dateFormatData,
//					displayField: 'date_format',
//					value: dateFormat,
//					valueField: 'id',
//					hiddenName: 'date_format',
//					mode: 'local',
//					triggerAction: 'all',
//					editable: false,
//					selectOnFocus: true,
//					forceSelection: true
//				}),
//				new Ext.form.ComboBox({
//					fieldLabel: GO.users.lang['cmdFormLabelDateSeperator'],
//					name: 'date_separator_name',
//					store: dateSeperatorData,
//					displayField: 'date_separator',			
//					value: GO.settings.date_separator,
//					valueField: 'id',
//					hiddenName: 'date_separator',
//					mode: 'local',
//					triggerAction: 'all',
//					editable: false,
//					selectOnFocus: true,
//					forceSelection: true
//				}),
				new Ext.form.ComboBox({
					fieldLabel: GO.users.lang.cmdFormLabelDateFormat,
					name: 'date_format_name',
					store: dateFormatData,
					displayField: 'dateformat',
					valueField: 'id',
					hiddenName: 'dateformat',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					value: GO.settings.dateformat,
					forceSelection: true
				}),
				new Ext.form.ComboBox({
					fieldLabel: GO.users.lang.timeFormat,
					name: 'time_format_name',
					store: timeFormatData,
					displayField: 'time_format',
					valueField: 'id',
					hiddenName: 'time_format',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					value: GO.settings.time_format,
					forceSelection: true
				}),
					
				new Ext.form.ComboBox({
					fieldLabel: GO.users.lang['cmdFormLabelFirstWeekday'],
					name: 'first_weekday_name',
					store: firstWeekdayData,
					displayField: 'first_weekday',
					valueField: 'id',
					hiddenName: 'first_weekday',
					mode: 'local',
					triggerAction: 'all',
					editable: false,
					selectOnFocus: true,
					forceSelection: true,
					value: GO.settings.first_weekday
				}),
				this.holidaysetCombo = new GO.form.ComboBox({
					fieldLabel: GO.users.lang['cmdFormLabelHolidaySet'],
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
//					value: GO.settings.language
				})]
			}]
	},{
			items: [{
				xtype:'fieldset',		
				defaults: { width:50 },
				autoHeight:true,
				title:GO.users.lang.numberFormat,
				items:[{
						xtype: 'textfield', 
						fieldLabel: GO.users.lang['cmdFormLabelThousandSeperator'], 
						name: 'thousands_separator',
						value: GO.settings.thousands_separator
					},
					{
						xtype: 'textfield', 
						fieldLabel: GO.users.lang['cmdFormLabelDecimalSeperator'], 
						name: 'decimal_separator',
						value: GO.settings.decimal_separator
					},
					{
						xtype: 'textfield', 
						fieldLabel: GO.users.lang['cmdFormLabelCurrency'], 
						name: 'currency',
						value: GO.settings.currency
					}]
			}]
	},{
			items:[{
					xtype:'fieldset',
					defaults: { width:50 },
					autoHeight:true,
					title:GO.users.lang.importExport,
					items:[{
						xtype: 'textfield', 
						fieldLabel: GO.users.lang.listSeparator, 
						name: 'list_separator',
						value: GO.settings.list_separator
					},{
						xtype: 'textfield', 
						fieldLabel: GO.users.lang.textSeparator, 
						name: 'text_separator',
						value: GO.settings.text_separator
					}]
		}]
	}];
	
	GO.users.RegionalSettingsPanel.superclass.constructor.call(this, config);		
};


Ext.extend(GO.users.RegionalSettingsPanel, Ext.Panel,{
	

});			