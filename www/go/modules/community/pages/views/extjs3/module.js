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
//todo: Laden van site zonder pagina's
//afvangen pagina slugs die niet bestaan!
//als na de pageSlug nog een # staat, opnieuw goto aanroepen om naar de header te springen.
//split pageslug op /!
go.Router.add(/(.*)\/view\/(.*)/, function (siteSlug, pageSlug) {
    console.log('site slug:' + siteSlug);
    console.log('page slug:' + pageSlug);
    var p = GO.mainLayout.openModule("pages");
    //check if the current site is already known.
    if (p.siteSlug !== siteSlug) {
	go.Jmap.request({
	    method: "Site/get",
	    params: {
		slug: siteSlug
	    },
	    callback: function (options, success, result) {
		console.log('setting site id.')
		p.setSiteId(result['list'][0]['id']);
		p.siteSlug = siteSlug;
	    },
	    scope: this
	});
    }
    go.Jmap.request({
	method: "Page/get",
	params: {
	    slug: pageSlug
	},
	callback: function (options, success, result) {
	    p.navigateToPage(result['list'][0]['id']);
	},
	scope: this
    });

});
//redirects to the view hash after crud operations on pages
go.Router.add(/page\/(.*)/, function (pageId) {
    go.Jmap.request({
	method: "Page/get",
	params: {
	    ids: {pageId}
	},
	callback: function (options, success, result) {
	    var p = GO.mainLayout.getModulePanel("pages");
	    console.log('redirect from: ' + go.Router.getPath());
	    go.Router.goto('pages\/view\/' + result['list'][0]['slug']);
	},
	scope: this
    });
});

//Redirect the tabpanel hash to the view hash.
//todo: afvangen fouten bij zoeken van site en eerste pagina.
var routes = go.Router.add(/pages$/, function () {
    // gebaseerd op site|module naam eerste pagina ophalen en naar redirecten
    go.Jmap.request({
	method: "Site/getFirstPage",
	params: {
	    slug: go.Router.getPath()
	},
	callback: function (options, success, result) {
	    var PageSlug = result['list'][0]['slug'];
	    if (!success) {
		console.log(result);
		window.alert("Something went wrong while connection to the server.")
	    } else if (!PageSlug) {
		console.log('failed to find any pages.');
		console.log('redirect from: ' + go.Router.getPath());
		go.Router.goto('pages\/view\/');
	    } else {
		console.log('redirect from: ' + go.Router.getPath());
		go.Router.goto('pages\/view\/' + PageSlug);
	    }
	},
	scope: this
    });

});