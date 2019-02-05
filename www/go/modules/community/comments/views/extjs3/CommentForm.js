go.modules.comments.CommentForm = Ext.extend(go.form.Dialog, {
	stateId: 'comments-commentForm',
	title: t("Comment", "comments"),
	entityStore: go.Stores.get("Comment"),	
	width: 600,
	height: 500,
	
	initFormItems: function () {
		return [
			{xtype:'hidden',name:'entityTypeId'},
			{xtype:'hidden', name: 'entityId'},
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
		];
	},

	show : function(entityId,entity){
		//this.formPanel.form.findField('entityTypeId').setValue(entity);
		this.formPanel.form.findField('entityId').setValue(parseInt(entityId));
		
		go.modules.comments.CommentForm.superclass.show.call(this);
	}
	
	
});
