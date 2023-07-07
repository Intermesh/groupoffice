go.modules.comments.ComposerFieldset = Ext.extend(Ext.form.FieldSet, {
	title: t("Message"),

	initComponent: function() {


		this.items = [
			this.editor = new go.form.HtmlEditor({
				anchor: "100%",
				submit: false,

				iframePad: 0,
				grow: true,
				growMin: dp(32),
				//enableColors: false,
				enableFont: false,
				headingsMenu: false,
				enableFontSize: false,
				enableAlignments: false,
				enableSourceEdit: true,
				// toolbarHidden: true,
				// emptyText: t('Add comment')+'...',
				allowBlank: false,
				plugins: [new GO.plugins.HtmlEditorImageInsert(), go.form.HtmlEditor.emojiPlugin],
				name: 'text',
				listeners: {



					attach: ( field, response, file, imgEl) => {


						if(imgEl) {
							return;
						}


						this.attachmentBox.setValue(this.attachmentBox.getValue().concat([{
							blobId: response.blobId,
							name: response.name
						}]));

					},


					scope: this
				}
			}),

			this.attachmentBox = new go.form.FormGroup({
				hideBbar: true,
				name:"attachments",
				startWithItem: false,
				submit: false,
				itemCfg : {
					items: [{
						hideLabel: true,
						xtype: "plainfield",
						name: "name",
						submit: true
					},{
						hideLabel: true,
						xtype: "hidden",
						name: "blobId"
					}]
				}
			})
		];

		this.supr().initComponent.call(this);
	},

	save : function(entityName, entityId) {
		this.getEl().mask(t("Saving..."));
		return go.Db.store("Comment").save(
			{
				entity: entityName,
				entityId: entityId,
				text: this.editor.getValue(),
				attachments: this.attachmentBox.getValue()
			}
		).finally(() => {
			this.getEl().unmask();
		})
	}
})