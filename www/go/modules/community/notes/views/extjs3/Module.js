Ext.ns('go.modules.notes');

go.Modules.register("community", 'notes', {
	mainPanel: "go.modules.notes.MainPanel",
	title: t("Notes", "notes"),
	entities: ["Note", "NoteBook"],
	initModule: function () {	
		go.Links.registerLinkToWindow("Note", function() {
			return new go.modules.notes.NoteForm();
		});
	}
});


