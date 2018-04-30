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

go.Router.add(/files\/(\w+)\/([0-9/]+)/, function(root, id) {
	console.log(root,id);
	var b = GO.mainLayout.openModule('files').browser;
	b.at = root;
	b.path = [];
	b.nav(id.replace(/\/$/g, '').split('/'));
});