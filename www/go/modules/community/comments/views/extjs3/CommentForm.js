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
				new go.form.CKEditor({
					editorConfig: {
						removeButtons: 'Format,Font,FontSize,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock',
					},
					name: 'text',
					fieldLabel: "",
					hideLabel: true,
					anchor: '100%',
					allowBlank: false,
					grow: true
				})
			]
		}

		];
	}
});
