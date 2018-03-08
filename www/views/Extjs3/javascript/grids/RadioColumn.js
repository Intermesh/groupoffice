/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: RadioColumn.js 14816 2013-05-21 08:31:20Z mschering $
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
GO.grid.RadioColumn = function(config){
	Ext.apply(this, config);
	if(!this.id){
		this.id = Ext.id();
	}
	this.renderer = this.renderer.createDelegate(this);
};

GO.grid.RadioColumn.prototype = {
	value : false,
	
	horizontal : false,
	/**
	 * @param {} grid passes Ext.grid.GridPanel.
	 */
	init : function(grid){
		this.grid = grid;
		this.grid.on('render', function(){
			var view = this.grid.getView();
			view.mainBody.on('mousedown', this.onMouseDown, this);
		}, this);
	},

	/**
	 * @param {} e passes the current event.
	 * @param {} t passes the table
	 */
	onMouseDown : function(e, t){
		if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
			
				e.stopEvent();
				var index = this.grid.getView().findRowIndex(t);
				var record = this.grid.store.getAt(index);
				var disabled = this.isDisabled(record);

				if (!disabled)
				{
					if(!this.horizontal){
						if(!GO.util.empty(record.get(this.dataIndex))) {
							return;
						}

						for(var i = 0, max = this.grid.store.getCount();i < max; i++) {
							var rec = this.grid.store.getAt(i);
							if(rec.get(this.dataIndex)) {
								rec.set(this.dataIndex, false);
							}

						}
						record.set(this.dataIndex, true);
					}else{
						record.set(this.dataIndex, this.value);
					}
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
};