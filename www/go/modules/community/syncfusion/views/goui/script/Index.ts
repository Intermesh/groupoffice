import {client, modules} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {openBlob, openFile} from "./Api.js";
import {extendFilesContextMenu} from "./FilesContextMenu.js";
import {loadLibrary} from "./Loader.js";
import {SystemSettings} from "./SystemSettings.js";

export * from "./Api.js";
export * from "./EditorWindow.js";
export {loadLibrary} from "./Loader.js";

// Global API for ExtJS code: the Files module file handler (filehandler/Syncfusion.php) and other modules such as
// DMS call these.
Object.assign(Ext.ns("go.modules.community.syncfusion"), {
	openFile,
	openBlob,
	/**
	 * Load the Syncfusion library into the page
	 *
	 * @param editorType Unused, the full bundle is loaded
	 * @param callback Receives an Error when loading failed, null on success
	 */
	loadLibrary: (editorType: string, callback: (error: Error | null) => void) => {
		loadLibrary().then(() => callback(null), (e) => callback(e));
	}
});

modules.register({
	package: "community",
	name: "syncfusion",
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:syncfusion"]) {
				return;
			}

			translate.setDefaultModule("community", "syncfusion");

			modules.addSystemSettingsPanel("community", "syncfusion", "syncfusion", t("Syncfusion"), "description", () => {
				return new SystemSettings();
			});

			extendFilesContextMenu();
		});
	}
});
