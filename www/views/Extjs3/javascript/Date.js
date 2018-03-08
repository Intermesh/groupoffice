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
	}
});