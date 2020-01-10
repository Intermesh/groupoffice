/* global go */

go.Modules.register("community", 'notes', {
	title: t("Notes"),
	initModule: function () {
		this.addPanel(go.modules.community.notes.MainPanel);
	},

	entities: [{
		name: "Note",
		hasFiles: true,
		relations: {
			creator: { store: "User", fk: "createdBy" },
			modifier: { store: "User", fk: "createdBy" }
		},
		filters: [
			{
				name: 'text',
				type: "string",
				multiple: false,
				title: "Query"
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
			 * @param {string} entity eg. "Note"
			 * @param {string|int} entityId
			 * @returns {go.form.Dialog}
			 */
			linkWindow: function (entity, entityId) {
				return new go.modules.community.notes.NoteDialog();
			},

			/**
			 * Return component for the detail view
			 * 
			 * @returns {go.detail.Panel}
			 */
			linkDetail: function () {
				return new go.modules.community.notes.NoteDetail();
			}
		}]
	}, { name: "NoteBook", title: t("Note book") }],

	userSettingsPanels: [
		"go.modules.community.notes.SettingsPanel"
	]
});


