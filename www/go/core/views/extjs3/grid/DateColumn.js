/* global Ext, go */

go.grid.DateColumn = Ext.extend(Ext.grid.Column, {
	align: "right",
	dateOnly: false,
	shortDate: null,
	constructor: function(cfg){


		var me = this;
		if(!cfg.renderer) {
			cfg.renderer = function (v, meta, record) {
				if (me.dateOnly) {
					return go.util.Format.date(v);
				}

				return me.shortDate ? go.util.Format.shortDateTimeHTML(v) : go.util.Format.dateTime(v);
			};
		}

		Ext.grid.DateColumn.superclass.constructor.call(this, cfg);

		if(this.shortDate === null) {
			this.shortDate = go.User.shortDateInList;
		}

		if(this.dateOnly) {
			this.width = dp(128);
		} else {
			this.width = this.shortDate ? dp(128) : dp(168);
		}
	}
});		

Ext.grid.Column.types.datecolumn = go.grid.DateColumn;

