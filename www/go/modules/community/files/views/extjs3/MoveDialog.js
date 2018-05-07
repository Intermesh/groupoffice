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
		
		var browser = new go.modules.community.files.Browser({
			store: new go.data.Store({
				fields: ['id', 'name', 'size','isDirectory', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'aclId'],
				baseParams: {
					filter:{isHome:true}
				},
				entityStore: this.entityStore
			})
		});
		
		this.folderTree = new go.modules.community.files.FolderTree({
			browser:browser,
			folderSelectMode:true, // This will remove the contextmenu from the tree items
			listeners: {
				'click':function(node,e){
					
					if(node.attributes.entity){ // Root nodes don't have an entity set
						this.parentIdField.setValue(node.attributes.entityId);
					}
					
					if(node.attributes.entityId === 'my-files'){
						this.parentIdField.setValue(go.User.storage.rootFolderId);
					}
				},
				scope:this
			}
		});
		
		items.push(this.parentIdField);
		items.push(this.folderTree);
		
		return items;
	}
});