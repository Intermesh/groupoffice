/* global go */

go.emailtemplate.TemplateDialog = Ext.extend(go.form.Dialog, {
	title: t('E-mail template'),
	entityStore: "EmailTemplate",
	width: dp(1000),
	height: dp(800),
	formPanelLayout: "fit",
	resizable: true,
	maximizable: true,
	collapsible: true,
	modal: false,

	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel());

		return [{
			xtype: 'fieldset',
			layout: "border",
			items: [{
				region: "center",
				xtype: "panel",
				layout: "form",
				defaults: {
					anchor: '100%'
				},
				items: [
				{
					xtype: 'textfield',
					name: 'name',
					fieldLabel: t("Name")
				}, {
					xtype: 'textfield',
					name: 'subject',
					fieldLabel: t("Subject")
				}, {
					anchor: "100% -" + dp(96),
					xtype: 'xhtmleditor',
					plugins: [new GO.plugins.HtmlEditorImageInsert()],
					name: 'body',
					hideLabel: true,
					listeners: {
						attach: this.onAttach,
						scope: this
					}
				}
				]
			},

			this.attachments = new go.form.AttachmentsField({
				region: "south",
				name: "attachments"
			})
			]
		}
		];
	},

	onAttach: function (htmleditor, blob, file, imgEl) {

		//Inline images are parsed form the body and should not be sent as attachment
		if (imgEl) {
			return;
		}

		this.attachments.addAttachment({
			blobId: blob.blobId,
			name: file.name,
			attachment: true
		});
	}
});


