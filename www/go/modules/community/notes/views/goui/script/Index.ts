import {AclItemEntity, AclOwnerEntity, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {EntityID, t} from "@intermesh/goui";
import {SettingsPanel} from "./SettingsPanel.js";
import {NoteDialog} from "./NoteDialog";
import {NoteDetail} from "./NoteDetail";

export * from "./NoteDialog";

modules.register({
	package: "community",
	name: "notes",
	panels: {
		notes: {
			cmp: Main,
			title: t("Notes"),
			routes: {
				"^note/(\\d+)$"(noteId) {
					this.show();
					if (noteId) {
						this.showNote(noteId);
					}
				}
			}
		}
	},
	userSettingsPanels: [SettingsPanel],
	entities: [{
		name: "Note",
		filters: {
			text: {
				type: "string",
				multiple: false,
				title: t("Query")
			},
			name: {
				type: "string",
				multiple: true,
				title: t("Name")
			},
			content: {
				type: "string",
				multiple: true,
				title: t("Content")
			},
			link: {
				title: t("Has links to..."),
				multiple: false,
				type: 'link'
			},
			commentedat: {
				title: t("Commented at"),
				multiple: false,
				type: 'date'
			},
			modifiedat: {
				title: t("Modified at"),
				multiple: false,
				type: 'date'
			},
			modifiedBy: {
				title: t("Modified by"),
				multiple: true,
				type: 'string'
			},
			createdat: {
				title: t("Created at"),
				multiple: false,
				type: 'date'
			},
			createdby: {
				title: t("Created by"),
				multiple: true,
				type: 'string'
			}
		},
		links: [{
			iconCls: 'entity ic-note yellow',
			/**
			 * Opens a dialog to create a new linked item
			 *
			 * @param entity eg. "Note"
			 * @param entityId
			 */
			linkWindow:  (entity, entityId) => {
				return new NoteDialog();
			},

			/**
			 * Return component for the detail view
			 *
			 * @returns {go.detail.Panel}
			 */
			linkDetail: ()=> {
				return new NoteDetail();
			}
		}]
	}, {
		name: "NoteBook", title: t("Note book")
	}],
});


export interface Note extends AclItemEntity {
	name: string,
	content: string
	noteBookId: EntityID
}

export interface NoteBook extends AclOwnerEntity {
	name: string
}

export const noteBookDS = new JmapDataSource<NoteBook>("NoteBook");
export const noteDS = new JmapDataSource<NoteBook>("Note");