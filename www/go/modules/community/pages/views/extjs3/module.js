go.Modules.register("community", "pages", {
    title: t("Site"),
    entities: ["Page", "Site"],
    systemSettingsPanels: [
	"go.modules.community.pages.SystemSettingsSitesGrid"
    ],
    loaded: false,
    initModule: function () {
	//gets a list with all sites
	go.Jmap.request({
	    method: "Site/get",
	    params: {
	    },
	    callback: function (options, success, result) {
		var overViewTabConfig;
		//generate routes for the sites and add all of them to the mainlayout tabpanel
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
	    },
	    scope: this
	});
    }
});

generateRoute = function (site) {
    var slug = site.slug;
    var routefunction;
    //Redirect the tabpanel route to the view route and get the first page of the site..
    routefunction = function () {
	go.Jmap.request({
	    method: "Site/getFirstPage",
	    params: {
		slug: go.Router.getPath()
	    },
	    callback: function (options, success, result) {
		var PageSlug = result['list'][0]['slug'];
		if (!success) {
		    console.log(result);
		    window.alert(t("Something went wrong while connecting to the server."))
		    console.log('redirect from: ' + go.Router.getPath());
		    go.Router.goto(slug + '\/view\/');
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
go.Router.add(/(.*)\/view\/(.*)/, function (siteSlug, pageHeaderSlug) {
    var pageSlug, headerSlug, p;
    slugs = pageHeaderSlug.split('/');
    pageSlug = slugs[0];
    headerSlug = slugs[1];
    p = GO.mainLayout.getModulePanel(siteSlug);

    //checks if the site is already initialized;
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
		//sets up the eventlistener needed to properly jump to headers.
		p.content.on('contentLoaded', function () {
		    header = document.getElementById(go.Router.getPath());
		    if (header) {
			header.scrollIntoView();
		    }
		});
		openPage(pageSlug, p);
	    },
	    scope: this
	});
    } else {
	GO.mainLayout.openModule(siteSlug);
	openPage(pageSlug, p);
    }
});

//this method attempts to get and load a page based on the slug.
//if page isnt found it loads the a page not found page.
openPage = function (pageSlug, panel) {
    if (pageSlug) {
	go.Jmap.request({
	    method: "Page/get",
	    params: {
		slug: pageSlug,
		siteId: panel.siteId
	    },
	    callback: function (options, success, result) {
		if (success && result['list'][0]) {
		    panel.navigateToPage(result['list'][0]['id']);

		} else {
		    panel.navigateToPage();
		}
	    },
	    scope: this
	});
    } else {
	panel.navigateToPage();
    }
};