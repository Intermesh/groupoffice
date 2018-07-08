/**
 * Client side formatting functions 
 */
 
GO.util.format = {
 
	duration : function(minutes) {
		var time = GO.util.unlocalizeNumber(minutes); //needed because default SUM summary will localize the number and formating to time wont work
		var hours = Math.floor( time / 60);          
		var minutes = time % 60;
		minutes = (minutes < 10) ? "0"+minutes : minutes;
		return hours+':'+minutes;
	},
	
	yesNo : function(boolean) {
		return !!boolean ? t("Yes") : t("No");
	},
	
	valuta : function(amount) {
		var value = GO.settings.currency + ' ' + GO.util.format.number(amount, 2);
		return value;
	},
	
	number : function(val, decimals) {
		
		if(isNaN(val))
			val = GO.util.unlocalizeNumber(val);
		return GO.util.numberFormat(val,decimals);
	},
	
	date : function(timestamp) {
		return Ext.util.Format.date(new Date(timestamp*1000), GO.settings.date_format);
	},
	
	smartDate : function(timestamp) {
		now = new Date();
		time = new Date(timestamp*1000);
		if(now.format('Ymd') === time.format('Ymd')) {
			return time.format(GO.settings.time_format);
		} else if (now.getFullYear() === time.getFullYear()) {
			return time.format('j M');
		} else {
			return time.format(GO.settings.date_format);
		}
	},
	
	timeAgo : function(timestamp) {

		var delta = Math.round((+new Date - timestamp*1000) / 1000);
		var minute = 60,
			 hour = minute * 60,
			 day = hour * 24,
			 week = day * 7;
  
		if (delta < 30) {
			 return t('just now');
		} else if (delta < minute) {
			 return String.format(t('{0} seconds ago'),delta);
		} else if (delta < 2 * minute) {
			 return t('a minute ago');
		} else if (delta < hour) {
			 return String.format(t('{0} minutes ago'), Math.floor(delta / minute));
		} else if (Math.floor(delta / hour) == 1) {
			 return t('1 hour ago');
		} else if (delta < day) {
			 return String.format(t('{0} hours ago'),Math.floor(delta / hour));
		} else if (delta < day * 2) {
			 return t('yesterday');
		} else if (delta < week) {
			return String.format(t('{0} days ago'),Math.floor(delta / day));
		} else if (Math.floor(delta / week) == 1) {
			return t('1 week ago');
		} else {
			return GO.util.format.date(timestamp);
		}
	},
	
	fileSize: function(bytes) {
		if(bytes==0)
			return '0B';
		var i = Math.floor( Math.log(bytes) / Math.log(1024) );
		return ( bytes / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
	},
	capitalize : function(s){		
		return s && s[0].toUpperCase() + s.toLowerCase().slice(1);
	}
};
