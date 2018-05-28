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
			useRouter: false,
			rootNodes: [
				{
					text: t('My files'),
					iconCls: 'ic-home',
					entityId: 'my-files',
					draggable: false,
					expanded: true,
					children: [], //to prevent router to load this node before params.filter.parentId is set after fetching the storage
					params: {
						filter: {
							parentId: null
						}
					}
				}]
		});
		
		this.folderTree = new go.modules.community.files.FolderTree({
			browser:browser,
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
	}
});