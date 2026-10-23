import {comp, Component, t, Window, WindowEventMap} from "@intermesh/goui";
import {client} from "@intermesh/groupoffice-core";
import {getCdnVersion, getLibraryUrls, getSettings, loadLibrary, registerLicense} from "./Loader.js";

export type EditorType = "document" | "spreadsheet";

export interface EditorWindowEventMap extends WindowEventMap {
	/**
	 * Fires after the edited file was saved
	 */
	save: {
		/**
		 * The blob holding the saved content
		 */
		blobId: string
	}
}

/**
 * Response of the Editor JMAP methods. They report failures in the result instead of a JMAP error.
 */
interface EditorResponse {
	success: boolean,
	error?: string,
	[key: string]: any
}

const tm = (key: string) => t(key, "community", "syncfusion");

/**
 * Window with the Syncfusion document or spreadsheet editor
 *
 * Opens a legacy Files module file (fileId) or a JMAP blob (blobId), converts it through the backend and saves it back.
 */
export class EditorWindow extends Window<EditorWindowEventMap> {

	private readonly editorPanel: Component;
	private readonly sfLocale: string;
	private iframe?: HTMLIFrameElement;
	private ej: any;
	private sfEditor: any;
	private canEdit = false;
	private dirty = false;
	private saving = false;
	private closed = false;
	private resizeObserver?: ResizeObserver;

	/**
	 * Blob ID of the last save
	 */
	public savedBlobId?: string;

	/**
	 * @param editorType 'document' or 'spreadsheet'
	 * @param fileName Name of the file being edited
	 * @param fileId Legacy Files module file ID
	 * @param blobId JMAP blob ID, when there is no fileId
	 */
	constructor(private readonly editorType: EditorType, private fileName: string, private fileId?: number, private readonly blobId?: string) {
		super();

		this.width = 1000;
		this.height = 800;
		this.maximizable = true;
		this.resizable = true;
		this.updateTitle();

		// Map the GO language to a Syncfusion locale
		const lang: string = GO.lang.iso || "en";
		this.sfLocale = lang === "en" ? "en-US" : lang;

		this.editorPanel = comp({cls: "syncfusion-editor", flex: 1});
		this.items.add(this.editorPanel);

		this.on("show", () => {
			// Mask right away: loading the Syncfusion bundle is usually the longest phase, well before the
			// conversion starts
			this.mask(undefined, 0);
			void this.start();
		}, {once: true});

		this.on("beforeclose", () => this.onBeforeClose());
		// Before the element leaves the DOM: the document editor writes its state to the iframe's storage on destroy,
		// which is gone once the iframe is removed
		this.on("beforeremove", () => this.cleanup());
	}

	private updateTitle() {
		this.title = this.fileName ? this.fileName + " - " + tm("Syncfusion") : tm("Syncfusion");
	}

	private async start() {
		try {
			if (this.editorType === "spreadsheet") {
				// Rendered directly in the page: inside an iframe the spreadsheet does not resize
				this.ej = await loadLibrary();
			} else {
				// Documents run in an iframe so the ej2 styles can't clash with the GO styles
				this.ej = await this.createIframe();
			}
			if (this.closed) {
				return;
			}
			await this.loadLocale();
			if (this.closed) {
				return;
			}
			await this.convertAndOpen();
		} catch (e: any) {
			if (this.closed) {
				return;
			}
			this.unmask();
			await Window.error(e);
			this.dirty = false;
			this.close();
		}
	}

	/**
	 * Create an iframe that loads the Syncfusion library, resolves with the iframe's `ej` namespace
	 */
	private createIframe(): Promise<any> {
		return new Promise((resolve, reject) => {
			const iframe = this.iframe = document.createElement("iframe");
			iframe.style.width = "100%";
			iframe.style.height = "100%";
			iframe.style.border = "none";
			this.editorPanel.el.appendChild(iframe);

			const urls = getLibraryUrls(),
				doc = iframe.contentDocument!;

			// Wait for the iframe to load completely
			iframe.onload = () => {
				const ej = (iframe.contentWindow as any)?.ej;

				// The iframe fires load even when the library script inside it failed, e.g. CDN unreachable
				if (!ej) {
					reject(new Error(tm("Failed to load the Syncfusion library.")));
					return;
				}
				registerLicense(ej);
				resolve(ej);
			};

			doc.open();
			doc.write(
				'<!DOCTYPE html>' +
				'<html><head>' +
				'<link rel="stylesheet" href="' + urls.css + '">' +
				'<style>' +
				'html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }' +
				'#sf-editor { height: 100%; width: 100%; }' +
				'.e-de-background { background-color: #eee; }' +
				'</style>' +
				'</head><body>' +
				'<div id="sf-editor"></div>' +
				'<script src="' + urls.js + '"><\/script>' +
				'</body></html>'
			);
			doc.close();
		});
	}

	/**
	 * Load the Syncfusion locale for the user's language from the ej2-locale package on jsdelivr
	 */
	private async loadLocale() {
		if (this.sfLocale === "en-US") {
			return;
		}

		try {
			const response = await fetch("https://cdn.jsdelivr.net/npm/@syncfusion/ej2-locale@" + getCdnVersion() + "/src/" + this.sfLocale + ".json");
			if (!response.ok) {
				return;
			}
			const data = await response.json();
			this.ej?.base?.L10n?.load(data);
		} catch (e) {
			// No locale for this language, continue without it
		}
	}

	/**
	 * Call an Editor JMAP method, throws the reported error when it failed
	 */
	private async editorRequest(method: string, params: Record<string, any>): Promise<EditorResponse> {
		const result: EditorResponse = await client.jmap("community/syncfusion/Editor/" + method, params);
		if (!result || !result.success) {
			throw new Error((result && result.error) || t("An error occurred"));
		}
		return result;
	}

	private fileParams(): Record<string, any> {
		if (this.fileId) {
			return {fileId: this.fileId};
		}
		if (this.blobId) {
			return {blobId: this.blobId};
		}
		throw new Error(tm("No file or blob specified."));
	}

	/**
	 * Let the backend convert the file, then open it in the editor
	 */
	private async convertAndOpen() {
		const result = await this.editorRequest(
			this.editorType === "document" ? "openDocument" : "openSpreadsheet",
			this.fileParams()
		);

		// Closed while the file was being converted
		if (this.closed) {
			return;
		}

		if (result.fileName) {
			this.fileName = result.fileName;
			this.updateTitle();
		}

		this.canEdit = result.canEdit !== false;

		if (this.editorType === "document") {
			this.initDocumentEditor(result);
		} else {
			this.initSpreadsheetEditor(result);
		}

		// The mask has been up since show, covering the library load and the conversion
		this.unmask();
	}

	private initDocumentEditor(result: EditorResponse) {
		const ej = this.ej,
			doc = this.iframe!.contentDocument!;

		// Office style tabbed toolbar
		ej.documenteditor.DocumentEditorContainer.Inject(ej.documenteditor.Ribbon);

		this.sfEditor = new ej.documenteditor.DocumentEditorContainer({
			enableToolbar: true,
			toolbarMode: "Ribbon",
			ribbonLayout: "Simplified",
			height: "100%",
			locale: this.sfLocale,
			showPropertiesPane: true,
			enableLocalPaste: false,
			restrictEditing: !this.canEdit,
			// No 'New': replacing the loaded document with a blank one and hitting Save would overwrite the
			// original file.
			fileMenuItems: [
				{text: t("Save"), id: "go_save", iconCss: "e-icons e-save"},
				"Print"
			],
			fileMenuItemClick: (args: any) => {
				if (args.item.id === "go_save") {
					void this.save();
				}
			},
			created: () => {
				this.sfEditor.documentEditor.pageOutline = "#d1d1d1";
			},
			contentChange: () => {
				this.dirty = true;
			}
		});

		this.sfEditor.appendTo(doc.getElementById("sf-editor"));
		this.sfEditor.documentEditor.open(JSON.stringify(result.sfdt));

		// open() itself triggers contentChange, reset once loading settles
		setTimeout(() => {
			this.dirty = false;
		}, 300);

		// Ctrl+S / Cmd+S saves back to Group-Office
		doc.addEventListener("keydown", (e) => this.onKeyDown(e));
	}

	private initSpreadsheetEditor(result: EditorResponse) {
		const ej = this.ej,
			settings = getSettings(),
			serviceUrl = settings.spreadsheetServiceUrl ? settings.spreadsheetServiceUrl.replace(/\/?$/, "/") : "",
			container = document.createElement("div");

		container.id = this.id + "-sf";
		this.editorPanel.el.appendChild(container);

		let spreadsheetData = result.data,
			saveButtonAdded = false;

		this.sfEditor = new ej.spreadsheet.Spreadsheet({
			locale: this.sfLocale,
			allowOpen: true,
			allowSave: true,
			// The File menu only renders when these are set
			openUrl: serviceUrl + "Open",
			saveUrl: serviceUrl + "Save",
			// isProtected only protects the workbook structure, allowEditing keeps the cells read-only
			isProtected: !this.canEdit,
			allowEditing: this.canEdit,
			created: () => {
				this.sfEditor.isRendered = true;

				if (spreadsheetData) {
					const data = typeof spreadsheetData === "object" ? spreadsheetData : JSON.parse(spreadsheetData);
					this.sfEditor.openFromJson({file: data});
					spreadsheetData = undefined;

					// openFromJson is async internally but returns no promise; force a layout recalculation by
					// briefly changing the container size
					setTimeout(() => {
						container.style.width = (container.offsetWidth - 1) + "px";
						setTimeout(() => {
							container.style.width = "";
							this.sfEditor?.resize();
						}, 50);
					}, 200);
				} else {
					this.resizeEditor();
				}
			},
			openComplete: () => {
				this.dirty = false;
				this.resizeEditor();
			},
			actionComplete: () => {
				this.dirty = true;
			},
			// The File menu items are created lazily, so custom items can only be added here
			fileMenuBeforeOpen: () => {
				if (saveButtonAdded) {
					return;
				}
				saveButtonAdded = true;
				this.sfEditor.addFileMenuItems([{
					text: t("Save"),
					id: "go_save",
					iconCss: "e-save e-icons"
				}], container.id + "_Open", true, true);

				// The built-in items post directly from the browser to the Docker service (openUrl/saveUrl),
				// which is unreachable and unauthenticated from the client. 'New' would let a blank workbook
				// overwrite the file via our own Save.
				this.sfEditor.hideFileMenuItems(["New", "Open", "Save As"], true);
			},
			fileMenuItemSelect: (args: any) => {
				if (args.item.properties && args.item.properties.id === "go_save") {
					void this.save();
				}
			}
		});

		this.sfEditor.appendTo("#" + container.id);

		this.resizeObserver = new ResizeObserver(() => this.resizeEditor());
		this.resizeObserver.observe(this.editorPanel.el);

		// Ctrl+S / Cmd+S saves back to Group-Office. On this window's element, so it doesn't fire for other
		// open editors. Captured before the spreadsheet's own handler, which would open its Save As dialog
		// that posts to the Docker service from the browser.
		this.el.addEventListener("keydown", (e) => this.onKeyDown(e), true);
	}

	private onKeyDown(e: KeyboardEvent) {
		if ((e.ctrlKey || e.metaKey) && e.key === "s") {
			e.preventDefault();
			e.stopPropagation();
			void this.save();
		}
	}

	private resizeEditor() {
		if (this.sfEditor && this.sfEditor.isRendered && this.sfEditor.resize) {
			this.sfEditor.resize();
		}
	}

	/**
	 * Extension of the file being edited
	 */
	private getExtension(): string {
		return (this.fileName.split(".").pop() || "").toLowerCase();
	}

	/**
	 * Name to upload the edited document under. Formats the editor cannot export back (doc, dotx, rtf) are always
	 * exported as DOCX, so the uploaded blob gets a .docx name; the backend then creates a new file next to the
	 * original instead of replacing it.
	 */
	private getDocumentSaveName(): string {
		const ext = this.getExtension();
		if (ext === "doc" || ext === "dotx" || ext === "rtf") {
			return this.fileName.replace(/\.[^.]+$/, "") + ".docx";
		}
		return this.fileName;
	}

	/**
	 * Save the edited content back to the file or to a new blob
	 */
	public async save() {
		// The mask does not stop Ctrl+S, so guard against a second save while one is running
		if (!this.canEdit || !this.sfEditor || this.saving) {
			return;
		}

		this.saving = true;
		this.mask(undefined, 0);
		try {
			const saved = this.editorType === "document" ? await this.saveDocument() : await this.saveSpreadsheet();

			if (this.fileId) {
				// unhide=false: don't pop the Files tab open
				const filesModule = GO.mainLayout.getModulePanel("files", false);
				if (filesModule && filesModule.gridStore) {
					filesModule.gridStore.reload();
				}
			}

			this.dirty = false;
			this.savedBlobId = saved;
			this.fire("save", {blobId: saved});
		} catch (e: any) {
			void Window.error(e);
		} finally {
			this.saving = false;
			this.unmask();
		}
	}

	private async saveDocument(): Promise<string> {
		// Txt round-trips; everything else is exported as DOCX
		const format = this.getExtension() === "txt" ? "Txt" : "Docx",
			blob: Blob = await this.sfEditor.documentEditor.saveAsBlob(format),
			uploadName = this.getDocumentSaveName(),
			upload = await client.upload(new File([blob], uploadName)),
			params: Record<string, any> = {blobId: upload.id};

		if (this.fileId) {
			params.fileId = this.fileId;
		}

		const result = await this.editorRequest("save", params);

		// A format that does not round-trip was saved as a new .docx file; retarget so further saves go there
		if (result.newFileId) {
			this.fileId = result.newFileId;
			this.fileName = result.newFileName || uploadName;
			this.updateTitle();
		}

		return upload.id;
	}

	private async saveSpreadsheet(): Promise<string> {
		// Map the original extension to the matching save format so xls/csv files round-trip instead of getting
		// XLSX content
		const ext = this.getExtension(),
			saveType = ext === "xls" ? "Xls" : (ext === "csv" ? "Csv" : "Xlsx"),
			response = await this.sfEditor.saveAsJson();

		const exported = await this.editorRequest("exportSpreadsheet", {
			jsonData: JSON.stringify(response.jsonObject.Workbook),
			fileName: this.fileName,
			saveType: saveType
		});

		const params: Record<string, any> = {blobId: exported.blobId};
		if (this.fileId) {
			params.fileId = this.fileId;
		}

		await this.editorRequest("save", params);

		return exported.blobId;
	}

	private onBeforeClose(): boolean {
		if (!this.dirty) {
			return true;
		}

		void Window.confirm(tm("Unsaved changes will be lost. Close anyway?"), t("Close")).then((confirmed) => {
			if (confirmed) {
				this.dirty = false;
				this.close();
			}
		});
		return false;
	}

	private cleanup() {
		this.closed = true;
		this.resizeObserver?.disconnect();
		this.resizeObserver = undefined;

		try {
			this.sfEditor?.destroy?.();
		} catch (e) {
			// A failing destroy in the library must not keep the window open
			console.error(e);
		}
		this.sfEditor = undefined;

		if (this.iframe) {
			this.iframe.onload = null;
			this.iframe = undefined;
		}
		this.ej = undefined;
	}
}
