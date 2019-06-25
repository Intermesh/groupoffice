go.modules.comments.CommentForm = Ext.extend(go.form.Dialog, {
	stateId: 'comments-commentForm',
	title: t("Comment", "comments"),
	entityStore: "Comment",
	width: 600,
	height: 500,
	formPanelLayout: "fit",
	initFormItems: function () {
		return [{
			xtype: "fieldset",
			items: [
				new go.form.HtmlEditor({
					enableFont: false,
					enableFontSize: false,
					enableAlignments: false,
					enableSourceEdit: false,
					plugins: [go.form.HtmlEditor.emojiPlugin],
					name: 'text',
					fieldLabel: "",
					hideLabel: true,
					anchor: '100% 100%',
					allowBlank: false
				})
			]
		}

		];
	}
});
