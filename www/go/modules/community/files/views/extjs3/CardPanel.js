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
	previewTypes: {
      'image/png': true,
      'image/jpeg': true,
      'image/gif': true
   },
		
	initComponent: function () {
		
		this.store = new go.data.Store({
			fields: ['id', 'name', 'byteSize', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'permissionLevel'],
			baseParams: {filter:{parentId:1}},
			entityStore: go.Stores.get("Node")
		});
		
		this.nodeGrid = new go.modules.community.files.NodeGrid({
			store:this.store,
			listeners: {
				viewready: function (grid) {
					this.nodeGrid.getStore().load();
//					this.folderTree.getStore().load({
//						callback: function (store) {
//							this.folderTree.getSelectionModel().selectRow(0);
//						},
//						scope: this
//					});
				},
				rowdblclick: function (grid, rowIndex, e) {

					var record = grid.getStore().getAt(rowIndex);
					if (record.get('permissionLevel') < GO.permissionLevels.write) {
						return;
					}

					var fileRename = new go.modules.files.FileForm();
					fileRename.load(record.id).show();
				},
				scope: this
			}
		});
		
		this.nodeGrid.getSelectionModel().on('rowselect', function (sm, rowIndex, record) {
			go.Router.goto("node/" + record.id);
		});
		
		this.nodeTile = new go.modules.community.files.NodeTile({store:this.store});
		
		this.items = [this.nodeGrid, this.nodeTile];
	
		go.modules.community.files.CardPanel.superclass.initComponent.call(this, arguments);

	},
	
	afterRender : function(view) {

		view.dom.addEventListener('dragenter', function() {
			this.body.addClass('x-dd-over');
			return false;
		}.bind(this));
		
		view.dom.addEventListener('dragleave', function() {
			this.body.removeClass('x-dd-over');
			return false;
		}.bind(this));
		
		view.dom.addEventListener('drop', function() {
			e.preventDefault();
			this.body.removeClass('x-dd-over');
			this.fileUpload(e.dataTransfer.files);
		}.bind(this));
		
		go.modules.community.files.CardPanel.superclass.afterRender.call(this, arguments);
	},
	
	fileUpload: function(files) {
		debugger;
		var formData = tests.formdata ? new FormData() : null;
		for (var i = 0; i < files.length; i++) {
		  if (tests.formdata) formData.append('file', files[i]);
		  this.appendProgressFile(files[i]);
		}

		// now post a new XHR request
		if (tests.formdata) {
			var xhr = new XMLHttpRequest();
			xhr.open('POST', '/upload.php');
			xhr.onload = function() {
			  progress.value = progress.innerHTML = 100;
			};

			if (tests.progress) {
			  xhr.upload.onprogress = function (event) {
				 if (event.lengthComputable) {
					var complete = (event.loaded / event.total * 100 | 0);
					progress.value = progress.innerHTML = complete;
				 }
			  }
			}

		  xhr.send(formData);
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
		this.getLayout().activeItem.dom.appendChild(holder);
	}
});