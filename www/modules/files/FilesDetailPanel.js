Ext.namespace('go.modules.files');

go.modules.files.FilesDetailPanel = Ext.extend(Ext.Panel, {
	title: t("Files", "files") + "<span class='badge'>0</span>",
	collapsible: true,
	titleCollapse: true,
	stateId: "files-detail",
	initComponent: function () {

		this.store = new GO.data.JsonStore({
			url: GO.url('files/folder/list'),
			suppressError: true,
			fields: ['id', 'name', 'mtime', 'extension', "handler"],
			remoteSort: true
		});
		
		this.store.on("load", function(store,rs, success) {

			var count = this.store.getTotalCount();
			var badge = "<span class='badge'>" + count + '</span>';
			this.setTitle(t("Files", "files") + badge);
			if(count) {
				this.browseBtn.setText(t("Browse {total} files", "files").replace("{total}", count));
			} else
			{
				this.browseBtn.setText(t("Browse files", "files"));
				this.setTitle(t("Files", "files"));
			}
		}, this);
		this.store.on('exception',function(store, type, action, options, response) {
			var data = Ext.decode(response.responseText);
			if(data && data.feedback) {
				this.items.get(0).getTemplateTarget().update('<div class="pad danger">'+data.feedback+'</div>');
				this.browseBtn.setText(t("Create folder", "files"));
			}
		},this);



		var tpl = new Ext.XTemplate('<div class="icons"><tpl for="."><a>\
			<i class="icon label filetype filetype-{extension}"></i>\
			<span>{name}</span>\
			<label>{user_name} at {mtime}</label>\
		</a></tpl></div>');

		this.items = [new Ext.DataView({
			store: this.store,
			tpl: tpl,
			autoHeight: true,
			multiSelect: true,
			emptyText: '<div class="fs-dropzone">'+t('Drop files here')+'</div>',
			itemSelector: 'a',
			listeners: {
				afterrender:function(me) {
					GO.files.DnDFileUpload(function (blobs) {
						var fb = GO.mainLayout.getModulePanel('files'),
							options = {
								upload: true,
								destination_folder_id: this.folderId,
								blobs: Ext.encode(blobs),
								cb: function() {
									this.store.load({
										params: {
											limit: 10,
											folder_id: this.folderId
										}
									});
								}.bind(this)
							};
						if(this.folderId) {
							fb.sendOverwrite(options);
						} else { // create folder first
							this.createFolderWhenNoneExist(function() {
								options.destination_folder_id = this.folderId;
								fb.sendOverwrite(options);
							}.bind(this))
						}

					}.bind(this), me.container)();

				},
				click: this.onClick,
				scope: this
			}
		})];
		
		this.bbar = [
			this.browseBtn = new GO.files.DetailFileBrowserButton({iconCls: ""})
		];
		
		this.browseBtn.on('closefilebrowser', function(btn, folderId) {
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


	createFolderWhenNoneExist: function(cb) {
		var dv = this.findParentByType("detailview"), entityId, entity;
		if(!dv) {
			dv = this.findParentByType("displaypanel") || this.findParentByType("tmpdetailview"); //for legacy modules
		}
		GO.request({
			url: 'files/folder/checkModelFolder',
			maskEl: dv.getEl(),
			jsonData: {},
			params: {
				mustExist: true,
				model: dv.model_name || dv.entity || dv.entityStore.entity.name,
				id: dv.data.id
			},
			success: function (response, options, result) {
				this.folderId = result.files_folder_id;
				cb();
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

		// this.browseBtn.model_name = dv.model_name || dv.entity || dv.entityStore.entity.name;
		// this.browseBtn.setId(dv.data.id);

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
		} else {
			this.store.removeAll();
			this.browseBtn.setText(t("Create folder", "files"));
		}
	}

});

