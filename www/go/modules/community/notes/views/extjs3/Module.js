/* global go */

go.Modules.register("community", 'notes', {
	mainPanel: "go.modules.community.notes.MainPanel",
	title: t("Notes"),
	entities: [{
			name: "Note",			
			hasFiles: true,
			links: [{
				/**
				 * Opens a dialog to create a new linked item
				 * 
				 * @param {string} entity eg. "Note"
				 * @param {string|int} entityId
				 * @returns {go.form.Dialog}
				 */
				linkWindow: function(entity, entityId) {
					return new go.modules.community.notes.NoteForm();
				},

				/**
				 * Return component for the detail view
				 * 
				 * @returns {go.detail.Panel}
				 */
				linkDetail: function() {
					return new go.modules.community.notes.NoteDetail();
				}	
			}]
	}, {name: "NoteBook", title: t("Note book")}],	
	systemSettingsPanels: ["go.modules.community.notes.SystemSettingsPanel"]
});


