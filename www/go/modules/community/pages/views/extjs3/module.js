
//register module foreach site?
go.Modules.register("community", "pages", {
	mainPanel: "go.modules.community.pages.MainPanel",
	title: t("Site"),
	entities: ["Page", "Site"],
	systemSettingsPanels: [
	    "go.modules.community.pages.SystemSettingsSitesGrid"
	],
	initModule: function () {}
});
//remove default module routing.
go.Router.remove(/pages$/);


//All site related hashes end up here through redirects.
go.Router.add(/(.*)\/view\/(.*)/, function(siteSlug, pageSlug) {
    console.log('site slug:' + siteSlug);
    console.log('page slug:' + pageSlug);
    console.log('view: '+go.Router.getPath())
    //als na de pageSlug nog een # staat, opnieuw goto aanroepen om naar de header te springen.
    var p = GO.mainLayout.openModule("pages");
    p.siteSlug = siteSlug;
    go.Jmap.request({
	method: "Site/get",
	params: {
	    slug: siteSlug
	},
	callback: function(options, success, result) {
	    p.setSiteId(1);
	},
	scope: this
    });
    go.Jmap.request({
	method: "Page/get",
	params: {
	    slug: pageSlug
	},
	callback: function(options, success, result) {
	    p.navigateToPage(59);
	},
	scope: this
    });
    
});
//redirects to the view hash after crud operations on pages
go.Router.add(/page\/(.*)/ , function(pageId) {
    // slug van de page ophalen
    console.log('redirect from: '+ go.Router.getPath());
    var p = GO.mainLayout.getModulePanel("pages");
    p.navigateToPage(pageId);
    go.Router.goto('pages\/view\/'+pageId+'/');
});

//Redirect the tabpanel hash to the view hash.
var routes = go.Router.add(/pages$/ , function() {
    // site|module naam naar site id vertalen, hierop gebaseerd eerste page ophalen en naar redirecten.
    console.log('redirect from: '+ go.Router.getPath() );    
    go.Router.goto('pages\/view\/');
});