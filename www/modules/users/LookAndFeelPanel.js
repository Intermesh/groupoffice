/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LookAndFeelPanel.js 20022 2016-05-02 12:19:19Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.LookAndFeelPanel = function(config)
{
	if(!config)
	{
		config={};
	}
	
	
	this.autoScroll=true;
	
	config.border=false;
	config.hideLabel=true;
	config.title = GO.users.lang.cmdPanelLookFeel;
	config.layout='form';
	config.defaults={anchor:'100%'};
	config.defaultType = 'textfield';
	config.cls='go-form-panel';
	config.labelWidth=190;
	
	
	var themesStore = new GO.data.JsonStore({
		url: GO.url('core/themes'),
		fields:['theme','label'],
		remoteSort: true,
		autoLoad:true
	});

	this.modulesStore = new GO.data.JsonStore({
		url: GO.url('core/modules'),
		baseParams: {user_id:0},
		fields:['id','name'],
		remoteSort: true
	});
	
	
	config.items=[];
	
	if(GO.settings.config.allow_themes)
	{
		config.items.push(this.themeCombo = new Ext.form.ComboBox({
			fieldLabel: GO.users.lang['cmdFormLabelTheme'],
			name: 'theme',
			store: themesStore,
			displayField:'label',
			valueField: 'theme',		
			mode:'local',
			triggerAction:'all',
			editable: false,
			selectOnFocus:true,
			forceSelection: true,
			value: GO.settings.config.theme
		}));
	}
	
	config.items.push(this.startModuleField = new GO.form.ComboBox({
			fieldLabel: GO.users.lang['cmdFormLabelStartModule'],
			name: 'start_module_name',
			hiddenName: 'start_module',
			store: this.modulesStore,
			displayField:'name',
			valueField: 'id',
			mode:'remote',
			triggerAction:'all',
			editable: false,
			selectOnFocus:true,
			forceSelection: true,
			value: GO.settings.start_module
		}));
		
		config.items.push({
			xtype:'combo',
			fieldLabel: GO.users.lang['cmdFormLabelMaximunRows'],
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
		});
		
		config.items.push({
			xtype:'combo',
			fieldLabel: GO.users.lang['cmdFormLabelSortNamesBy'],
			store: new Ext.data.SimpleStore({
				fields: ['value', 'text'],
				data : [
				['first_name',GO.users.lang.firstName],
				['last_name',GO.users.lang.lastName]
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
		});

    this.cbMuteReminderSound = new Ext.ux.form.XCheckbox({
      hideLabel:true,
      boxLabel: GO.users.lang.muteReminderSound,
			name: 'mute_reminder_sound'
    });

    this.cbMuteNewMailSound = new Ext.ux.form.XCheckbox({
      hideLabel: true,
			boxLabel: GO.users.lang.muteNewMailSound,
			name: 'mute_new_mail_sound'
    });

		config.items.push({
			xtype:'xcheckbox',
			hideLabel: true,
			boxLabel: GO.users.lang.muteSound,
			name: 'mute_sound',
      listeners:{
        check: function(cb, val){
          if(val)
          {
            this.cbMuteNewMailSound.disable();
            this.cbMuteReminderSound.disable();
          }
          else
          {
            this.cbMuteNewMailSound.enable();
            this.cbMuteReminderSound.enable();
          }
        },scope:this
      }
		},
    this.cbMuteReminderSound,
//    {
//			xtype:'checkbox',
//			hideLabel: true,
//			boxLabel: GO.users.lang.muteReminderSound,
//			name: 'mute_reminder_sound'
//		}
//    ,{
//			xtype:'checkbox',
//			hideLabel: true,
//			boxLabel: GO.users.lang.muteNewMailSound,
//			name: 'mute_new_mail_sound'
//		},

    this.cbMuteNewMailSound,
    {
		xtype:'xcheckbox',
		hideLabel: true,
			boxLabel: GO.users.lang.popupReminderNotification,
		name: 'popup_reminders',
		listeners: {
			check: function(cb,checked) {
				if(checked) {
					var options = {
						body: GO.users.lang.desktopNotificationsActive,
						icon: 'views/Extjs3/themes/Group-Office/images/groupoffice.ico'
					}
					// Let's check if the browser supports notifications
					if (!("Notification" in window)) {
						// Browser does not support desktop notification and will show a popup instead
					} else if (Notification.permission !== 'granted' && (Notification.permission !== 'denied' || Notification.permission === "default")) {
					  Notification.requestPermission(function (permission) {
						// If the user accepts, let's create a notification
						if (permission === "granted") {
						   var notification = new Notification(GO.users.lang.desktopNotificationsActive,options);
						} else {
							cb.setValue(false);
						}
					  });
					}
				} 
			}
		}
		}, {
		xtype: 'xcheckbox',
		hideLabel: true,
		boxLabel: GO.users.lang.popupEmailNotification,
		name: 'popup_emails',
		listeners: {
			check: function (cb, checked) {
				if (checked) {
					var options = {
						body: GO.users.lang.desktopNotificationsActive,
						icon: 'views/Extjs3/themes/Group-Office/images/groupoffice.ico'
					}
					// Let's check if the browser supports notifications
					if (!("Notification" in window)) {
						// Browser does not support desktop notification and will show a popup instead
					} else if (Notification.permission !== 'granted' && (Notification.permission !== 'denied' || Notification.permission === "default")) {
						Notification.requestPermission(function (permission) {
							// If the user accepts, let's create a notification
							if (permission === "granted") {
								var notification = new Notification(GO.users.lang.desktopNotificationsActive, options);
							} else {
								cb.setValue(false);
							}
						});
					}
				}
			}
		}
	}, {
		xtype:'xcheckbox',
		hideLabel: true,
		boxLabel: GO.users.lang.mailReminders,
		name: 'mail_reminders'
	},{
		xtype:'xcheckbox',
		hideLabel: true,
		boxLabel: GO.users.lang.showSmilies,
		name: 'show_smilies'
	},{
		xtype:'xcheckbox',
		hideLabel: true,
		boxLabel: GO.users.lang.autoPunctuation,
		name: 'auto_punctuation'
	},{
		xtype:'button',
		style:'margin-top:20px',
		handler:this.resetState,
		scope:this,
		text:GO.users.lang.resetState,
		anchor:''
	});
	
	
	GO.users.LookAndFeelPanel.superclass.constructor.call(this, config);		
}


Ext.extend(GO.users.LookAndFeelPanel, Ext.Panel,{
	resetState : function(){
		if(confirm(GO.users.lang.resetStateConfirm)){
			GO.request({
				maskEl:Ext.getBody(),
				url:'maintenance/resetState',
				params:{
					user_id:this.ownerCt.ownerCt.ownerCt.remoteModelId
				},
				success:function(){
					document.location.reload();
				},
				scope:this
			});
		}
	},
	onLoadSettings : function(action){
		this.startModuleField.setRemoteText(action.result.data.start_module_name);
	}
});			