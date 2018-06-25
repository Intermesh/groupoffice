go.modules.community.files.MoveDialog = Ext.extend(go.form.Dialog, {
	stateId: 'files-moveDialog',
	title: t("Move"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		var items = [];
		
		this.parentIdField = new Ext.form.Hidden({
			name:'parentId',
			hiddenName:'parentId'
		});
		
		this.browser = new go.modules.community.files.Browser({
			useRouter: false,
			rootConfig:{
				storages:true
			}
		});
		
		this.folderTree = new go.modules.community.files.FolderTree({
			browser: this.browser,
			folderSelectMode:true, // This will remove the contextmenu from the tree items
			listeners: {
				'click':function(node,e){
					
					if(node.attributes.entity){ // Root nodes don't have an entity set
						this.parentIdField.setValue(node.attributes.entityId);
					}
				},
				scope:this
			}
		});
		
		items.push(this.parentIdField);
		items.push(this.folderTree);
				
		return items;
	},
	
	submit: function() {
		var folder = go.Stores.get('Node').get(currentId),
			 callback = this.copy ? go.files.community.copy : go.files.community.move
		this.browser.receive([folder], callback , this.parentIdField.getValue());
	}
});