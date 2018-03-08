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
		return !!boolean ? GO.lang['yes'] : GO.lang['no'];
	},
	
	valuta : function(amount) {
		var value = GO.settings.currency + ' ' + GO.util.format.number(amount, 2);
		return value;
	},
	
	number : function(val, decimals) {
		
		if(isNaN(val))
			val = GO.util.unlocalizeNumber(val);
		return GO.util.numberFormat(val,decimals);
		// below was made before i know there was a GO.util.numberFormat
		decimals = decimals || 0;
		var power = Math.pow(10, decimals);
		val = Math.round(val*power)/power;
		var ds = GO.settings.decimal_separator,
			ts = GO.settings.thousands_separator;
		if(ds=='.')
			return val.toLocaleString();
		
		return val.toLocaleString().replace('.','%').replace(',',ts).replace('%', ds);
	},
	
	date : function(timestamp) {
		return Ext.util.Format.date(new Date(timestamp*1000), GO.settings.date_format);
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