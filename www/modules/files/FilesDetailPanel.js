Ext.namespace('go.modules.files');

go.modules.files.FilesDetailPanel = Ext.extend(Ext.Panel, {
	title: t("Files", "files"),
	collapsible: true,
	titleCollapse: true,
	stateId: "files-detail",
	initComponent: function () {



		this.store = new GO.data.JsonStore({
			url: GO.url('files/folder/list'),
			fields: ['id', 'name', 'mtime', 'extension', "handler"],
			remoteSort: true
		});
		
		
		this.store.on("load", function() {
			var count = this.store.getTotalCount();
			if(count) {
				this.browseBtn.setText(t("Browse {total} files").replace("{total}", count));
			} else
			{
				this.browseBtn.setText(t("Browse files"));
			}
		}, this);


		var tpl = new Ext.XTemplate('<div class="icons"><tpl for=".">\
<a>\
<i class="icon label filetype filetype-{extension}"></i>\
<span>{name}</span>\
\<label>{user_name} at {mtime}</label>\
\
</a><hr class="indent"></tpl></div>', {
		});


		this.items = [new Ext.DataView({
				store: this.store,
				tpl: tpl,
				autoHeight: true,
				multiSelect: true,
				itemSelector: 'a',
				listeners: {
					click: this.onClick,
					scope: this
				}
			})];

		this.addButtonItems = [{
				iconCls: 'ic-folder',
				text: t("Files"),
				handler: this.browse,
				scope: this
			}];
		
		
		this.bbar = [
			this.browseBtn = new GO.files.FileBrowserButton()
		];
		
		this.browseBtn.on('close', function(btn, folderId) {
			this.folderId = folderId;
			this.store.load({
				params: {
					limit: 10,
					folder_id: this.folderId
				}
			});
		}, this);


		go.modules.files.FilesDetailPanel.superclass.initComponent.call(this);

	},

	browse: function () {


		GO.request({
			url: 'files/folder/checkModelFolder',
			maskEl: this.ownerCt.ownerCt.getEl(),
			jsonData: {},
			params: {
				mustExist: true,
				model: this.detailView.model_name || this.detailView.entity || this.detailView.entityStore.entity.name,
				id: this.detailView.data.id
			},
			success: function (response, options, result) {
				var fb = GO.files.openFolder(result.files_folder_id);
				fb.model_name = this.detailView.model_name || this.detailView.entity || this.detailView.entityStore.entity.name;
				fb.model_id = this.detailView.data.id;

				this.folderId = result.files_folder_id;
				
				//hack to update entity store
				var store = go.Stores.get(fb.model_name);
				if (store) {
					store.data[fb.model_id].filesFolderId = result.files_folder_id;
					store.saveState();
				}

				fb.on('hide', function () {
					fb.model_id = null;
					fb.model = null;

				}, {single: true});

				//reload display panel on close

				GO.files.fileBrowserWin.on('hide', function () {
					//this.fireEvent('close', this, result.files_folder_id);
					
					this.store.load({
						params: {
							limit: 10,
							folder_id: this.folderId
						}
					});
				}, this, {single: true});
			},
			scope: this

		});


	},

	onClick: function (dataview, index, node, e) {

		var record = this.store.getAt(index);

		if (record.data.extension == 'folder')
		{
			GO.files.openFolder(this.folderId, record.id);
		} else
		{
			if (go.Modules.isAvailable("legacy", "files")) {
				//GO.files.openFile({id:file.id});
				record.data.handler.call(this);
			} else
			{
				window.open(GO.url("files/file/download", {id: record.data.id}));
			}
		}
	},

	onLoad: function (dv) {

		this.browseBtn.model_name = dv.model_name || dv.entity || dv.entityStore.entity.name;
		this.browseBtn.setId(dv.data.id);
		

		this.detailView = dv;

		this.folderId = dv.data.files_folder_id == undefined ? dv.data.filesFolderId : dv.data.files_folder_id;

		//this.setVisible(this.folderId != undefined);

		if (this.folderId) {
			this.store.load({
				params: {
					limit: 10,
					folder_id: this.folderId
				}
			});
		} else
		{
			this.store.removeAll();
		}
	}

});

