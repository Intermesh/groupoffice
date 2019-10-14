/**
 * 
 * Credits: http://krasimirtsonev.com/blog/article/A-modern-JavaScript-router-in-100-lines-history-api-pushState-hash-url
 */

go.Router = {
	routes: [],
	root: '/',
	
	pathBeforeLogin : "",
	suspendEvent : false,
	
	previousPath : null,
	loadedPath : null,
	
	routing: false,
	
	requireAuthentication : false,
	
	config: function (options) {
		this.root = options && options.root ? '/' + this.trimSlashes(options.root) + '/' : '/';
		return this;
	},
	getPath: function () {
		return window.location.hash.substr(1);
	},
	
	setPath : function(path) {
		//this._setPath = path; //to cancel event
		if(path != window.location.hash) {
			this.suspendEvent = true;
			window.location.hash = path;
		}
	},
	
	trimSlashes: function (path) {
		return path.toString().replace(/\/$/, '').replace(/^\//, '');
	},
	
	/**
	 * Add a route
	 * 
	 * 
	 * @param {RegExp|function} /notes/(.*)/
	 * @param {type} handler
	 * @returns {go.Router}
	 */
	add: function (re, handler, requireAuthentication) {
		if (typeof re == 'function') {
			handler = re;
			re = '';
		}
		
		if(typeof requireAuthentication === "undefined") {
			requireAuthentication = true;
		}
		
		this.routes.push({re: re, handler: handler, requireAuthentication: requireAuthentication});
		return this;
	},
	remove: function (param) {
		for (var i = 0, r; i < this.routes.length, r = this.routes[i]; i++) {
			if (r.handler === param || r.re.toString() === param.toString()) {
				this.routes.splice(i, 1);
				return this;
			}
		}
		return this;
	},
	flush: function () {
		this.routes = [];		
		this.root = '/';
		return this;
	},
	check: function (f) {
		var path = f || this.getPath();
		
		this.loadedPath = path;
				
		if(this.suspendEvent) {
			this.suspendEvent = false;
			return this;
		}
		
		
		
		for (var i = 0; i < this.routes.length; i++) {
			var match = path.match(this.routes[i].re);
			if (match) {
				match.shift();
				
				this.requireAuthentication = this.routes[i].requireAuthentication;
				
				if(!go.User && this.routes[i].requireAuthentication){
					
					console.log("redirect", this.routes[i]);
					this.pathBeforeLogin = this.getPath();
					return this.goto('login');					
				}
	
				for(var n = 0, l = match.length; n < l; n++) {
					match[n] = decodeURIComponent(match[n]);
				}
				this.routing = true;
				this.routes[i].handler.apply({}, match);
				this.routing = false;
				return this;
			}
		}
		return this;
	},
	goto: function (path) {
		
		if(this.getPath() == path) {
			
			//rerun route if hash is the same
			//go.Router.check();
		} else
		{		
			window.location.hash = path || "";		
		}
		return this;
	}
}

//// configuration
go.Router.config({mode: 'hash'});

GO.mainLayout.on("boot", function() {		
	
	
	
	go.Router.add(/^login$/, function() {
		GO.mainLayout.login();
	}, false);

	go.Router.add(/^recover\/([a-f0-9]{40})-?(.*)/, function(hash, redirectUrl) {
		var recoveryPanel = new go.login.RecoveryDialog();
		recoveryPanel.show(hash, redirectUrl);
	}, false);
	
	
	//Add these default routes on boot so they are added as last options for sure.
	//
	//default route for entities		
	go.Router.add(/([a-zA-Z0-9]*)\/([0-9]*)/, function(entity, id) {
		var entityObj = go.Entities.get(entity);
		if(!entityObj) {
			console.log("Entity ("+entity+") not found in default entity route")
			return false;
		}
    
    var module = entityObj.module; 
		var mainPanel = GO.mainLayout.openModule(module);
		var detailViewName = entity + "Detail";

		if (mainPanel.route) {
			mainPanel.route(parseInt(id), entity);
		} else if(mainPanel[detailViewName]) {
			mainPanel[detailViewName].load(parseInt(id));
			mainPanel[detailViewName].show();
		} else {
			console.log("Default entity route failed because " + detailViewName + " or 'route' function not found in mainpanel of " + module + ":", mainPanel);
			console.log(arguments);
		}
	});

	//default route
	go.Router.add(function() {	
		if(go.User.isLoggedIn()) {
			
			if(!go.Modules.isAvailable("legacy", GO.settings.start_module)) {
				//console.log(GO.mainLayout.tabPanel.items.first());
				GO.settings.start_module = GO.mainLayout.tabPanel.items.first().module;
			}
			
			go.Router.goto(GO.settings.start_module);
		}
	});
		
	go.Router.check();			
});




window.addEventListener('hashchange', function() {	
	go.Router.previousPath = go.Router.loadedPath;
	go.Router.check();
}, false);

//
//// returning the user to the initial state
//Router.navigate();
//
//// adding routes
//Router
//				.add(/about/, function () {
//					console.log('about');
//				})
//				.add(/products\/(.*)\/edit\/(.*)/, function () {
//					console.log('products', arguments);
//				})
//				.add(function () {
//					console.log('default');
//				})
//				.check('/products/12/edit/22').listen();
//
//// forwarding
//Router.navigate('/about');


