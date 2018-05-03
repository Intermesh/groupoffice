go.modules.community.files.BrowseWindow = Ext.extend(Ext.Window, {
	
	title: t("Browse files"),
	width: 600,
	height: 600,
	browser:null,

	initComponent: function () {
		
		this.browser = new go.modules.community.files.Browser({
			store: new go.data.Store({
				fields: ['id', 'name', 'byteSize','isDirectory', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'aclId'],
				baseParams: {
					filter:{isHome:true}
				},
				entityStore: go.Stores.get("Node")
			})
		});
		
		this.folderTree = new go.modules.community.files.FolderTree({
			browser:this.browser,
			region: 'west'
		});
		
		this.centerCardPanel = new go.modules.community.files.CenterPanel({
			region: 'center',
			browser: this.browser
		});
		
		go.modules.community.files.BrowseWindow.superclass.initComponent.call(this);
	}	
});