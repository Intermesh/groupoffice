go.ActivityWatcher = (function() {
	var ActivityWatcher = function() {

	};

	Ext.apply(ActivityWatcher.prototype, {
		maxInactivity: 0,

		//The function that will be called whenever a user is active
		activity : function() {
			//reset the secondsSinceLastActivity variable
			//back to 0
			localStorage.lastActivity = Math.floor(Date.now() / 1000);
		},

		init : function(maxInactivity) {


			var me = this;

			me.maxInactivity = maxInactivity;

			if(me.maxInactivity < 1) {
				return;
			}

			console.log("Enabling logout on inactivity after " + maxInactivity + "s");

			me.checkActivity();

			//Setup the setInterval method to run
			//every second. 1000 milliseconds = 1 second.
			me.interval = setInterval(function() {
				me.checkActivity();
			}, 1000);

			//register main document
			me.registerDocument(window.document);
		},


		checkActivity : function() {
			if(!localStorage.lastActivity) {
				return;
			}

			var now = Math.floor(Date.now() / 1000);;

			var secondsSinceLastActivity = now - localStorage.lastActivity;
			//console.log(secondsSinceLastActivity + ' seconds since the user was last active');
			//if the user has been inactive or idle for longer
			//then the seconds specified in maxInactivity
			if(secondsSinceLastActivity > this.maxInactivity){
				console.log('User has been inactive for more than ' + this.maxInactivity + ' seconds');
				clearInterval(this.interval);
				localStorage.removeItem("lastActivity");
				go.AuthenticationManager.logout();
			}
		},

		registerDocument: function(doc) {
			//An array of DOM events that should be interpreted as
			//user activity.
			var activityEvents = [
				'mousedown', 'mousemove', 'keydown',
				'scroll', 'touchstart'
			], me = this;

			//add these events to the document.
			//register the activity function as the listener parameter.
			activityEvents.forEach(function(eventName) {
				doc.addEventListener(eventName, function() {
					me.activity();
				}, true);
			});
		}
	});


	return new ActivityWatcher();

})();