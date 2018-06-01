/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ListField.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * @class GO.form.ListField
 * @extends Ext.Component
 * A 1 column editable grid the contains a list of strings that can be added and removed
 * Group-Office personal settings
 * @constructor
 * Creates a new NumberField
 * @param {Object} config Configuration options
 */
GO.form.ListField = Ext.extend(GO.grid.EditorGridPanel, {
	
	/**
     * @cfg {String} name the post value name 
     */
	name : '',
    
	/**
     * @cfg {Array} list of string in the ListField
     */
	value : [],
	
	/**
	 * @cfg {Number} height default height of component
	 */
	height : 160,

	/**
	 * @cfg {Number} anchor test
	 */
	anchor : Number.MAX_VALUE,
		
	initComponent : function(){
		
		if(this.name) {
			this.hiddenField = new Ext.form.Hidden({
				name: this.name
			});
			var items = [this.hiddenField];
		}

		Ext.apply(this, {
			tbar: [{
				itemId:'add',
				iconCls: 'btn-add',							
				text: t("Add"),
				cls: 'x-btn-text-icon',
				handler: function(){
					this.addNewRow();
				},
				scope: this
			},{
				itemId:'delete',
				iconCls: 'btn-delete',
				text: t("Delete"),
				cls: 'x-btn-text-icon',
				handler: function(){
					this.deleteSelected();
				},
				scope: this
			}],
			store: new Ext.data.ArrayStore({
				idIndex: 0,
				fields:['value']
			}),
			viewConfig: {
				autoFill: true,
				forceFit:true
			},
			height: this.height,
			cm: new Ext.grid.ColumnModel({
				columns: [{
					sortable:false,
					groupable:false,
					id:'value',
					dataIndex: 'value',
					editor: new Ext.grid.GridEditor(new Ext.form.TextField())
				}]
			}),
			items: items || [],
			sm: new Ext.grid.RowSelectionModel()
		});

		this.setValue(this.value);
		
		GO.form.ListField.superclass.initComponent.call(this);
	},
	setValue: function(data) {
		this.value = data || [];
		var records = [];
		for (var i = 0; i < this.value.length; i++) {
			records.push([this.value[i]]);
		}
		this.store.loadData(records);
		this.update();
	},
	update : function() {
		if(this.name && this.hiddenField) {
			var value = [];
			for(var i = 0 ; i < this.store.data.items.length; i++) {
				var item = this.store.data.items[i];
				value.push(item.data.value);
			}
			var val = Ext.encode(value);
			this.hiddenField.setValue(val);
		}
	},
	deleteSelected : function(){
		var selection = this.selModel.getSelections()[0];
        if (selection) {
            this.store.remove(selection);
			this.update();
        }
	},
	addNewRow : function(){	

		this.stopEditing();
		var index = this.store.getCount();
		var sm=this.getSelectionModel();
		var rows=sm.getSelections();
		if(rows.length){
			index = this.store.indexOf(rows[rows.length-1])+1;			
		}
		//var previousRecord = this.store.getAt(index-1);

		//var record = Ext.data.Record.create([{value:''}]);
		
		var record = new GO.form.ListFieldRecord({
			value: ""
		});

		this.store.insert(index, record);
		var colIndex = this.getColumnModel().getIndexById('value');
		sm.selectRow(index);
		this.startEditing(index, colIndex);
	},
	onEditComplete : function(ed, value, startValue){
		GO.form.ListField.superclass.onEditComplete.call(this, ed, value, startValue);
		this.update();
	}
});

GO.form.ListFieldRecord = Ext.data.Record.create([
		{
			name: 'id',
			type: 'int'
		},{
			name: 'description',
			type: 'string'
		}
	]);

Ext.reg('listfield', GO.form.ListField);
