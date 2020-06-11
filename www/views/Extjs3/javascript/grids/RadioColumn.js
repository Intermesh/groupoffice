/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: RadioColumn.js 22361 2018-02-12 18:42:16Z mschering $
 * @copyright Copyright Intermesh
 * @author Danny Wijffelaars <dwijffelaars@intermesh.nl>
 */

/**
 * @class GO.grid.RadioColumn
 * @extends Ext.util.Observable
 *
 * Creates new RadioColumn plugin
 * @constructor
 * @param {Object} config The config object
 */
GO.grid.RadioColumn = Ext.extend(Ext.grid.Column, {

	constructor: function(config) {


		this.supr().constructor.call(this, config);

		if(!config.id){
			config.id = Ext.id();
		}
		this.renderer = this.renderer.createDelegate(this);

		this.on("mousedown", this.onMouseDown, this);
	},

	init : function() {

	},

	value : false,
	
	horizontal : false,
	
	
	menuDisabled: true,
	
	sortable: false,
	hideable: false,
	
	align: "center",
	
	isColumn: true,

	/**
	 * @param {} e passes the current event.
	 * @param {} t passes the table
	 */
	onMouseDown : function(col, grid, rowIndex, e){


				// e.stopEvent();
				var record = grid.store.getAt(rowIndex);
				var disabled = this.isDisabled(record);

				if (!disabled)
				{
					if(!this.horizontal){
						if(!GO.util.empty(record.get(this.dataIndex))) {
							return;
						}

						for(var i = 0, max = grid.store.getCount();i < max; i++) {
							var rec = grid.store.getAt(i);
							if(rec.get(this.dataIndex)) {
								rec.set(this.dataIndex, false);
							}

						}
						record.set(this.dataIndex, true);
					}else{
						record.set(this.dataIndex, this.value);
					}
				}
	},



	/**
	 * This function can be overwritten
	 * @param {} record
	 */
	isDisabled : function(record){
		return record.data[this.disabled_field];
	},

	/**
	 * This function makes the radiobutton (not) selected or (not) disabled
	 * @param {} v passes the value of the checkbox it is currently linked to
	 * @param {} p passes the panel
	 * @param {} record passes the current record from the store of the checkbox
	 */
	renderer : function(v, p, record){
		p.css += ' x-grid3-radio-col-td';
		var disabled = this.isDisabled(record);
		var on='';
		
		if(this.horizontal)
			on = v==this.value ? '-on' : '';		
		else
			on = !GO.util.empty(v) ? '-on' : '';				
		
		if (disabled)
			on += ' x-item-disabled';

		return '<div class="x-grid3-radio-col'+ on +' x-grid3-cc-'+this.id+'">&#160;</div>';
	}
});

