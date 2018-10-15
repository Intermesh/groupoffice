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
						['H:i', t('24 hour format','users','core')],
						['h:i a', t('12 hour format','users','core')]
						]
					}),
		
		/**
		 * 
		 * 
		 * @param {string|Date} v
		 * @returns {String}
		 */
		date : function(v) {
			v = checkDate(v);
			if(!v) {
				return "-";
			}
			return Ext.util.Format.date(v, GO.settings.date_format);
		},

		dateTime: function (v) {
			v = checkDate(v);
			if(!v) {
				return "-";
			}
			
			return Ext.util.Format.date(v, GO.settings.date_format + " " + GO.settings.time_format);
		},

		shortDateTime: function (v) {
			v = checkDate(v);
			if(!v) {
				return "-";
			}

			var now = new Date(),
							nowYmd = parseInt(now.format("Ymd")),
							vYmd = parseInt(v.format("Ymd"));

			if (nowYmd === vYmd) {
				return Ext.util.Format.date(v, GO.settings.time_format.replace(/g/, "G").replace(/h/, "H"));
			}

			if (nowYmd - 1 === vYmd) {
				return t('Yesterday');
			}

			if (nowYmd + 1 === vYmd) {
				return t('Tomorrow');
			}

			if (now.getFullYear() === v.getFullYear()) {
				var dayIndex = GO.settings.date_format.indexOf('d'),
								monthIndex = GO.settings.date_format.indexOf('m');
				
				if(dayIndex == -1) {
					dayIndex = GO.settings.date_format.indexOf('j');
				}

				return Ext.util.Format.date(v, dayIndex > monthIndex ? 'M j' : 'j M');
			} else
			{
				return Ext.util.Format.date(v, GO.settings.date_format);
			}
		}
	};
})();