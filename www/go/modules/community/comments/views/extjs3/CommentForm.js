go.modules.comments.CommentForm = Ext.extend(go.form.FormWindow, {
	stateId: 'comments-commentForm',
	title: t("Comment", "comments"),
	entityStore: go.Stores.get("Comment"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		var items = [{
				xtype: 'fieldset',
				autoHeight: true,
				items: [
					new go.modules.comments.CategoryCombo(),
					{
						xtype: 'xhtmleditor',
						name: 'comment',
						fieldLabel: "",
						hideLabel: true,
						anchor: '100%',
						height: 300,
						allowBlank: false
					}]
			}
		]

		return items;
	},
	getSubmitValues: function(){
		var values = go.modules.comments.CommentForm.superclass.getSubmitValues.call(this);
		
		values.entityId=this.entityId;
		values.entity=this.entity;
		
		return values;
	},
	show : function(entityId,entity){
	
		this.entityId = entityId;
		this.entity= entity;
		
		go.modules.comments.CommentForm.superclass.show.call(this);
	}
	
	
});
