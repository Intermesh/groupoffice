/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LookAndFeelPanel.js 22112 2018-01-12 07:59:41Z mschering $
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
	config.title = t("Look & Feel", "users");
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
			fieldLabel: t("Theme", "users"),
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
			fieldLabel: t("Start in module", "users"),
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
		});
		
		config.items.push({
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
		});

    this.cbMuteReminderSound = new Ext.ux.form.XCheckbox({
      hideLabel:true,
      boxLabel: t("Mute reminder sounds", "users"),
			name: 'mute_reminder_sound'
    });

    this.cbMuteNewMailSound = new Ext.ux.form.XCheckbox({
      hideLabel: true,
			boxLabel: t("Mute new mail sounds", "users"),
			name: 'mute_new_mail_sound'
    });

		config.items.push({
			xtype:'xcheckbox',
			hideLabel: true,
			boxLabel: t("Mute all sounds", "users"),
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
//			boxLabel: t("Mute reminder sounds", "users"),
//			name: 'mute_reminder_sound'
//		}
//    ,{
//			xtype:'checkbox',
//			hideLabel: true,
//			boxLabel: t("Mute new mail sounds", "users"),
//			name: 'mute_new_mail_sound'
//		},

    this.cbMuteNewMailSound,
    {
		xtype:'xcheckbox',
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
		}, {
		xtype: 'xcheckbox',
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
	}, {
		xtype:'xcheckbox',
		hideLabel: true,
		boxLabel: t("Mail reminders", "users"),
		name: 'mail_reminders'
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
	},{
		xtype:'button',
		style:'margin-top:20px',
		handler:this.resetState,
		scope:this,
		text:t("Reset windows and grids", "users"),
		anchor:''
	});
	
	
	GO.users.LookAndFeelPanel.superclass.constructor.call(this, config);		
}


Ext.extend(GO.users.LookAndFeelPanel, Ext.Panel,{
	resetState : function(){
		if(confirm(t("Are you sure you want to reset all grid columns, windows, panel sizes etc. to the factory defaults?", "users"))){
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
