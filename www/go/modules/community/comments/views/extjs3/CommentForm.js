go.modules.comments.CommentForm = Ext.extend(go.form.Dialog, {
	stateId: 'comments-commentForm',
	title: t("Comment", "comments"),
	entityStore: "Comment",
	width: 1000,
	height: 600,
	formPanelLayout: "form",
	initFormItems: function () {
		return [{
			xtype: "fieldset",
			items: [
				{
					xtype: "datetimefield",
					name: "date",
					fieldLabel: t("Date")
				},
				new go.form.HtmlEditor({
					enableFont: false,
					enableFontSize: false,
					enableAlignments: false,
					//enableSourceEdit: false,
					//plugins: [go.form.HtmlEditor.emojiPlugin],
					name: 'text',
					fieldLabel: "",
					hideLabel: true,
					anchor: '100%',
					allowBlank: false,
					grow: true,
					listeners: {
						ctrlenter: function() {
							this.sendBtn.handler.call(this);
						},

						attach: ( field, response, file, imgEl) => {
							if(imgEl) {
								return;
							}

							this.attachmentBox.setValue(this.attachmentBox.getValue().concat([{
								blobId: response.blobId,
								name: response.name
							}]))

							this.onSync()
						},

						scope: this
					}
				}),
				this.attachmentBox = new go.form.FormGroup({
					hideBbar: true,
					name:"attachments",
					startWithItem: false,
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
			]
		}

		];
	}
});
