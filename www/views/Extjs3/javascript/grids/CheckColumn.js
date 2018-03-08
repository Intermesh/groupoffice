/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CheckColumn.js 19993 2016-04-21 14:48:03Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
 
GO.grid.CheckColumn = Ext.extend(Ext.grid.Column, {

		disabled_field : 'disabled',
		
		initComponent : function(){
			this.groupable=false;
			this.menuDisabled=true;
			this.checkboxClickOnly = false;
			
			GO.grid.CheckColumn.superclass.initComponent.call(this);
		},
    /**
     * @private
     * Process and refire events routed from the GridView's processEvent method.
     */
    processEvent : function(name, e, grid, rowIndex, colIndex){
        if (name == 'click') {
					
						if(this.checkboxClickOnly){
							
							var clickedEl = e.getTarget();
							
							if(clickedEl.className != 'x-grid3-check-col-on' && clickedEl.className != 'x-grid3-check-col'){
								return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
							}
						}
					
            var record = grid.store.getAt(rowIndex);
            
            if (!this.isDisabled(record))
            {
           		var newValue = GO.util.empty(record.data[this.dataIndex])?true:false;
           		record.set(this.dataIndex, newValue);
           		
           		this.fireEvent('change', record, newValue);
            }
            
            return false; // Cancel row selection.
        } else {
            return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
        }
    },
		
		isDisabled: function(record) {
			return record.get(this.disabled_field);
		},

    renderer : function(v, p, record){
        p.css += ' x-grid3-check-col-td'; 
				
        var disabledCls='';
        if(this.isDisabled(record))
					disabledCls =' x-item-disabled';
		
				return String.format('<div class="x-grid3-check-col{0}'+disabledCls+'">&#160;</div>', !GO.util.empty(v) ? '-on' : '');
    },

    // Deprecate use as a plugin. Remove in 4.0
    init: Ext.emptyFn
});
 
//GO.grid.CheckColumn = function(config){
//    Ext.apply(this, config);
//    if(!this.id){
//        this.id = Ext.id();
//    }
//
//		this.groupable=false;
//		this.menuDisabled=true;
//    this.renderer = this.renderer.createDelegate(this);
//    
//    this.addEvents({'change' : true});
//    
//    GO.grid.CheckColumn.superclass.constructor.call(this);
//};
//
//Ext.extend(GO.grid.CheckColumn, Ext.util.Observable, {
//
//		disabled_field : 'disabled',
//	
//    init : function(grid){
//        this.grid = grid;
//        this.grid.on('render', function(){
//            var view = this.grid.getView();
//            view.mainBody.on('mousedown', this.onMouseDown, this);
//        }, this);
//    },
//
//    onMouseDown : function(e, t){
//        if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
//            e.stopEvent();
//            var index = this.grid.getView().findRowIndex(t);
//            var record = this.grid.store.getAt(index);
//            
//            var disabled = record.get(this.disabled_field);
//            
//            if (!disabled)
//            {
//           		var newValue = record.data[this.dataIndex]=='1' ? '0' : '1';
//           		record.set(this.dataIndex, newValue);
//           		
//           		this.fireEvent('change', record, newValue);
//            }
//        }
//    },
//
//    renderer : function(v, p, record){
//        p.css += ' x-grid3-check-col-td';
//        
//        var disabled = record.get(this.disabled_field);
//
//        var on;
//        if (!GO.util.empty(v))
//        {
//        	if (disabled)
//        	{
//        		on = '-on x-item-disabled';
//        	} else {
//        		on = '-on';
//        	}
//        } else {
//        	if (disabled)
//        	{
//        		on = ' x-item-disabled';
//        	} else {
//        		on = '';
//        	}
//        }
//        
//        return '<div class="x-grid3-check-col'+ on +' x-grid3-cc-'+this.id+'">&#160;</div>';
//    }
//});