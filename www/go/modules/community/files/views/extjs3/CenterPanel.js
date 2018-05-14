/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
go.modules.community.files.CenterPanel = Ext.extend(Ext.Panel, {
	layout:'card',
	activeItem: 0,
	deferredRender: false,
	border: false,
	browser: null, // set from mainpanel
	detailView: null, // from mainpanel (for click in centerpanel)
	previewTypes: {
      'image/png': true,
      'image/jpeg': true,
      'image/gif': true
   },
	
	activeUploads: 0,
	pendingDuplicates: {},
		
	initComponent: function () {

		var contextMenu = new go.modules.community.files.ContextMenu({
			store: this.browser.store
		});
		
		this.nodeGrid = new go.modules.community.files.NodeGrid({
			store:this.browser.store,
			listeners: {
				viewready: function (grid) {
					this.nodeGrid.getStore().load();
				},
				rowcontextmenu: function(grid, index, event){
					var selections = grid.getSelectionModel().getSelections();
					var records = [];
					for(var i=0; i < selections.length; i++){
						records.push(selections[i].json);
					};
					
					contextMenu.showAt(event.xy, records);
				},
				rowdblclick: function (grid, rowIndex, e) {
					var record = grid.getStore().getAt(rowIndex);
					if (record.data.isDirectory) {
						this.browser.descent(record.id);
						return;
					} else {
						go.Preview(record.json);
					}
				},
				scope:this
			},
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: false,
				listeners: {
					rowselect: function(sm,rowIndex,record){
						if(this.detailView){
							this.detailView.load(parseInt(record.id));
						}
					},
					scope: this
				}
			})
		});
		
//		this.nodeGrid.getSelectionModel().on('rowselect', function (sm, rowIndex, record) {
//			go.Router.goto("files/mine/" + this.browser.path.join('/')+ record.id);
//		},this);
		
		this.nodeTile = new go.modules.community.files.NodeTile({
			store:this.browser.store,
			listeners: {
				click: function(view, index, node, e) {
					var record = view.getStore().getAt(index);
					console.log(record);
					this.detailView.load(parseInt(record.id));
				},
				contextmenu: function(view, index, node, event){
					event.stopEvent();
					var selections = view.getSelectedRecords();
					if(Ext.isEmpty(selections)) {
						view.select(index);
						selections = view.getSelectedRecords();
					}
					var records = [];
					for(var i=0; i < selections.length; i++){
						records.push(selections[i].json);
					};
					contextMenu.showAt(event.xy, records);
				},
				dblclick(view, index, node, e) {
					var record = view.getStore().getAt(index);
					if(record.data.isDirectory) {
						this.browser.descent(record.id);
					} else {
						go.Preview(record.json);
					}
					
				},
				scope:this
			}
		});
		
		this.items = [this.nodeGrid, this.nodeTile];
	
		go.modules.community.files.CenterPanel.superclass.initComponent.call(this, arguments);

	},
	
	afterRender : function(view) {
		var childCount = 0;
		this.body.dom.addEventListener('dragenter', function(e) {
			e.preventDefault();
			e.stopPropagation();
			childCount++;
			this.body.addClass('x-dd-over');
		}.bind(this));
		
		this.body.dom.addEventListener('dragleave', function(e) {
			e.preventDefault();
			childCount--;
			if (childCount === 0) {
				this.body.removeClass('x-dd-over');
			}
		}.bind(this));
		
		this.body.dom.addEventListener('dragover', function(e) {
			e.preventDefault(); // THIS IS NEEDED
			e.stopPropagation();
		});
		
		this.body.dom.addEventListener('drop', function(e) {
			e.stopPropagation();
			e.preventDefault();
			this.body.removeClass('x-dd-over');
			this.addFiles(e.dataTransfer.files);
		}.bind(this));
		
		go.modules.community.files.CenterPanel.superclass.afterRender.call(this, arguments);
	},
	
	addFiles: function(files) {
		for (var i = 0; i < files.length; i++) {
			var index = this.browser.store.find('name', files[i].name);
			if(index === -1) { // not found
				this.fileUpload(files[i]);
			} else { // already exist
				this.solveDuplicate(files[i], index);
			}
		}
	},
	
	fileUpload: function(file, replaceIndex, newName) {
		if(replaceIndex || replaceIndex === 0) {
			var record = this.browser.store.getAt(replaceIndex);
			record.set('status', 'queued');
		} else {
			var record = new this.browser.store.recordType({
				name: newName || file.name, 
				isDirectory: 0,
				parentId: this.browser.store.baseParams.filter.parentId, 
				size: file.size, 
				status: 'queued' 
			});
			this.browser.store.add(record);
		}
		this.activeUploads++;
		go.Jmap.upload(file, {
		  progress: function(e) {
				if (e.lengthComputable) {
					var complete = (e.loaded / e.total * 100 | 0);
					record.set('progress', complete);
				}
		  },
		  success: function(data) {
			  this.activeUploads--;
			  record.set('status', 'done');
			  record.set('blobId', data.blobId);
			  if(this.activeUploads === 0) {
				  this.browser.store.commitChanges();
			  }
		  },
		  failure: function(e) {
			  record.set('progress', 0);
			  record.set('status', 'failed');
		  },
		  scope:this
	  });
	},
	
	solveDuplicate: function(file, index) {
		this.pendingDuplicates[index] = file;
		var count = Object.keys(this.pendingDuplicates).length,
			msg = (count < 2) ? 'A file named <b>'+file.name+ '</b>' : '<b>'+count + '</b> files';
		Ext.Msg.show({
			title: t('Duplicate file(s)'),
			msg: t(msg+' already exists. <br>What would you like to do?'),
			buttons: {yes:t('Keep both'), no:t('Replace'), cancel:t('Cancel')},
			icon: Ext.MessageBox.QUESTION,
			fn: function(btnId, text) {
				for (var i in this.pendingDuplicates) {
					if(btnId === 'no') {
						this.fileUpload(this.pendingDuplicates[i], i);
						continue;
					} else if(btnId === 'yes') {
						var newName, nameCount = 0, index = i,
							nameExt = this.pendingDuplicates[i].name.split('.'),
							name, extension = nameExt.pop();
							if(nameExt.length === 0) {
								name = extension;
								extionsion = null;
							} else {
								name = nameExt.join('.');
							}
						while(index !== -1) {
							nameCount++;
							newName = name + '('+nameCount+')';
							index = this.browser.store.find('name', newName);
						}
						if(extension !== null) {
							newName += ('.'+extension);
						}
						console.log(newName,this.pendingDuplicates[i]);
						this.fileUpload(this.pendingDuplicates[i], false, newName);
					}
				}
				this.pendingDuplicates = {};
			},
			scope:this
		});
		
	}
});
