go.Modules.register("community", "syncfusion", {

	title: t("Syncfusion", "syncfusion", "community"),

	systemSettingsPanels: [
		"go.modules.community.syncfusion.SystemSettingsPanel"
	],

	initModule: function () {

		var documentExtensions = ['docx', 'doc', 'dotx', 'rtf', 'txt'];
		var spreadsheetExtensions = ['xlsx', 'xls', 'csv'];
		var allExtensions = documentExtensions.concat(spreadsheetExtensions);

		/**
		 * Open the Syncfusion editor for a file from the Files module
		 *
		 * @param {Number} fileId
		 * @param {String} fileName
		 * @param {String} extension
		 * @param {Number} folderId
		 */
		go.modules.community.syncfusion.openFile = function (fileId, fileName, extension, folderId) {
			var type = 'document';
			if (spreadsheetExtensions.indexOf(extension.toLowerCase()) !== -1) {
				type = 'spreadsheet';
			}

			var win = new go.modules.community.syncfusion.EditorWindow({
				editorType: type,
				fileId: fileId,
				fileName: fileName,
				folderId: folderId
			});
			win.show();
		};

		/**
		 * Open the Syncfusion editor for a blob
		 *
		 * @param {String} blobId
		 * @param {String} fileName
		 * @param {Object} [listeners] Optional listeners (e.g. {save: function(win, blobId){}})
		 */
		go.modules.community.syncfusion.openBlob = function (blobId, fileName, listeners) {
			var ext = (fileName.split('.').pop() || '').toLowerCase();
			var type = 'document';
			if (spreadsheetExtensions.indexOf(ext) !== -1) {
				type = 'spreadsheet';
			}

			var win = new go.modules.community.syncfusion.EditorWindow({
				editorType: type,
				blobId: blobId,
				fileName: fileName,
				listeners: listeners || {}
			});
			win.show();
		};

		// Hook into Files module context menu
		GO.mainLayout.on('authenticated', function () {
			if (!GO.files || !GO.files.FilesContextMenu) {
				return;
			}

			var origShowAt = GO.files.FilesContextMenu.prototype.showAt;

			GO.files.FilesContextMenu.prototype.initSyncfusionButton = function () {
				if (this.syncfusionButton) {
					return;
				}

				this.syncfusionSeparator = new Ext.menu.Separator();
				this.syncfusionButton = new Ext.menu.Item({
					iconCls: 'ic-edit',
					text: t("Edit with Syncfusion", "syncfusion", "community"),
					handler: function () {
						var record = this.records[0];
						go.modules.community.syncfusion.openFile(
							record.data.id,
							record.data.name,
							record.data.extension,
							record.data.folder_id
						);
					},
					scope: this
				});

				var idx = this.items.indexOf(this.cutSeparator);
				if (idx !== -1) {
					this.insert(idx, this.syncfusionButton);
					this.insert(idx, this.syncfusionSeparator);
				} else {
					this.add(this.syncfusionSeparator);
					this.add(this.syncfusionButton);
				}
			};

			GO.files.FilesContextMenu.prototype.showAt = function (xy, records, clickedAt, forFileSearchModule) {
				this.initSyncfusionButton();

				var showButton = false;
				if (records && records.length === 1) {
					var ext = (records[0].data.extension || '').toLowerCase();
					showButton = allExtensions.indexOf(ext) !== -1;
				}

				this.syncfusionSeparator.setVisible(showButton);
				this.syncfusionButton.setVisible(showButton);

				origShowAt.apply(this, arguments);
			};
		});
	}
});
