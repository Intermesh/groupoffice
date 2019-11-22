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
</a></tpl></div>', {
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
			this.browseBtn.setText(t("Create folder"));
		}
	}

});

