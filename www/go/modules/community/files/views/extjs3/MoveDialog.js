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
		
		this.on('afterrender', function() {
		go.Files.onReady(function (files) {

				this.browser.getRootNode('my-files').params.filter.parentId = files.myFilesFolderId;
				var me = this;
				this.folderTree.getTreeNodesByEntityId('my-files').forEach(function (node) {

					node.attributes.params.filter.parentId = files.myFilesFolderId;
					//delete node.childNodes;
					node.expanded = true;
					delete node.attributes.children;

					node.reload(function () {
						me.folderTree.openPath(me.browser.getPath(true));
					}, this);

				});
			}, this);
		});
		
		return items;
	}
});