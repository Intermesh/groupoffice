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

		go.grid.DateColumn.superclass.constructor.call(this, cfg);

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


go.grid.NumberColumn = Ext.extend(Ext.grid.Column, {
	align: "right",
	decimals: 2,
	constructor: function(cfg){
		var me = this;
		if(!cfg.renderer) {
			cfg.renderer = function (v, meta, record) {
				return go.util.Format.number(v, me.decimals);
			};
		}

		go.grid.NumberColumn.superclass.constructor.call(this, cfg);
	}
});

Ext.grid.Column.types.numbercolumn = go.grid.NumberColumn;


go.grid.ValutaColumn = Ext.extend(Ext.grid.NumberColumn, {
	align: "right",

	constructor: function(cfg){
		var me = this;
		if(!cfg.renderer) {
			cfg.renderer = function (v, meta, record) {
				return go.util.Format.valuta(v, me.decimals);
			};
		}

		go.grid.NumberColumn.superclass.constructor.call(this, cfg);
	}
});

Ext.grid.Column.types.valutacolumn = go.grid.ValutaColumn;