go.Modules.register("community", "pages", {
    //mainPanel: "go.modules.community.pages.MainPanel",
    title: t("Site"),
    entities: ["Page", "Site"],
    systemSettingsPanels: [
	"go.modules.community.pages.SystemSettingsSitesGrid"
    ],
    loaded: false,
    //todo: Tabs added event toevoegen.
    initModule: function () {
	go.Jmap.request({
	    method: "Site/get",
	    params: {
	    },
	    callback: function (options, success, result) {
		//console.log(result['list']);
		var overViewTabConfig;
		for (i = 0; i < result['list'].length; i++) {
		    //configure site tab settings
		    overViewTabConfig = {
			moduleTabId: result['list'][i].slug,
			mainPanel: "go.modules.community.pages.MainPanel",
			title: t(result['list'][i].siteName),
			panelConfig: {
			    title: t(result['list'][i].siteName),
			    routeFunction: generateRoute(result['list'][i])
			}
		    };
		    // This adds the tab
		    GO.mainLayout.addModulePanel(overViewTabConfig.moduleTabId, overViewTabConfig.mainPanel, overViewTabConfig.panelConfig);
		}

		//trigger event hier
	    },
	    scope: this
	});
    }
});

generateRoute = function (site) {
    var slug = site.slug;
    var routefunction;
    //Redirect the tabpanel hash to the view hash.
    //todo: afvangen fouten bij zoeken van site en eerste pagina.
    routefunction = function () {
	//console.log('running route function')
	// gebaseerd op site naam eerste pagina ophalen en naar redirecten
	go.Jmap.request({
	    method: "Site/getFirstPage",
	    params: {
		slug: go.Router.getPath()
	    },
	    callback: function (options, success, result) {
		//console.log('opening page through tab.');
		var PageSlug = result['list'][0]['slug'];
		if (!success) {
		    console.log(result);
		    window.alert("Something went wrong while connection to the server.")
		} else if (!PageSlug) {
		    console.log('failed to find any pages.');
		    console.log('redirect from: ' + go.Router.getPath());
		    go.Router.goto(slug + '\/view\/');
		} else {
		    console.log('redirect from: ' + go.Router.getPath());
		    go.Router.goto(slug + '\/view\/' + PageSlug);
		}
	    },
	    scope: this
	});
    };
    return routefunction;

};

//All site related hashes end up here through redirects.
//todo: 
//Laden van site zonder pagina's
//afvangen pagina slugs die niet bestaan!
//als na de pageSlug nog een # staat, opnieuw goto aanroepen om naar de header te springen.
//split pageslug op /!
//voeg aan page/get ook siteId als param toe!
//requests naar eigen functies verplaatsen.
go.Router.add(/(.*)\/view\/(.*)/, function (siteSlug, pageSlug) {
    var p;
    p = GO.mainLayout.getModulePanel(siteSlug);
    //check if the current site is already known.
    if (p.siteSlug !== siteSlug) {
	go.Jmap.request({
	    method: "Site/get",
	    params: {
		slug: siteSlug
	    },
	    callback: function (options, success, result) {
		p = GO.mainLayout.openModule(siteSlug);
		p.setSiteId(result['list'][0]['id']);
		p.siteSlug = siteSlug;
		if (pageSlug) {
		    go.Jmap.request({
			method: "Page/get",
			params: {
			    slug: pageSlug,
			    siteId: p.siteId
			},
			callback: function (options, success, result) {
			    debugger;
			    if (success) {
				console.log('navtoslug');
				p.navigateToPage(result['list'][0]['id']);
			    } else {
				console.log('navtonull');
				p.navigateToPage();
			    }
			},
			scope: this
		    });
		} else {
		    debugger;
		    console.log('navtonull');
		    p.navigateToPage();
		}
	    },
	    scope: this
	});
    }else{
			if (pageSlug) {
		    go.Jmap.request({
			method: "Page/get",
			params: {
			    slug: pageSlug,
			    siteId: p.siteId
			},
			callback: function (options, success, result) {
			    debugger;
			    if (success) {
				console.log('navtoslug');
				p.navigateToPage(result['list'][0]['id']);
			    } else {
				console.log('navtonull');
				p.navigateToPage();
			    }
			},
			scope: this
		    });
		} else {
		    debugger;
		    console.log('navtonull');
		    p.navigateToPage();
		}
    }

});

console.log(go.Router.routes);


