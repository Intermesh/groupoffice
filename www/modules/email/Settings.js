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
			layout: "column",
			items:[

				{
					xtype: "container",
					columnWidth: .5,
					layout: "form",
					items: [
						this.templateCombo = new GO.email.TemplateCombo({
							anchor: '90%',
							hiddenName: "emailSettings.defaultTemplateId",
							fieldLabel: t("Default template"),
							anchor: "-20"
						}),

						this.useHtml = new Ext.form.Checkbox({
							boxLabel:t("Use HTML markup", "email"),
							hideLabel:true,
							name:'emailSettings.use_html_markup'
						}),this.showFrom = new Ext.form.Checkbox({
							boxLabel:t("Show from field by default", "email"),
							hideLabel:true,
							name:'emailSettings.show_from'
						}),this.showCC = new Ext.form.Checkbox({
							boxLabel:t("Show CC field by default", "email"),
							hideLabel:true,
							name:'emailSettings.show_cc'
						}),this.showBCC = new Ext.form.Checkbox({
							boxLabel:t("Show BCC field by default", "email"),
							hideLabel:true,
							name:'emailSettings.show_bcc'
						})
					]
				},

				{
					layout: "form",
					xtype: "container",
					columnWidth: .5,
					items: [
						new Ext.form.Checkbox({
							boxLabel:t("Use desktop email client to compose", "email"),
							hideLabel:true,
							name:'emailSettings.use_desktop_composer'
						}),
						this.skipUnknownRecipients = new Ext.form.Checkbox({
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
					]
				}

		]
			},
			this.templatesGrid = new GO.email.TemplatesGrid({
				region: "center"
			})
		];

		this.templateCombo.store.baseParams.permissionLevel = go.permissionLevels.read;
		
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
