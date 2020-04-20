/**
 * 
 * Credits: http://krasimirtsonev.com/blog/article/A-modern-JavaScript-router-in-100-lines-history-api-pushState-hash-url
 */

go.Router = (function () {
	var Router = Ext.extend(Ext.util.Observable, {
		routes: [],
		root: '/',

		pathBeforeLogin: "",
		suspendEvent: false,

		previousPath: null,
		loadedPath: null,

		routing: false,

		requireAuthentication: false,

		defaultRoute: null,

		config: function (options) {
			this.root = options && options.root ? '/' + this.trimSlashes(options.root) + '/' : '/';
			return this;
		},
		getPath: function () {
			return window.location.hash.substr(1);
		},

		setPath: function (path) {
			//this._setPath = path; //to cancel event
			if (path != window.location.hash) {
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
		 * @example
		 * 
		 * ```
		 * go.Router.add(/([a-zA-Z0-9]*)\/([0-9]*)/, function(entity, id) {
		 * 
		 * });
		 * 
		 * @param {RegExp|function} /notes/(.*)/
		 * @param {type} handler
		 * @returns {go.Router}
		 */
		add: function (re, handler, requireAuthentication) {

			if (typeof requireAuthentication === "undefined") {
				requireAuthentication = true;
			}

			if (typeof re == 'function') {
				handler = re;
				re = '';
				this.defaultRoute = { re: re, handler: handler, requireAuthentication: requireAuthentication };
				return this;
			}

			this.routes.push({ re: re, handler: handler, requireAuthentication: requireAuthentication });
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

			this.oldPath = this.loadedPath;
			this.loadedPath = path;

			if (this.suspendEvent) {
				var me = this;
				setTimeout(function () {
					me.suspendEvent = false;
				});

				return this;
			}

			for (var i = 0; i < this.routes.length; i++) {
				var match = path.match(this.routes[i].re);
				if (match) {
					match.shift();
					return this.handleRoute(this.routes[i], match);
				}
			}
			return this.defaultRoute ? this.handleRoute(this.defaultRoute, []) : this;
		},

		handleRoute: function (route, match) {
			this.requireAuthentication = route.requireAuthentication;

			if (!go.User.isLoggedIn() && route.requireAuthentication) {

				console.log("redirect", route);
				go.AuthenticationManager.login();

				return;// this.goto('login');
			}

			for (var n = 0, l = match.length; n < l; n++) {
				match[n] = decodeURIComponent(match[n]);
			}
			this.routing = true;
			route.handler.apply({}, match);
			this.routing = false;

			this.fireEvent("change", this.getPath(), this.oldPath, route);
			return this;
		},

		goto: function (path) {

			if (this.getPath() == path) {

				//rerun route if hash is the same
				go.Router.check();
			} else {
				window.location.hash = path || "";
			}
			return this;
		}
	});

	return new Router();
})();

//// configuration
go.Router.config({ mode: 'hash' });

GO.mainLayout.on("boot", function () {

	// go.Router.add(/^login$/, function () {
	// 	GO.mainLayout.login();
	// }, false);

	go.Router.add(/^recover\/([a-f0-9]{40})-?(.*)/, function (hash, redirectUrl) {
		var recoveryPanel = new go.login.RecoveryDialog();
		recoveryPanel.show(hash, redirectUrl);
	}, false);

	// default route
	go.Router.add(function () {
		if (go.User.isLoggedIn()) {
			go.Router.goto(GO.settings.start_module);
		}
	});
});




window.addEventListener('hashchange', function () {
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


