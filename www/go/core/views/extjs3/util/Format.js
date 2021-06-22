/* global Ext, GO, go */

(function () {
	
	var checkDate = function(v) {
		if (!v) {
			return false;
		}
		var date;
		if (!Ext.isDate(v)) {
			//New framework uses "c"
			date = Date.parseDate(v, "c");
			if(!date) {
				//Try old formatted date and time
				date = Date.parseDate(v, go.User.dateTimeFormat);
			}
			
			if(!date) {				
				//Try old formatted date
				date = Date.parseDate(v, go.User.dateFormat);
			}
			
			//finally try unix timestamp
			if(!date) {				
				//Try old formatted date
				date = Date.parseDate(v, "U");
			}
		} else
		{
			date = v;
		}
		return date;
	};
	
	go.util.Format = {

		/**
		 * Parse a UTC date string and transform to the users's timezone
		 *
		 * @param v eg. "2021-05-25T11:00+00:00"
		 * @param format
		 * @return {boolean|*|Date}
		 */
		parseDateUserTZ: function(v, format) {
			var date = Date.parseDate(v, format);
			if(!date) {
				return false;
			}

			return this.dateToUserTZ(date);
				
		},

		/**
		 * Change date object to user timezone
		 *
		 * @param date
		 * @return {Date|*}
		 */
		dateToUserTZ : function(date) {
			if(Ext.isIE) {
				//sigh
				return date;
			}
			try {
				var local = date.toLocaleString("en-US", {timeZone: go.User.timezone});
			}
			catch(e) {
				console.error(e);
				return date;
			}
			return new Date(local);		
		},

		/**
		 * Change date object to browser's timezone so it will send a correct iso string
		 * to the server. eg. "2021-05-25T04:00:00+02:00".
		 *
		 * @param v
		 * @return {Date|*}
		 */
		dateToBrowserTZ : function(v) {		
			
			if(Ext.isIE) {
				//sigh
				return v;
			}

			var local = this.dateToUserTZ(v);
			var time = v.getTime();
			 
			var diff = time - new Date(local).getTime();

			var browsertz = new Date(time + diff);

			return browsertz;
		},

		htmlEncode  : function(v) {

			if(Ext.isArray(v)) {
				for(var i = 0, l = v.length; i < l; i++) {
					v[i] = this.htmlEncode(v[i]);
				}		
			} else if(Ext.isObject(v)) {
				for(var key in v) {
					v[key] = this.htmlEncode(v[key]);
				}
			} else if(Ext.isString(v)){
				v = Ext.util.Format.htmlEncode(v);
			}

			return v;
		},
		
		dateFormats: new Ext.data.ArrayStore({
						fields: ['format', 'label'],
						idIndex: 0,
						data : [
						['d-m-Y', t("Day-Month-Year",'users','core')],
						['m/d/Y', t("Month/Day/Year",'users','core')],
						['d/m/Y', t("Day/Month/Year",'users','core')],
						['d.m.Y', t("Day.Month.Year",'users','core')],
						['Y-m-d', t("Year-Month-Day",'users','core')],
						['Y.m.d', t("Year.Month.Day",'users','core')]
						]
					}),
					
		timeFormats: new Ext.data.ArrayStore({
						fields: ['format', 'label'],		
						idIndex: 0,
						data : [
						['G:i', t('24 hour format','users','core')],
						['h:i a', t('12 hour format','users','core')]
						]
					}),
		age: function(birthday) {
			birthday = checkDate(birthday);
			if(!birthday) {
				return "-";
			}
			 var ageDifMs = Date.now() - birthday.getTime();
			var ageDate = new Date(ageDifMs); // miliseconds from epoch
			return Math.abs(ageDate.getUTCFullYear() - 1970);
		},
		
		duration : function(seconds, pad, showSeconds) {
			var time = parseInt(seconds);
			var hours = Math.floor( time / 60 / 60);
			time -= hours * 3600;
			var minutes = Math.floor( time / 60);
			minutes = (minutes < 10) ? "0"+minutes : minutes;
			hours = (pad && hours < 10) ? "0"+hours : hours;
			if(showSeconds) {
				var seconds = time % 60;
				return hours+':'+minutes+':'+((seconds < 10) ? "0"+seconds : seconds);
			}
			return hours+':'+minutes;
		},

		durationSplit : function(seconds) {
			var hours = Math.floor( seconds / 3600);
			seconds -= hours * 3600;
			var minutes = Math.floor( seconds / 60);
			seconds -= minutes * 60;

			return {
				hours: hours,
				minutes: minutes,
				seconds: seconds
			};
		},

		timeRemaining : function(seconds) {
			var d = this.durationSplit(seconds);

			var str = "";

			if(d.hours) {
				return d.hours + ":" + (d.minutes < 10 ? "0" + d.minutes : d.minutes) + "h";
			}
			if(d.minutes) {
				return d.minutes + "m";
			}

			return d.seconds + "s";
		},

		//valid str format is 2:04, 08:00, (19:61 == 20:01)
		minutes: function(timeStr) {
			parts = timeStr.split(':');
			return parseInt(parts[0])*60+parseInt(parts[1]);
		},

		valuta : function(amount) {
			return go.User.currency + go.util.Format.number(amount, 2);
		},


		number : function(value, decimals) {
			if(isNaN(value))
				return value; // only localize number values

			var neg = value < 0;

			if(neg) {
				value *= -1;
			}
			
			var dec = go.User.decimalSeparator,
				tho = go.User.thousandsSeparator;

			if(decimals === undefined) {
				decimals = 2;
			}
			
			var no =  value.toFixed(decimals);
			var parts = no.split('.');

			var formatted = "";
			var length = parts[0].length;
			for(var i = length - 1, l = 0; i >= l; i--) {
				formatted = parts[0][i] + formatted;

				if(i > 0 && (length - i) % 3 == 0) {
					formatted = tho + formatted;
				}
			}

			if(decimals) {
				formatted += dec + parts[1];
			}

			if(neg) {
				formatted = "-" + formatted;
			}

			return formatted;
		},
		/**
		 * @param {string|Date} v
		 * @returns {String}
		 */
		date : function(v) {
			v = checkDate(v);
			if(!v) {
				return "";
			}
			return Ext.util.Format.date(v, GO.settings.date_format);
		},
		
			/**
		 * @param {string|Date} v
		 * @returns {String}
		 */
		time : function(v) {
			v = checkDate(v);
			if(!v) {
				return "";
			}
			v = this.dateToUserTZ(v);
			return Ext.util.Format.date(v, GO.settings.time_format);
		},
		
		// string, ...args
		// eg go.util.Format.string("Welcome {0}, good {1}", 'Michael', 'afternoon')
		string : function() {
			var args = arguments;
			var string = [].shift.call(args);
			return string.replace(/{(\d+)}/g, function(match, number) { 
			  return typeof args[number] != 'undefined' ? args[number] : match;
			});
		},

		userDateTime : function(v){
			return go.User.shortDateInList ? go.util.Format.shortDateTime(v) : go.util.Format.dateTime(v)
		},

		dateTime: function (v) {
			v = checkDate(v);
			if(!v) {
				return "";
			}

			v = this.dateToUserTZ(v);
			
			return Ext.util.Format.date(v, GO.settings.date_format + " " + GO.settings.time_format);
		},

		shortDateTimeHTML: function(v) {
			return '<span style="cursor:pointer" title="' + go.util.Format.dateTime(v) + '">' + go.util.Format.userDateTime(v) + '</span>';
		},

		shortDateTime: function (v, showTime, longNotation) {
			
			showTime?showTime:null;
			longNotation?longNotation:null;
			
			v = checkDate(v);
			if(!v) {
				return "-";
			}

			v = this.dateToUserTZ(v);

			var now = new Date(),
							nowYmd = parseInt(now.format("Ymd")),
							vYmd = parseInt(v.format("Ymd")),
							diff = vYmd - nowYmd;
			
			switch(diff) {
				case 0:
					return !showTime ? t('Today') : t('Today') + " " + t('at') + " " + Ext.util.Format.date(v, GO.settings.time_format.replace(/g/, "G").replace(/h/, "H"));
				case -1:
					return !showTime ? t('Yesterday') : t('Yesterday') + " " + t('at') + " " + Ext.util.Format.date(v, GO.settings.time_format.replace(/g/, "G").replace(/h/, "H"));
				case 1:
					return !showTime ? t('Tomorrow') : t('Tomorrow') + " " + t('at') + " " + Ext.util.Format.date(v, GO.settings.time_format.replace(/g/, "G").replace(/h/, "H"));
			}

			if(diff > -6 && diff < 6) {
				var str = !longNotation ? t('full_days')[v.getDay()] : t('full_days')[v.getDay()] + " " + v.getDate() + " " + t('short_months')[v.getMonth()+1];
				str += !showTime?"": " " + t('at') + " " + Ext.util.Format.date(v, GO.settings.time_format.replace(/g/, "G").replace(/h/, "H"));
				return str;
			}			

			if (now.getFullYear() === v.getFullYear()) {
				var dayIndex = GO.settings.date_format.indexOf('d'),
								monthIndex = GO.settings.date_format.indexOf('m');
				
				if(dayIndex == -1) {
					dayIndex = GO.settings.date_format.indexOf('j');
				}
				
				var str = !longNotation ? Ext.util.Format.date(v, dayIndex > monthIndex ? 'M j' : 'j M') : t('full_days')[v.getDay()] + " " + v.getDate() + " " + t('short_months')[v.getMonth()+1];
				str += !showTime?"": " " + t('at') + " " + Ext.util.Format.date(v, GO.settings.time_format.replace(/g/, "G").replace(/h/, "H"));
				return str;
			} else {
				var str = !longNotation ? Ext.util.Format.date(v, GO.settings.date_format) : t('full_days')[v.getDay()] + " " + v.getDate() + " " + t('short_months')[v.getMonth()+1] + " " + v.getFullYear();
				str += !showTime?"": " " + t('at') + " " + Ext.util.Format.date(v, GO.settings.time_format.replace(/g/, "G").replace(/h/, "H"));
				return str;
			}
		}
	};
})();
