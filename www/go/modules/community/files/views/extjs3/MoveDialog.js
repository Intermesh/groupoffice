go.modules.community.files.MoveDialog = Ext.extend(go.form.Dialog, {
	stateId: 'files-moveDialog',
	title: t("Move"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		var items = [];
		
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
			folderSelectMode:true
		});
		
		items.push(this.folderTree);
		
		return items;
	}
});