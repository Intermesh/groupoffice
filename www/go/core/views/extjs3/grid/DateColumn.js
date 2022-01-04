/* global Ext, go */

go.grid.DateColumn = Ext.extend(Ext.grid.Column, {
	align: "right",
	dateOnly: false,
	constructor: function(cfg){


		var me = this;
		if(!cfg.renderer) {
			cfg.renderer = function (v, meta, record) {
				if (me.dateOnly) {
					return go.util.Format.date(v);
				}

				return go.util.Format.shortDateTimeHTML(v);
			};
		}

		Ext.grid.DateColumn.superclass.constructor.call(this, cfg);
		
		this.resizable = !go.User.shortDateInList;
		if(this.dateOnly) {
			this.width = dp(128);
		} else {
			this.width = go.User.shortDateInList ? dp(128) : dp(168);
		}
	}
});		

Ext.grid.Column.types.datecolumn = go.grid.DateColumn;

