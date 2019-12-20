GO.grid.IconColumn = Ext.extend(Ext.grid.Column, {
	constructor: function(cfg) {
		//set required properties
		cfg.id = 'icon';
		cfg.width = dp(40);
		cfg.sortable = false;
		cfg.header = '&nbsp;';
		cfg.resizable = true;
		cfg.groupable = false;

		GO.grid.IconColumn.superclass.constructor.call(this, cfg);

		this.renderer = function(v, metaData, record, rowIndex, colIndex, store) {
			metaData.css += ' go-grid-col-icon';
			if (!GO.util.empty(v)) {
				//todo check for valid BlobID
				return '<div style="background-image:url(' + v + ')"></div>';
			}
			return '';
		};
	}
});

Ext.grid.Column.types.iconcolumn = GO.grid.IconColumn;
