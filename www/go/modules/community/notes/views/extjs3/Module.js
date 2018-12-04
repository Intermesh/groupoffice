/* global go */

go.Modules.register("community", 'notes', {
	mainPanel: "go.modules.community.notes.MainPanel",
	title: t("Notes"),
	entities: ["Note", "NoteBook"],
	links: [{
			/**
			 * Entity name
			 */
			entity: "Note",
			
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
			 * @returns {go.panels.DetailView}
			 */
			linkDetail: function() {
				return new go.modules.community.notes.NoteDetail();
			}	
		}],
	
	systemSettingsPanels: ["go.modules.community.notes.SystemSettingsPanel"]
});


