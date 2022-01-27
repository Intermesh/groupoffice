/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CheckColumn.js 22399 2018-02-19 14:45:39Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


GO.grid.CheckColumn = Ext.extend(Ext.grid.Column, {

	disabled_field: 'disabled',
	width: dp(40),
	initComponent: function () {
		this.groupable = false;
		this.menuDisabled = true;
		this.checkboxClickOnly = false;

		GO.grid.CheckColumn.superclass.initComponent.call(this);
		
		this.addEvents('change');
	},
	/**
	 * @private
	 * Process and refire events routed from the GridView's processEvent method.
	 */
	processEvent: function (name, e, grid, rowIndex, colIndex) {
		if (name == 'click') {

			if (this.checkboxClickOnly) {

				var clickedEl = e.getTarget();

				if (clickedEl.className != 'x-grid3-check-col-on' && clickedEl.className != 'x-grid3-check-col') {
					return GO.grid.CheckColumn.superclass.processEvent.apply(this, arguments);
				}
			}

			var record = grid.store.getAt(rowIndex);

			if(!record) {
				return false;
			}

			if (!this.isDisabled(record))
			{
				var newValue = GO.util.empty(record.data[this.dataIndex]) ? true : false;
				record.set(this.dataIndex, newValue);

				this.fireEvent('change', record, newValue);
			}

			return false; // Cancel row selection.
		} else {
			return GO.grid.CheckColumn.superclass.processEvent.apply(this, arguments);
		}
	},

	isDisabled: function (record) {
		return record.get(this.disabled_field);
	},

	renderer: function (v, p, record) {
		p.css += ' x-grid3-check-col-td';

		var disabledCls = '';
		if (this.isDisabled(record))
			disabledCls = ' x-item-disabled';

		return String.format('<div class="x-grid3-check-col{0}' + disabledCls + '"></div>', !GO.util.empty(v) ? '-on' : '');
	},

	// Deprecate use as a plugin. Remove in 4.0
	init: Ext.emptyFn
});

Ext.grid.Column.types.checkcolumn = GO.grid.CheckColumn;