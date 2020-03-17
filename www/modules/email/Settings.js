GO.email.SettingsPanel = Ext.extend(Ext.Panel, {
	title : t("E-mail"),
	iconCls: 'ic-email',
	autoScroll: true,
	layout: "border",
	initComponent: function() {

		this.items=[{
			xtype:'fieldset',
			title:t("Options"),
			region: "north",
			autoHeight: true,
			items:[
//			this.sortBySendMailTime = new Ext.form.Checkbox({
//				boxLabel:t("Sort recipient addresses by time of last email sending (requires Address Book module)", "email"),
//				hideLabel:true,				
//				name:'emailSettings.sort_email_addresses_by_time'
//			}),

//			this.templateCombo = new GO.email.TemplateCombo({
//				hiddenName: "emailSettings.defaultTemplateId",
//				fieldLabel: t("Default template")				
//			}),
			this.useHtml = new Ext.form.Checkbox({
				boxLabel:t("Use HTML markup", "email"),
				hideLabel:true,				
				name:'emailSettings.use_html_markup'
			}),this.showCC = new Ext.form.Checkbox({
				boxLabel:t("Show CC field by default", "email"),
				hideLabel:true,
				name:'emailSettings.show_cc'
			}),this.showBCC = new Ext.form.Checkbox({
				boxLabel:t("Show BCC field by default", "email"),
				hideLabel:true,
				name:'emailSettings.show_bcc'
			}),this.skipUnknownRecipients = new Ext.form.Checkbox({
				boxLabel:t("Don't show unknown recipients dialog", "email"),
				hideLabel:true,				
				name:'emailSettings.skip_unknown_recipients'
			}),this.alwaysRequestNotification = new Ext.form.Checkbox({
				boxLabel:t("Always request a read notification", "email"),
				hideLabel:true,				
				name:'emailSettings.always_request_notification'
			}),this.alwaysRespondToNotifications = new Ext.form.Checkbox({
				boxLabel:t("Always respond to a read notification", "email"),
				hideLabel:true,				
				name:'emailSettings.always_respond_to_notifications'
			}),this.sortOnMailTime = new Ext.form.Checkbox({
					boxLabel:t("Sort on last contact mail time", "email"),
					hideLabel:true,
					name:'emailSettings.sort_email_addresses_by_time'
			})
			// ,this.fontSize = new GO.form.ComboBox({
			// 	fieldLabel:t("Default font size", "email"),
			// 	name:'emailSettings.font_size',
			// 	store : new Ext.data.SimpleStore({
			// 		fields : ['value'],
			// 		data : [
			// 			['10px'],['11px'],['12px'],['13px'],['14px'],['15px'],['16px'],
			// 			['17px'],['18px'],['19px'],['20px'],['21px'],['22px'],['23px'],['24px']
			// 		]
			// 	}),
			// 	width:80,
			// 	value : GO.email.fontSize,
			// 	valueField : 'value',
			// 	displayField : 'value',
			// 	mode : 'local',
			// 	triggerAction : 'all',
			// 	editable : false,
			// 	selectOnFocus : true,
			// 	forceSelection : true
			// })
		]
			},
//			this.templateGrid = new GO.email.TemplateGrid({
//				ownedBy: null
//			}),
			this.templatesGrid = new GO.email.TemplatesGrid({
				region: "center"
			})
		];
		
		GO.email.SettingsPanel.superclass.initComponent.call(this);
	},
	
	
	onLoadComplete : function(user) {
		//this.templateGrid.setOwnedBy(user.id);
	},

	onSubmitComplete : function() {
		GO.email.useHtmlMarkup=this.useHtml.getValue();
		GO.email.showCCfield=this.showCC.getValue();
		GO.email.showBCCfield=this.showBCC.getValue();
		GO.email.skipUnknownRecipients=this.skipUnknownRecipients.getValue();
		GO.email.alwaysRequestNotification=this.alwaysRequestNotification.getValue();
		GO.email.alwaysRespondToNotifications=this.alwaysRespondToNotifications.getValue();
	}

});
