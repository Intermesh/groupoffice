GO.customfields.TreeSelectOptions = function(config){

	config = config || {};

	Ext.apply(config, {
		animate:true,
		height:200,
		disapled:true,
		autoScroll:true,
		loader: new GO.base.tree.TreeLoader({
			dataUrl:GO.url('customfields/fieldTreeSelectOption/tree'),
			baseParams:{
				field_id:0
			}
		}),
		enableDrag:true,
		containerScroll: true,
		border: true
	});

	config.tbar=[{
		text:t("Add"),
		handler:function(){
			var node = this.selModel.getSelectedNode();
			if(!node)
			{
				node=this.rootNode;
			}

			var newNode = new Ext.tree.AsyncTreeNode({
				text: '',
				id: '0',
				expanded:true,
				children:[],
				iconCls:'folder-default'
			});

			newNode = node.appendChild(newNode);

			this.treeEditor.triggerEdit(newNode);
		},
		scope:this,
		iconCls:'btn-add'
	},'-',
	{
		text:t("Delete"),
		iconCls:'btn-delete',
		handler:function(){
			var node = this.selModel.getSelectedNode();
			if(!node)
			{
				alert(t("You didn't select an item."));
				return false;
			}
			GO.request({
				url:'customfields/fieldTreeSelectOption/delete',
				params:{					
					id:node.id					
				},
				success: function(response, options, result)
				{
					if(!result.success)
					{
						this.reload();
						alert(result.feedback);
					}else
					{
						if(node.parentNode){
							node.parentNode.reload();
							node.destroy();
						}else
						{
							this.rootNode.reload();
						}
					}
				},
				scope:this
			});
		},
		scope:this
	},{
		iconCls: 'btn-upload',
		text:t("Import"),
		handler:this.importSelectOptions,
		scope:this
	}];


	


	GO.customfields.TreeSelectOptions.superclass.constructor.call(this, config);

	this.treeEditor = new Ext.tree.TreeEditor(
		this,
		new Ext.form.TextField({
			cancelOnEsc:true,
			completeOnEnter:true,
			maskRe:/[^:]/
		}),
		{
			listeners:{
				complete  : this.afterEdit,
				beforecomplete  : function( editor, value, startValue){
					value=value.trim();
					if(GO.util.empty(value)){
						editor.focus();
						return false;
					}
				},
				scope:this
			}
		});

	// set the root node
	this.rootNode = new Ext.tree.AsyncTreeNode({
		text: 'Root',
		draggable:false, // disable root node dragging
		id:'root',
		iconCls:'folder-default',
		editable:false
	});
	this.setRootNode(this.rootNode);
}

Ext.extend(GO.customfields.TreeSelectOptions,Ext.tree.TreePanel, {
	importSelectOptions : function(){

		if(!this.importDialog)
		{
			this.importDialog = new GO.customfields.ImportDialog({
				importText:t("You can import a CSV file where each column represents a tree level. eg.<br />\"option 1\",\"option 1.1\", \"option 1.1.1\"<br />\"option 1\",\"option 1.2\", \"option 1.2.1\". Please replace the comma with your personal setting of Group-Office.", "customfields"),
				task:'treeselect_import',				
				listeners:{
					scope:this,
					importSelectOptions:function(){this.rootNode.reload();}
				}
			});

		}
		this.importDialog.upForm.baseParams.field_id=	this.getLoader().baseParams.field_id;
		this.importDialog.show();
	},

	setFieldId : function(field_id){
		this.setDisabled(!field_id);
		this.getLoader().baseParams.field_id=field_id;
		if(field_id>0)
		{			
			this.rootNode.reload();
		}
	},

	afterEdit : function(editor, text, oldText ){

		GO.request({
			url:'customfields/fieldTreeSelectOption/submit',
			params:{				
				parent_id:editor.editNode.parentNode.id=='root' ? 0 : editor.editNode.parentNode.id,
				id:editor.editNode.id,
				field_id:this.loader.baseParams.field_id,
				name:text
			},
			success: function(response, options, result)
			{
				if(!result.success)
				{
					if(editor.editNode.id=='0')
						editor.editNode.destroy();

					alert(result.feedback);
				}

				if(result.id)
					editor.editNode.setId(result.id);
			},
			scope:this
		});
	}
});
