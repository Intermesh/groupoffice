go.emailtemplate.TemplateFieldset = Ext.extend(Ext.form.FieldSet, {
	xtype: 'fieldset',
	layout: "border",
	hideLanguage: false,

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
	},

	initComponent : function() {
		this.items = [{
			region: "center",
			xtype: "panel",
			layout: "form",
			defaults: {
				anchor: '100%'
			},
			items: [
				{
					xtype: 'golanguagecombo',
					hidden: this.hideLanguage
				}, {
					xtype: 'textfield',
					name: 'subject',
					fieldLabel: t("Subject")
				}, {
					anchor: "100% -" + (this.noName ? dp(150) : dp(200)),
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
		];


		if(!this.noName) {
			this.items[0].items.unshift({
				xtype: 'textfield',
				name: 'name',
				fieldLabel: t("Name")
			});
		}

		this.supr().initComponent.call(this);
	}

});
