Ext.ns('go.modules.community.files');

go.Modules.register("community", 'files', {
	mainPanel: "go.modules.community.files.MainPanel",
	title: t("Files", "files"),
	entities: ["Node"],
	initModule: function () {
		go.Links.registerLinkToWindow("Node", function() {
			return new go.modules.community.files.FileForm();
		});
	}
});

go.Preview = function(file) {
	if(!this.preview) {
		this.preview = new go.modules.community.files.PreviewLayer();
	}
	this.preview.show(file);
}