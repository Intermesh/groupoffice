Ext.ns('go.modules.community.files');

go.Modules.register("community", 'files', {
	mainPanel: "go.modules.community.files.MainPanel",
	title: t("Files", "files"),
	entities: ["File", "Folder"],
	initModule: function () {	
		go.Links.registerLinkToWindow("File", function() {
			return new go.modules.community.files.FileForm();
		});
	}
});


