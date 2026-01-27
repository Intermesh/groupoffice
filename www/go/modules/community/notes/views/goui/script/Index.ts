import {
	AclItemEntity,
	AclOwnerEntity,
	appSettings,
	client,
	JmapDataSource,
	modules,
	router
} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {EntityID, t, translate} from "@intermesh/goui";
import {SettingsPanel} from "./SettingsPanel.js";
import {NoteDialog} from "./NoteDialog";
import {NoteDetail} from "./NoteDetail";

modules.register({
	package: "community",
	name: "notes",
	async init() {

		let notes: Main;

		client.on("authenticated", ( {session}) => {
			if (!session.capabilities["go:community:notes"]) {
				// User has no access to this module
				return;
			}

			translate.load(GO.lang.community.notes, "community", "notes");

			router.add(/^note\/(\d+)$/, (noteId) => {
				modules.openMainPanel("notes");
				notes.showNote(noteId);
			});

			modules.addMainPanel("community", "notes", "notes", t("Notes"), () => {
				notes = new Main();
				return notes;
			});

			// modules.addAccountSettingsPanel("community", "notes", "notes", t("Notes"), "note", () => {
			// 	return new SettingsPanel();
			// });

			appSettings.addPanel(SettingsPanel);

		});
	},
	entities: [{
		name: "Note",
		filters: [
			{
				name: 'text',
				type: "string",
				multiple: false,
				title: t("Query")
			},
			{
				name: 'name',
				type: "string",
				multiple: true,
				title: t("Name")
			},
			{
				name: 'content',
				type: "string",
				multiple: true,
				title: t("Content")
			},
			{
				title: t("Has links to..."),
				name: 'link',
				multiple: false,
				type: 'go.links.FilterLinkEntityCombo'
			},
			{
				title: t("Commented at"),
				name: 'commentedat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Modified at"),
				name: 'modifiedat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Modified by"),
				name: 'modifiedBy',
				multiple: true,
				type: 'string'
			}, {
				title: t("Created at"),
				name: 'createdat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Created by"),
				name: 'createdby',
				multiple: true,
				type: 'string'
			}
		],
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