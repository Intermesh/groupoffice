/* global Ext, go */

go.grid.DateColumn = Ext.extend(Ext.grid.Column, {
	align: "right",
	dateOnly: false,
	constructor: function(cfg){		
		Ext.grid.DateColumn.superclass.constructor.call(this, cfg);

		var me = this;
		this.renderer = function(v) {
			if(me.dateOnly) {
				return go.util.Format.date(v);
			}
			
			return go.User.shortDateInList ? go.util.Format.shortDateTime(v) : go.util.Format.dateTime(v);
		};
		
		this.resizable = !go.User.shortDateInList;
		if(this.dateOnly) {
			this.width = dp(128);
		} else {
			this.width = go.User.shortDateInList ? dp(128) : dp(168);
		}
	}
});		

Ext.grid.Column.types.datecolumn = go.grid.DateColumn;

