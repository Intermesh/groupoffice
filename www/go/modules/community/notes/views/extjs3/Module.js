go.Modules.register("community", 'notes', {
	mainPanel: "go.modules.community.notes.MainPanel",
	title: t("Notes"),
	entities: ["Note", "NoteBook"],
	initModule: function () {	
		go.Links.registerLinkToWindow("Note", function() {
			return new go.modules.community.notes.NoteForm();
		}, t('Note'));
	}
});


