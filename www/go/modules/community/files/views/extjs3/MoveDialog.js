go.modules.community.files.MoveDialog = Ext.extend(go.Window, {
	stateId: 'files-moveDialog',
	title: t("Move"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	copy: false,
	currentIds: [], // ids of Nodes to move
	initComponent: function () {	
		
		this.items = this.initFormItems();
		go.modules.community.files.MoveDialog.superclass.initComponent.call(this);
	},
	initFormItems: function () {
		var items = [];
		
		this.buttons = ['->', this.submitButton = new Ext.Button({
				text: t("Move"), // or copy?
				handler: this.submit,
				scope: this
			})];
		
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
		self = this;
		go.Stores.get('Node').get(this.currentIds, function(nodes) {
			this.browser.receive(nodes, this.parentIdField.getValue(), this.copy?'copy':'move', function(){
				self.close();
			});
		}, this);
			
		
	},
	
	copy: function(copy) {
		this.copy = copy;
		return this;
	},
	
	load: function (records) {
		var ids = [];
		for(var i = 0,record; record = records[i];i++) {
			ids.push(record.id);
		}
		this.currentIds = ids;
		
		if(this.copy) { 
			if(ids.length === 1) {
				this.setTitle(t("Copy")+ " " +records[0].name);
			} else {
				this.setTitle(t("Copy")+ " " +records.length + ' ' + t('files'));
			}
			this.submitButton.setText(t("Copy"));
		} else {
			if(this.records.length === 1) {
				this.setTitle(t("Move")+ " " +this.records[0].name);
			} else {
				this.setTitle(t("Move")+ " " +this.records.length + ' ' + t('files'));
			}
			this.submitButton.setText(t("Move"));
		}
		return this;
	}
});