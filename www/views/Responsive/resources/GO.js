
var GO = angular.module('GO', ['ui.bootstrap','ngRoute']);



// configure our routes
GO.config(function($routeProvider) {
	$routeProvider

		// route for the home page
		.when('/', {
			templateUrl : 'views/Responsive/view/login.html',
			controller  : 'LoginController'
		})

		// route for the about page
		.when('/start', {
			templateUrl : 'views/Responsive/view/start.html',
			controller  : 'StartController'
		})

});


GO.config(function($routeProvider) {
	$routeProvider

		// route for the home page
		.when('/apps/email', {
			templateUrl : 'views/Responsive/view/email.html',
			controller  : 'EmailController'
		});


});




GO.factory('alert', ['$rootScope', function($rootScope) {
		var alertService;
		$rootScope.alerts = [];
		return alertService = {
			
			set: function(type, msg){
				this.clear();
				this.add(type,msg);
			},
			
			add: function(type, msg) {
				return $rootScope.alerts.push({
					type: type,
					msg: msg,
					close: function() {
						return alertService.closeAlert(this);
					}
				});
			},
			closeAlert: function(alert) {
				return this.closeAlertIdx($rootScope.alerts.indexOf(alert));
			},
			closeAlertIdx: function(index) {
				return $rootScope.alerts.splice(index, 1);
			},
			clear: function() {
				$rootScope.alerts = [];
			}
		};
	}
]);


GO.factory('utils', ['$rootScope', function($rootScope) {
		return {
			
			baseUrl : '/trunk/www/',
			
			securityToken : '',
			
			url: function(relativeUrl, params) {
				if (!relativeUrl && !params)
					return this.baseUrl;
				var url = this.baseUrl + "index.php?r=" + relativeUrl + "&security_token=" + this.securityToken;
				if (params) {
					for (var name in params) {
						url += "&" + name + "=" + encodeURIComponent(params[name]);
					}
				}
				return url;
			}
		}
	}
]);



function LoginController($scope, $http, $location, utils, alert) {
		$scope.master = {username:'John'};
		
	

		$scope.login = function(user) {
			console.log(user);
			
			var url = utils.url('auth/login');
			
//			console.log(url);
			
			$http.post(url, user)		
					.success(function(data) {
//							console.log(data);
						
							alert.clear();

							if (!data.success) {
									alert.set('warning',data.feedback);
							} else {
									$location.path('/start');
							}
					});
			
			
		};

		$scope.reset = function() {
			$scope.user = angular.copy($scope.master);
		};

		$scope.reset();
	}