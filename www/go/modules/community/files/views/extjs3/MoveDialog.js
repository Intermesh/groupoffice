go.modules.community.files.MoveDialog = Ext.extend(go.Window, {
	stateId: 'files-moveDialog',
	title: t("Move"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	currentId: null,
	initComponent: function () {	
		
		this.items = this.initFormItems();
		go.modules.community.files.MoveDialog.superclass.initComponent.call(this);
	},
	initFormItems: function () {
		var items = [];
		
		this.buttons = ['->', {
				text: this.title,
				handler: this.submit,
				scope: this
			}];
		
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
		var nodes = go.Stores.get('Node').get([this.currentId]),
			self = this;
		//this.browser.receive(records, droppedAt.data.id, 'move');
		this.browser.receive(nodes, this.parentIdField.getValue(), this.copy?'copy':'move', function(){
			self.close();
		});
	},
	
	load: function (id) {
		this.currentId = id;

		return this;
	}
});