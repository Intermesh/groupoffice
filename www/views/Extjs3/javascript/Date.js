Ext.apply(Date.prototype, {

	getLastSunday : function(){
		//Calculate the first day of the week		
		var weekday = this.getDay();
		return this.add(Date.DAY, -weekday);
	},

	calculateDaysBetweenDates:function(secondDate) {

		var tz1 = this.getTimezoneOffset();
		var tz2 = secondDate.getTimezoneOffset();

		var firstTime = this.getTime();
		var secondTime = secondDate.getTime();

		var timeinmilisec = false;
		var useMinus = false;
		var result = false;

		if(firstTime < secondTime){
			timeinmilisec = secondTime - firstTime;
			useMinus = true;
		} else {
			timeinmilisec = firstTime - secondTime;
		}

		if(tz1 < tz2 || tz1 == tz2){
			result = Math.ceil(timeinmilisec / (1000 * 60 * 60 * 24));
			if(useMinus)
				result = Math.floor(timeinmilisec / (1000 * 60 * 60 * 24));
		}else{
			result = Math.floor(timeinmilisec / (1000 * 60 * 60 * 24));
			if(useMinus)
				result = Math.ceil(timeinmilisec / (1000 * 60 * 60 * 24));
		}

		if(useMinus)
			result = '-'+result;

		return result;
	},
	
	/**
	 * Serialize the date to send to API
	 * 
	 * @returns {DateAnonym$0@call;format}
	 */
	serialize : function() {
		if(this.getHours() == 0 && this.getMinutes() == 0 && this.getSeconds() == 0) {
			//no time
			return this.format("Y-m-d");
		} else
		{
			return this.toISOString();
		}
	}


});

Ext.apply(Date, {
	/**
	 * Get date from given year and ISO week
	 * @static
	 * @param w
	 * @param y
	 * @returns {Date}
	 */
	fromISOWeek : function(w, y) {
		var simple = new Date(y, 0, 1 + (w - 1) * 7);
		var dow = simple.getDay();
		var ISOweekStart = simple;
		if (dow <= 4)
			ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
		else
			ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
		return ISOweekStart;
	}
});


