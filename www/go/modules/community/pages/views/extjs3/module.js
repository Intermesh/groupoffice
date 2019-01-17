go.Modules.register("community", "pages", {
    title: t("Site"),
    entities: ["Page", "Site"],
    systemSettingsPanels: [
	"go.modules.community.pages.SystemSettingsSitesGrid"
    ],
    loaded: false,
    initModule: function () {
	this.entityStore = go.Stores.get("Site");
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
    //Redirect the tabpanel route to the view route and get the first page of the site.
    routefunction = function () {
	go.Jmap.request({
	    method: "Site/getFirstPage",
	    params: {
		slug: slug
	    },
	    callback: function (options, success, result) {
		var pageSlug = false;
		if (result['list'][0] && result['list'][0]['slug']) {
		    pageSlug = result['list'][0]['slug'];
		}
		if (!pageSlug || !success) {
		    console.warn("Failed to retrieve any pages.")
		    go.Router.goto(slug + '\/view\/');
		} else {
		    go.Router.goto(slug + '\/view\/' + pageSlug);
		}
	    },
	    scope: this
	});
    };
    return routefunction;

};

//All site related hashes end up here through redirects.
go.Router.add(/(.*)\/view\/(.*)/, function (siteSlug, pageHeaderSlug) {
    var pageSlug, headerSlug, panel;
    slugs = pageHeaderSlug.split('/');
    pageSlug = slugs[0];
    headerSlug = slugs[1];
    panel = GO.mainLayout.getModulePanel(siteSlug);

    //Checks if the site is already initialized;
    if (panel.siteSlug !== siteSlug) {
	console.log('loading site')
	go.Jmap.request({
	    method: "Site/get",
	    params: {
		slug: siteSlug
	    },
	    callback: function (options, success, result) {
		panel = GO.mainLayout.openModule(siteSlug);
		if (success && result['list'][0]) {
		    panel.setSiteId(result['list'][0]['id']);
		    panel.siteSlug = siteSlug;
		    //sets up the eventlistener needed to properly jump to headers.
		    panel.content.on('contentLoaded', function () {

			//gets the current header, passing the earlier headerSlug wont update itself.
			headerSlug = go.Router.getPath().split('/')[3];
			if (headerSlug) {
			    header = document.getElementById(headerSlug);
			    if (header) {
				header.scrollIntoView();
			    }
			}
		    }, this);
		    openPage(pageSlug, panel);
		} else {
		    go.Router.goto('summary');
		    console.warn('No Site found with the slug: ' + siteSlug);
		}
	    },
	    scope: this
	});

	//Check to see if the page has already been set.
    } else if (panel.pageSlug !== pageSlug) {
	console.log('loading page')
	GO.mainLayout.openModule(siteSlug);
	openPage(pageSlug, panel);

	//if the site and page are already open jump to the header if there is one in the url.
    } else if (headerSlug) {
	console.log('jump to header')
	GO.mainLayout.openModule(siteSlug);
	header = document.getElementById(headerSlug);
	if (header) {
	    header.scrollIntoView();
	}
    }
});

//this method attempts to get and load a page based on the slug.
//if page isnt found it fills the content of the page with a page not found string.
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
		    panel.navigateToPage(result['list'][0]['id'], pageSlug);

		} else {
		    panel.navigateToPage();
		    console.warn('No Page found with the slug: ' + pageSlug);
		}
	    },
	    scope: this
	});
    } else {
	panel.navigateToPage();
    }
};