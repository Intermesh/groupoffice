GO.email.SettingsPanel = function(config) {
	if (!config) {
		config = {};
	}


	config.autoScroll = true;
	config.border = false;
	config.hideLabel = true;
	config.title = GO.lang.strEmail;
	config.hideMode = 'offsets';
	config.layout = 'form';
	config.bodyStyle = 'padding:5px';
	config.labelWidth=150;
	
	config.items=[{
		xtype:'fieldset',
		title:GO.email.lang.defaultProgram,
		autoHeight:true,		
		html:GO.email.lang.defaultProgramInstructions.replace('{url}', GO.url("email/register/downloadWin7")).replace('{product_name}', GO.settings.config.product_name)
	},
	this.sortBySendMailTime = new Ext.form.Checkbox({
		boxLabel:GO.email.lang.sortAddressesByMailTime,
		hideLabel:true,
		checked:GO.email.sortBySendMailTime,
		name:'sort_email_addresses_by_time'
	}),
	this.useHtml = new Ext.form.Checkbox({
		boxLabel:GO.email.lang.htmlMarkup,
		hideLabel:true,
		checked:GO.email.useHtmlMarkup,
		name:'use_html_markup'
	}),this.showCC = new Ext.form.Checkbox({
		boxLabel:GO.email.lang.showCcByDefault,
		hideLabel:true,
		checked:GO.email.showCCfield,
		name:'email_show_cc'
	}),this.showBCC = new Ext.form.Checkbox({
		boxLabel:GO.email.lang.showBccByDefault,
		hideLabel:true,
		checked:GO.email.showBCCfield,
		name:'email_show_bcc'
	}),this.skipUnknownRecipients = new Ext.form.Checkbox({
		boxLabel:GO.email.lang.skipUnknownRecipients,
		hideLabel:true,
		checked:GO.email.skipUnknownRecipients,
		name:'skip_unknown_recipients'
	}),this.alwaysRequestNotification = new Ext.form.Checkbox({
		boxLabel:GO.email.lang.alwaysRequestNotification,
		hideLabel:true,
		checked:GO.email.alwaysRequestNotification,
		name:'always_request_notification'
	}),this.alwaysRespondToNotifications = new Ext.form.Checkbox({
		boxLabel:GO.email.lang.alwaysRespondToNotifications,
		hideLabel:true,
		checked:GO.email.alwaysRespondToNotifications,
		name:'always_respond_to_notifications'
	}),this.fontSize = new GO.form.ComboBox({
			fieldLabel:GO.email.lang.defaultFontSize,
			name:'font_size',
			store : new Ext.data.SimpleStore({
				fields : ['value'],
				data : [
					['10px'],
					['11px'],
					['12px'],
					['13px'],
					['14px'],
					['15px'],
					['16px'],
					['17px'],
					['18px'],
					['19px'],
					['20px'],
					['21px'],
					['22px'],
					['23px'],
					['24px']
				]
			}),
			width:70,
			value : GO.email.fontSize,
			valueField : 'value',
			displayField : 'value',
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true

	})];


	GO.email.SettingsPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.email.SettingsPanel, Ext.Panel, {
	onLoadSettings : function(action) {

	},

	onSaveSettings : function() {
		GO.email.useHtmlMarkup=this.useHtml.getValue();
		GO.email.showCCfield=this.showCcByDefault.getValue();
		GO.email.showBCCfield=this.showBccByDefault.getValue();
		GO.email.skipUnknownRecipients=this.skipUnknownRecipients.getValue();
		GO.email.alwaysRequestNotification=this.alwaysRequestNotification.getValue();
		GO.email.alwaysRespondToNotifications=this.alwaysRespondToNotifications.getValue();
	}

});

GO.mainLayout.onReady(function() {
			GO.moduleManager.addSettingsPanel('email',
					GO.email.SettingsPanel);
		});