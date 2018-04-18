Ext.ns('go.modules.files');
console.log('test');
go.Modules.register("community", 'files', {
	mainPanel: "go.modules.files.MainPanel",
	title: t("Files", "files"),
	entities: ["File", "Folder"],
	initModule: function () {	
		go.Links.registerLinkToWindow("File", function() {
			return new go.modules.files.FileForm();
		});
	}
});


