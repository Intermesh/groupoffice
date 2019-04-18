/* global go */

go.Modules.register("community", 'notes', {	
	title: t("Notes"),
	initModule: function() {
		this.addPanel(go.modules.community.notes.MainPanel);
	},
	
	entities: [{
			name: "Note",			
			hasFiles: true,
			relations: {
				creator: {store: "User", fk: "createdBy"},
				modifier: {store: "User", fk: "createdBy"}
			},
			links: [{
				/**
				 * Opens a dialog to create a new linked item
				 * 
				 * @param {string} entity eg. "Note"
				 * @param {string|int} entityId
				 * @returns {go.form.Dialog}
				 */
				linkWindow: function(entity, entityId) {
					return new go.modules.community.notes.NoteDialog();
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
	}, {name: "NoteBook", title: t("Note book")}]
});


