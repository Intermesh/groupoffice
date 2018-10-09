/* global go */

go.Modules.register("community", 'notes', {
	mainPanel: "go.modules.community.notes.MainPanel",
	title: t("Notes"),
	entities: [{
			name: "Note",
			linkWindow: function() {
				return new go.modules.community.notes.NoteForm();
			},
			linkDetail: function() {
				return new go.modules.community.notes.NoteDetail();
			}	
		}, "NoteBook"]
});


