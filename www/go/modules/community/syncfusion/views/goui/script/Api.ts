import {EditorType, EditorWindow} from "./EditorWindow.js";

export const DOCUMENT_EXTENSIONS = ["docx", "doc", "dotx", "rtf", "txt"];
export const SPREADSHEET_EXTENSIONS = ["xlsx", "xls", "csv"];
export const SUPPORTED_EXTENSIONS = DOCUMENT_EXTENSIONS.concat(SPREADSHEET_EXTENSIONS);

function editorType(extension: string): EditorType {
	return SPREADSHEET_EXTENSIONS.includes(extension.toLowerCase()) ? "spreadsheet" : "document";
}

/**
 * Open the editor for a file of the legacy Files module
 *
 * @param fileId
 * @param fileName
 * @param extension
 * @param folderId Unused, kept for the file handler's call signature
 */
export function openFile(fileId: number, fileName: string, extension: string, folderId?: number): EditorWindow {
	const win = new EditorWindow(editorType(extension), fileName, fileId);
	win.show();
	return win;
}

/**
 * Open the editor for a JMAP blob
 *
 * Listeners use the ExtJS signature of the former ExtJS window, e.g. `{save: (win, blobId) => {}}`, so ExtJS
 * callers such as the DMS module keep working.
 *
 * @param blobId
 * @param fileName
 * @param listeners Optional `save` and `close` listeners
 */
export function openBlob(blobId: string, fileName: string, listeners: {save?: (win: EditorWindow, blobId: string) => void, close?: (win: EditorWindow) => void} = {}): EditorWindow {
	const win = new EditorWindow(editorType(fileName.split(".").pop() || ""), fileName, undefined, blobId);
	if (listeners.save) {
		const save = listeners.save;
		win.on("save", ({blobId}) => save(win, blobId));
	}
	if (listeners.close) {
		const close = listeners.close;
		win.on("close", () => close(win));
	}
	win.show();
	return win;
}
