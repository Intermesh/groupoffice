go.Modules.register("community", "pages", {
	mainPanel: "go.modules.community.pages.MainPanel",
	title: t("Pages"),
	entities: ["Page", "Site"],
	systemSettingsPanels: [
	    "go.modules.community.pages.SystemSettingsSitesGrid"
	],
	initModule: function () {}
});

go.Router.add(/pages\/view\/(.*)/, function(slug) {
     go.Jmap.request({
	method: "Page/get",
	params: {
	    slug: slug
	},
	callback: function(options, success, result) {
	    console.log(result.list);
	    
	    
	    //openModule(sitemodulenaamhier)
	   var p = GO.mainLayout.openModule("pages");
	   p.setIds(2,1);
	},
	scope: this
    });
});
