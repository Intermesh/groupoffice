go.modules.community.syncfusion.EditorWindow = Ext.extend(go.Window, {

	title: t("Syncfusion", "syncfusion", "community"),
	layout: 'fit',
	width: dp(1000),
	height: dp(800),
	maximizable: true,
	resizable: true,
	modal: false,
	closeAction: 'close',

	/**
	 * @cfg {String} editorType 'document' or 'spreadsheet'
	 */
	editorType: 'document',

	/**
	 * @cfg {Number} fileId Legacy Files module file ID (optional)
	 */
	fileId: null,

	/**
	 * @cfg {String} blobId JMAP blob ID (optional)
	 */
	blobId: null,

	/**
	 * @cfg {String} fileName Name of the file being edited
	 */
	fileName: '',

	initComponent: function () {

		if (this.editorType === 'spreadsheet') {
			this.sfContainerId = Ext.id();
			this.editorPanel = new Ext.Panel({
				border: false,
				html: '<div id="' + this.sfContainerId + '"></div>',
				listeners: {
					bodyresize: this.onEditorPanelResize,
					scope: this
				}
			});
		} else {
			this.editorPanel = new Ext.Panel({
				border: false,
				listeners: {
					bodyresize: this.onEditorPanelResize,
					scope: this
				}
			});
		}

		// Map GO language to Syncfusion locale
		var lang = GO.lang.iso || 'en';
		this.sfLocale = lang === 'en' ? 'en-US' : lang;

		Ext.apply(this, {
			title: this.fileName
				? this.fileName + ' - ' + t("Syncfusion", "syncfusion", "community")
				: t("Syncfusion", "syncfusion", "community"),
			items: [this.editorPanel]
		});

		go.modules.community.syncfusion.EditorWindow.superclass.initComponent.call(this);

		this.on('afterrender', this.onAfterRender, this, {single: true});
		this.on('beforeclose', this.onBeforeClose, this);
	},

	onAfterRender: function () {
		// Mask right away — loading the Syncfusion bundle (iframe/CDN) is
		// usually the longest phase, well before the file conversion starts.
		this.getEl().mask(t("Loading..."));

		if (this.editorType === 'spreadsheet') {
			this.initSpreadsheetMode();
		} else {
			this.createIframe();
		}
	},

	// =========================================================================
	// Spreadsheet: direct rendering (no iframe)
	// =========================================================================

	initSpreadsheetMode: function () {
		var me = this;
		go.modules.community.syncfusion.loadLibrary('spreadsheet', function (error) {
			if (error) {
				me.getEl().unmask();
				Ext.MessageBox.alert(t("Error"), error.message);
				me.close();
				return;
			}
			me.iframeEj = window.ej;
			me.loadLocale(function () {
				me.convertAndOpen();
			});
		});
	},

	// =========================================================================
	// Document: iframe for style isolation
	// =========================================================================

	/**
	 * Get CDN base URL with trailing slash
	 */
	getCdnBase: function () {
		var settings = go.Modules.get('community', 'syncfusion').settings;
		return (settings.cdnUrl || 'https://cdn.syncfusion.com/ej2/32.1.19/').replace(/\/?$/, '/');
	},

	/**
	 * Extract version number from CDN URL for locale loading via jsdelivr
	 */
	getCdnVersion: function () {
		var base = this.getCdnBase();
		var match = base.match(/\/ej2\/([^\/]+)\//);
		return match ? match[1] : '32.1.19';
	},

	/**
	 * Create an iframe for style isolation and load Syncfusion inside it
	 */
	createIframe: function () {
		var panelBody = this.editorPanel.body;

		this.iframe = document.createElement('iframe');
		this.iframe.style.width = '100%';
		this.iframe.style.height = '100%';
		this.iframe.style.border = 'none';
		this.iframe.setAttribute('frameborder', '0');

		panelBody.dom.appendChild(this.iframe);

		var settings = go.Modules.get('community', 'syncfusion').settings;
		var cssUrl, jsUrl;

		if (settings.librarySource === 'local') {
			var localBase = BaseHref + 'go/modules/community/syncfusion/lib/';
			cssUrl = localBase + 'ej2.min.css';
			jsUrl = localBase + 'ej2.min.js';
		} else {
			var cdnBase = this.getCdnBase();
			cssUrl = cdnBase + 'material.css';
			jsUrl = cdnBase + 'dist/ej2.min.js';
		}

		var doc = this.iframe.contentDocument;
		doc.open();
		doc.write(
			'<!DOCTYPE html>' +
			'<html><head>' +
			'<link rel="stylesheet" href="' + cssUrl + '">' +
			'<style>' +
			'html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }' +
			'#sf-editor { height: 100%; width: 100%; }' +
			'.e-de-background { background-color: #eee; }' +
			'</style>' +
			'</head><body>' +
			'<div id="sf-editor"></div>' +
			'<script src="' + jsUrl + '"><\/script>' +
			'</body></html>'
		);
		doc.close();

		// Wait for iframe to fully load
		this.iframe.onload = this.onIframeReady.bind(this);
	},

	onIframeReady: function () {
		var iframeWindow = this.iframe.contentWindow;

		// The iframe fires load even when the library script inside it
		// failed (e.g. CDN unreachable) — don't continue without it.
		if (!iframeWindow.ej) {
			this.getEl().unmask();
			Ext.MessageBox.alert(t("Error"), "Failed to load the Syncfusion library.");
			this.close();
			return;
		}

		// Register license key inside the iframe context
		var settings = go.Modules.get('community', 'syncfusion').settings;
		if (settings.licenseKey && iframeWindow.ej.base) {
			iframeWindow.ej.base.registerLicense(settings.licenseKey);
		}

		this.iframeEj = iframeWindow.ej;

		// Load locale then proceed
		this.loadLocale(function () {
			this.convertAndOpen();
		}.bind(this));
	},

	// =========================================================================
	// Shared: locale, convert, init
	// =========================================================================

	/**
	 * Load Syncfusion locale JSON for the user's language.
	 * Uses jsdelivr CDN which serves @syncfusion/ej2-locale package.
	 */
	loadLocale: function (callback) {
		if (this.sfLocale === 'en-US') {
			callback();
			return;
		}

		var version = this.getCdnVersion();
		var localeUrl = 'https://cdn.jsdelivr.net/npm/@syncfusion/ej2-locale@' + version + '/src/' + this.sfLocale + '.json';

		fetch(localeUrl)
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Locale not found');
				}
				return response.json();
			})
			.then(function (data) {
				if (this.iframeEj && this.iframeEj.base && this.iframeEj.base.L10n) {
					this.iframeEj.base.L10n.load(data);
				}
				callback();
			}.bind(this))
			.catch(function () {
				// Locale not available for this language, continue without it
				callback();
			});
	},

	/**
	 * Call the backend JMAP controller to convert the file, then open in the editor
	 */
	convertAndOpen: function () {
		var method, params = {};

		if (this.fileId) {
			params.fileId = this.fileId;
		} else if (this.blobId) {
			params.blobId = this.blobId;
		} else {
			this.getEl().unmask();
			Ext.MessageBox.alert(t("Error"), "No file or blob specified.");
			return;
		}

		if (this.editorType === 'document') {
			method = 'community/syncfusion/Editor/openDocument';
		} else {
			method = 'community/syncfusion/Editor/openSpreadsheet';
		}

		go.Jmap.request({
			method: method,
			params: params,
			callback: function (options, success, result) {
				if (!result || !result.success) {
					this.getEl().unmask();
					var msg = (result && result.error) || 'Unknown error';
					Ext.MessageBox.alert(t("Error"), msg);
					return;
				}

				if (result.fileName) {
					this.fileName = result.fileName;
					this.setTitle(this.fileName + ' - ' + t("Syncfusion", "syncfusion", "community"));
				}

				this.canEdit = result.canEdit !== false;
				this.initEditor(result);

				// unmask only after the editor is constructed — the mask has
				// been up since afterrender, covering library load + convert
				this.getEl().unmask();
			},
			scope: this
		});
	},

	initEditor: function (result) {
		var settings = go.Modules.get('community', 'syncfusion').settings;

		if (this.editorType === 'document') {
			this.initDocumentEditor(settings, result);
		} else {
			this.initSpreadsheetEditor(settings, result);
		}
	},

	initDocumentEditor: function (settings, result) {
		var me = this;
		var ej = this.iframeEj;
		var doc = this.iframe.contentDocument;

		// Inject Ribbon module for Office-style tabbed toolbar
		ej.documenteditor.DocumentEditorContainer.Inject(ej.documenteditor.Ribbon);

		this.sfEditor = new ej.documenteditor.DocumentEditorContainer({
			enableToolbar: true,
			toolbarMode: 'Ribbon',
			ribbonLayout: 'Simplified',
			height: '100%',
			locale: this.sfLocale,
			showPropertiesPane: true,
			enableLocalPaste: false,
			restrictEditing: !this.canEdit,
			// No 'New': replacing the loaded document with a blank one and
			// hitting Save would overwrite the original file.
			fileMenuItems: [
				{text: t("Save"), id: 'go_save', iconCss: 'e-icons e-save'},
				'Print'
			],
			fileMenuItemClick: function (args) {
				if (args.item.id === 'go_save') {
					me.onSave();
				}
			},
			created: function () {
				me.sfEditor.documentEditor.pageOutline = '#d1d1d1';
			},
			contentChange: function () {
				me._dirty = true;
			}
		});

		this.sfEditor.appendTo(doc.getElementById('sf-editor'));

		this.sfEditor.documentEditor.open(JSON.stringify(result.sfdt));

		// open() itself triggers contentChange — reset once loading settles
		setTimeout(function () {
			me._dirty = false;
		}, 300);

		// Ctrl+S / Cmd+S to save back to Group-Office
		doc.addEventListener('keydown', function (e) {
			if ((e.ctrlKey || e.metaKey) && e.key === 's') {
				e.preventDefault();
				me.onSave();
			}
		});
	},

	initSpreadsheetEditor: function (settings, result) {
		var me = this;
		var ej = this.iframeEj;
		var serviceUrl = settings.spreadsheetServiceUrl
			? settings.spreadsheetServiceUrl.replace(/\/?$/, '/')
			: '';

		this._saveButtonAdded = false;

		this._spreadsheetData = result.data;

		this.sfEditor = new ej.spreadsheet.Spreadsheet({
			locale: this.sfLocale,
			allowOpen: true,
			allowSave: true,
			openUrl: serviceUrl + 'Open',
			saveUrl: serviceUrl + 'Save',
			isProtected: !this.canEdit,
			created: function () {
				me.sfEditor.isRendered = true;

				if (me._spreadsheetData) {
					var data = typeof me._spreadsheetData === 'object' ? me._spreadsheetData : JSON.parse(me._spreadsheetData);
					me.sfEditor.openFromJson({file: data});
					delete me._spreadsheetData;

					// openFromJson is async internally but returns no promise;
					// force a layout recalc by briefly changing container size
					setTimeout(function () {
						var el = document.getElementById(me.sfContainerId);
						if (el) {
							el.style.width = (el.offsetWidth - 1) + 'px';
							setTimeout(function () {
								el.style.width = '';
								me.sfEditor.resize();
							}, 50);
						}
					}, 200);
				} else {
					me.onEditorPanelResize();
				}
			},
			openComplete: function () {
				me._dirty = false;
				me.onEditorPanelResize();
			},
			actionComplete: function () {
				me._dirty = true;
			},
			fileMenuBeforeOpen: function () {
				if (!me._saveButtonAdded) {
					me._saveButtonAdded = true;
					var refId = me.sfContainerId + '_Open';
					me.sfEditor.addFileMenuItems([{
						text: t("Save"),
						id: 'go_save',
						iconCss: 'e-save e-icons'
					}], refId, true, true);

					// The built-in items post directly from the browser to the
					// Docker service (openUrl/saveUrl) — unreachable and
					// unauthenticated from the client. 'New' would let a blank
					// workbook overwrite the file via our own Save.
					me.sfEditor.hideFileMenuItems(['New', 'Open', 'Save As'], true);
				}
			},
			fileMenuItemSelect: function (args) {
				if (args.item.properties && args.item.properties.id === 'go_save') {
					me.onSave();
				}
			}
		});

		this.sfEditor.appendTo('#' + this.sfContainerId);

		// Ctrl+S / Cmd+S to save back to Group-Office. Scoped to this
		// window's element so it doesn't fire for other open editors.
		this._keydownHandler = function (e) {
			if ((e.ctrlKey || e.metaKey) && e.key === 's') {
				e.preventDefault();
				me.onSave();
			}
		};
		this.el.dom.addEventListener('keydown', this._keydownHandler);
	},

	// =========================================================================
	// Resize
	// =========================================================================

	onEditorPanelResize: function () {
		if (this.sfEditor && this.sfEditor.isRendered && this.sfEditor.resize) {
			this.sfEditor.resize();
		}
	},

	// =========================================================================
	// Save
	// =========================================================================

	onSave: function () {
		if (!this.canEdit) {
			return;
		}

		if (this.editorType === 'document') {
			this.saveDocument();
		} else {
			this.saveSpreadsheet();
		}
	},

	/**
	 * File extension of the file being edited
	 */
	getExtension: function () {
		return (this.fileName.split('.').pop() || '').toLowerCase();
	},

	/**
	 * Name to upload the edited document under. Formats the editor cannot
	 * export back (doc, dotx, rtf) are always exported as DOCX, so the
	 * uploaded blob gets a .docx name; the backend then creates a new file
	 * next to the original instead of replacing it.
	 */
	getDocumentSaveName: function () {
		var ext = this.getExtension();
		if (ext === 'doc' || ext === 'dotx' || ext === 'rtf') {
			return this.fileName.replace(/\.[^.]+$/, '') + '.docx';
		}
		return this.fileName;
	},

	saveDocument: function () {
		this.getEl().mask(t("Saving..."));

		// Txt round-trips; everything else is exported as DOCX
		var format = this.getExtension() === 'txt' ? 'Txt' : 'Docx';

		this.sfEditor.documentEditor.saveAsBlob(format)
			.then(function (blob) {
				this.uploadAndSave(blob);
			}.bind(this))
			.catch(function (err) {
				this.getEl().unmask();
				Ext.MessageBox.alert(t("Error"), 'Failed to export document: ' + err.message);
			}.bind(this));
	},

	saveSpreadsheet: function () {
		var me = this;
		this.getEl().mask(t("Saving..."));

		// Map the original extension to the matching save format so
		// xls/csv files round-trip instead of getting XLSX content.
		var ext = this.getExtension();
		var saveType = ext === 'xls' ? 'Xls' : (ext === 'csv' ? 'Csv' : 'Xlsx');

		this.sfEditor.saveAsJson().then(function (response) {
			var jsonData = JSON.stringify(response.jsonObject.Workbook);

			go.Jmap.request({
				method: 'community/syncfusion/Editor/exportSpreadsheet',
				params: {
					jsonData: jsonData,
					fileName: me.fileName,
					saveType: saveType
				},
				callback: function (options, success, result) {
					if (!result || !result.success) {
						me.getEl().unmask();
						Ext.MessageBox.alert(t("Error"), (result && result.error) || 'Export failed');
						return;
					}

					var params = {blobId: result.blobId};
					if (me.fileId) {
						params.fileId = me.fileId;
					}

					go.Jmap.request({
						method: 'community/syncfusion/Editor/save',
						params: params,
						callback: function (options, success, saveResult) {
							me.getEl().unmask();

							if (!saveResult || !saveResult.success) {
								Ext.MessageBox.alert(t("Error"), (saveResult && saveResult.error) || 'Save failed');
								return;
							}

							if (me.fileId) {
								// unhide=false: don't pop the Files tab open
								var filesModule = GO.mainLayout.getModulePanel('files', false);
								if (filesModule && filesModule.gridStore) {
									filesModule.gridStore.reload();
								}
							}

							me._dirty = false;
							me.savedBlobId = result.blobId;
							me.fireEvent('save', me, result.blobId);
						},
						scope: me
					});
				},
				scope: me
			});
		}).catch(function (err) {
			me.getEl().unmask();
			Ext.MessageBox.alert(t("Error"), 'Failed to export spreadsheet: ' + err.message);
		});
	},

	uploadAndSave: function (blob) {
		var uploadName = this.getDocumentSaveName();
		var file = new File([blob], uploadName);

		go.Jmap.upload(file, {
			scope: this,
			success: function (response) {
				var params = {blobId: response.blobId};

				if (this.fileId) {
					params.fileId = this.fileId;
				}

				go.Jmap.request({
					method: 'community/syncfusion/Editor/save',
					params: params,
					callback: function (options, success, result) {
						this.getEl().unmask();

						if (!result || !result.success) {
							var msg = (result && result.error) || 'Save failed';
							Ext.MessageBox.alert(t("Error"), msg);
							return;
						}

						// A non-round-trip format was saved as a new .docx
						// file — retarget so further saves go to that file.
						if (result.newFileId) {
							this.fileId = result.newFileId;
							this.fileName = result.newFileName || uploadName;
							this.setTitle(this.fileName + ' - ' + t("Syncfusion", "syncfusion", "community"));
						}

						if (this.fileId) {
							// unhide=false: don't pop the Files tab open
							var filesModule = GO.mainLayout.getModulePanel('files', false);
							if (filesModule && filesModule.gridStore) {
								filesModule.gridStore.reload();
							}
						}

						this._dirty = false;
						this.savedBlobId = response.blobId;
						this.fireEvent('save', this, response.blobId);
					},
					scope: this
				});
			},
			failure: function () {
				this.getEl().unmask();
				Ext.MessageBox.alert(t("Error"), 'Failed to upload file.');
			}
		});
	},

	// =========================================================================
	// Cleanup
	// =========================================================================

	onBeforeClose: function () {
		if (this._dirty) {
			Ext.MessageBox.confirm(
				t("Close"),
				t("Unsaved changes will be lost. Close anyway?", "syncfusion", "community"),
				function (btn) {
					if (btn === 'yes') {
						this._dirty = false;
						this.close();
					}
				},
				this
			);
			return false;
		}

		if (this._keydownHandler && this.el) {
			this.el.dom.removeEventListener('keydown', this._keydownHandler);
			this._keydownHandler = null;
		}
		if (this.sfEditor) {
			if (this.sfEditor.destroy) {
				this.sfEditor.destroy();
			}
			this.sfEditor = null;
		}
		if (this.iframe) {
			this.iframe.onload = null;
			this.iframe = null;
		}
		this.iframeEj = null;
		return true;
	}
});
