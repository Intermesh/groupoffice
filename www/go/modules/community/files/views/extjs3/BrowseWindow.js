go.modules.community.files.BrowseWindow = Ext.extend(Ext.Window, {
	
	title: t("Browse files"),
	width: 900,
	height: 600,
	browser:null,
	layout:'border',

	initComponent: function () {
		
		this.browser = new go.modules.community.files.Browser({
			store: new go.data.Store({
				fields: ['id', 'name', 'size','isDirectory', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'aclId'],
				baseParams: {
					filter:{isHome:true}
				},
				entityStore: go.Stores.get("Node")
			})
		});
		
		this.folderTree = new go.modules.community.files.FolderTree({
			browser:this.browser,
			region: 'west',
			width:200
		});
		
		this.breadCrumbs = new go.modules.community.files.BreadCrumbBar({
			browser:this.browser
		});
		
		this.centerCardPanel = new go.modules.community.files.CenterPanel({
			region: 'center',
			browser: this.browser,
			tbar:[
				this.breadCrumbs
			]
		});
		
		this.items = [
			this.folderTree,
			this.centerCardPanel
		];
		
		go.modules.community.files.BrowseWindow.superclass.initComponent.call(this);
	}	
});
