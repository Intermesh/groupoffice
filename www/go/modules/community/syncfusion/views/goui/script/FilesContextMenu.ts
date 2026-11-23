import {t} from "@intermesh/goui";
import {openFile, SUPPORTED_EXTENSIONS} from "./Api.js";

/**
 * Add "Edit with Syncfusion" to the context menu of the legacy (ExtJS) Files module
 */
export function extendFilesContextMenu() {
	if (!GO.files || !GO.files.FilesContextMenu) {
		return;
	}

	const proto = GO.files.FilesContextMenu.prototype;
	if (proto.initSyncfusionButton) {
		return;
	}

	const origShowAt = proto.showAt;

	proto.initSyncfusionButton = function (this: any) {
		if (this.syncfusionButton) {
			return;
		}

		this.syncfusionSeparator = new Ext.menu.Separator();
		this.syncfusionButton = new Ext.menu.Item({
			iconCls: "ic-edit",
			text: t("Edit with Syncfusion", "community", "syncfusion"),
			handler: function (this: any) {
				const record = this.records[0];
				openFile(record.data.id, record.data.name, record.data.extension, record.data.folder_id);
			},
			scope: this
		});

		const idx = this.items.indexOf(this.cutSeparator);
		if (idx !== -1) {
			this.insert(idx, this.syncfusionButton);
			this.insert(idx, this.syncfusionSeparator);
		} else {
			this.add(this.syncfusionSeparator);
			this.add(this.syncfusionButton);
		}
	};

	proto.showAt = function (this: any, xy: any, records: any[]) {
		this.initSyncfusionButton();

		let showButton = false;
		if (records && records.length === 1) {
			showButton = SUPPORTED_EXTENSIONS.includes((records[0].data.extension || "").toLowerCase());
		}

		this.syncfusionSeparator.setVisible(showButton);
		this.syncfusionButton.setVisible(showButton);

		return origShowAt.apply(this, arguments);
	};
}
