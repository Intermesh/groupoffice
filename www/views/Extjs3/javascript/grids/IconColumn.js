GO.grid.IconColumn = Ext.extend(Ext.grid.Column, {
	emptyIcon: BaseHref + 'views/Extjs3/ext/resources/images/default/s.gif', // todo: change
	constructor: function(cfg) {
		//set required properties
		cfg.id = 'icon';
		cfg.width = dp(32);
		cfg.sortable = false;
		cfg.header = 'Icon';
		cfg.resizable = false;
		cfg.groupable = false;

		GO.grid.IconColumn.superclass.constructor.call(this, cfg);

		var emptyIcon = this.emptyIcon;
		this.renderer = function(v, metaData, record, rowIndex, colIndex, store) {
			metaData.css += ' go-grid-col-icon';
			if (!GO.util.empty(v)) {
				//todo check for valid BlobID
				return '<div style="background-image:url(' + v + ')"></div>';
			}
			return '<img src="'+emptyIcon+'" />';
		};
	}
});

Ext.grid.Column.types.iconcolumn = GO.grid.IconColumn;
