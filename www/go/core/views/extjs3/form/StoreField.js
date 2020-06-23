
go.form.StoreField = Ext.extend(Ext.form.Field, {
	
	store: null,
	name : null,
	hidden: true,
	
	constructor: function (config) {

		config = config || {};
		go.form.StoreField.superclass.constructor.call(this, config);

	},


	isFormField: true,

	getName: function() {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	},
	
	

	setValue: function (records) {
	
		
		this._isDirty = false; //todo this is not right but works for our use case
		
		var data = {};
		data[this.store.root] = records;
		
			
		console.log(data);
		this.store.loadData(data);
	},


	getValue: function () {		
		var records = this.store.getRange(), v = [];
		for(var i = 0, l = records.length; i < l; i++) {
			v.push(records[i].data);
		}
		return v;
	},

	markInvalid: function () {

	},
	clearInvalid: function () {

	},
	
	validate : function() {
		return true;
	},

	isValid: function(preventMark) {
		return true;
	}
});

Ext.reg("storefield", go.form.StoreField);
