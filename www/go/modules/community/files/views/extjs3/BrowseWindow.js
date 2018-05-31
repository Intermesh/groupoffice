go.modules.community.files.BrowseWindow = Ext.extend(Ext.Window, {
	
	title: t("Browse files"),
	width: 900,
	height: 600,
	browser:null,
	layout:'border',
	currentId:null,
	initComponent: function () {
		
		this.entityStore = go.Stores.get("Node");
		
		this.browser = new go.modules.community.files.Browser({
			rootConfig:{
				storages:true
			}
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
		
		this.entityStore.on('changes',this.onChanges, this);
	},
	
	load: function (id) {
		this.currentId = id;

		var entities = this.entityStore.get([id]);
		if(entities) {
			this.setRootNode(entities[0]);
		} else {
			//If no entity was returned the entity store will load it and fire the "changes" event. This dialog listens to that event.
		}
		
		return this;
	},
	
	onChanges : function(entityStore, added, changed, destroyed) {

		if(changed.concat(added).indexOf(this.currentId) !== -1){
			var entities = entityStore.get([this.currentId]);
			this.setRootNode(entities[0]);
		}
	},
	
	setRootNode : function(rootNodeEntity){
		this.folderTree.initRootNode(rootNodeEntity);
		this.browser.addRootNodeEntity(rootNodeEntity,true);
		this.browser.goto([rootNodeEntity.id]);
	}
});
