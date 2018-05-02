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
go.modules.community.files.CardPanel = Ext.extend(Ext.Panel, {
	layout:'card',
	activeItem: 0,
	deferredRender: false,
	border: false,
	browser: null, // set from mainpanel
	previewTypes: {
      'image/png': true,
      'image/jpeg': true,
      'image/gif': true
   },
		
	initComponent: function () {

		var contextMenu = new go.modules.community.files.ContextMenu();
		
		this.nodeGrid = new go.modules.community.files.NodeGrid({
			store:this.browser.store,
			listeners: {
				viewready: function (grid) {
					this.nodeGrid.getStore().load();
				},
				rowcontextmenu: function(grid, index, event){
					event.stopEvent();
					var records = grid.getSelectionModel().getSelections();
					contextMenu.showAt(event.xy, records);
				},
				rowdblclick: function (grid, rowIndex, e) {
					var record = grid.getStore().getAt(rowIndex);
					if(record.data.isDirectory) {
						this.browser.descent(record.id);
						return;
					}
					if (record.get('permissionLevel') < GO.permissionLevels.write) {
						return;
					}

					var fileRename = new go.modules.files.FileForm();
					fileRename.load(record.id).show();
				},
				scope: this
			}
		});
		
//		this.nodeGrid.getSelectionModel().on('rowselect', function (sm, rowIndex, record) {
//			go.Router.goto("files/mine/" + this.browser.path.join('/')+ record.id);
//		},this);
		
		this.nodeTile = new go.modules.community.files.NodeTile({
			store:this.browser.store,
			listeners: {
				contextmenu: function(view, index, node, event){
					event.stopEvent();
					contextMenu.showAt(event.xy, view.getSelectedRecords());
				},
				dblclick(view, index, node, e) {
					var record = view.getStore().getAt(index);
					if(record.data.isDirectory) {
						this.browser.descent(record.id);
					}
					
				},
				scope:this
			}
		});
		
		this.items = [this.nodeGrid, this.nodeTile];
	
		go.modules.community.files.CardPanel.superclass.initComponent.call(this, arguments);

	},
	
	afterRender : function(view) {
		var childCount = 0;
		this.body.dom.addEventListener('dragenter', function(e) {
			e.preventDefault();
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
			this.fileUpload(e.dataTransfer.files);
		}.bind(this));
		
		go.modules.community.files.CardPanel.superclass.afterRender.call(this, arguments);
	},
	activeUploads: 0,
	fileUpload: function(files) {

		for (var i = 0; i < files.length; i++) {
			var name = this.solveDuplicate(files[i].name);
			var record = new this.store.recordType({ name: name, size: files[i].size, status: 'queued' });
			this.store.add(record);
			this.activeUploads++;
			//var progress = this.appendProgressFile(files[i]);
			go.Jmap.upload(files[i], {
			  progress: function(e) {
				  console.log(e);
					if (e.lengthComputable) {
						var complete = (e.loaded / e.total * 100 | 0);
						record.set('progress', complete);
					}
			  },
			  success: function(data) {
				  this.activeUploads--;
				  record.set('status', 'done');
				  record.set('blobId', data.blobId);
				  record.set('progress', 100);
				  console.log(record);
				  if(this.activeUploads === 0) {
					  this.store.commitChanges();
				  }
			  },
			  failure: function(e) {
				  record.set('progress', 0);
				  record.set('status', 'failed');
			  },
			  scope:this
		  });
		}
		
	},
	
	solveDuplicate: function(name, action) {
		var index = this.store.find('name', name);
		if(index === -1) {
			return name;
		}
		switch(action) {
			case 'replace':
				this.store.removeAt(index);
				return name;
			case 'cancel':
				return false;
			case 'addnumber':
			default:
				var newName, nameCount = 0;
				while(index) {
					nameCount++;
					newName = name + '('+number+')';
					index = this.store.find('name', newName);
				}
				return newName;
		}
	},
	
	appendProgressFile: function(file){
		var holder = document.createElement('div');
		if (this.previewTypes[file.type] === true) {
			var reader = new FileReader();
			reader.onload = function (event) {
			  var image = new Image();
			  image.src = event.target.result;
			  image.width = 160;
			  holder.appendChild(image);
			};

			reader.readAsDataURL(file);
		}  else {
			holder.innerHTML += '<p>Uploaded ' + file.name + ' ' + (file.size ? (file.size/1024|0) + 'K' : '');
			console.log(file);
		}
		var progress = document.createElement('progress');
		holder.appendChild(progress);
		this.getLayout().activeItem.dom.appendChild(holder);
		return progress;
	}
});