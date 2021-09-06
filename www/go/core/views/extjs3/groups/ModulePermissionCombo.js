Ext.define('go.groups.ModulePermissionCombo',{
	extend: Ext.form.ComboBox,

	valueField:'id',
	displayField:'name',
	editable: false,
	mode:'local',
	triggerAction:'all',
	emptyText: t('None'),
	_checkField:'checked', // field name for checkbox
	selectedItems: [],
	allowNoSelection: true,

	listeners: {
		beforeselect: function (combo, record, index) {
			//debugger;
				var selectedNames = [],
				selectNone = false;
			// if(index === 0) {
			// 	selectNone = true;
			// 	selectedNames.push(t('Use'));
			// }

			var selected = [];

			combo.store.each(function (item) {

				//debugger;
				if(selectNone) {
					item.set(combo._checkField, false);
				}

				let val = item.get(combo._checkField);
				if(item.id == record.id) {
					val = !val;
					item.set(combo._checkField, val);
				}

				if(val) {
					selectedNames.push(item.data.name);
					selected.push(item.id);
				} else {

				}
			});
			combo.selectedItems = selected;
			//combo.fireEvent('filter', this, selected, combo.store.items);
			Ext.form.ComboBox.superclass.setValue.call(combo, selectedNames.join(', '));

			//combo.applyFilter(selected);
			return false; // prevent dropdown from closing
		}
	},
	// afterRender: function() {
	//
	// 	this.store.load();
	// 	this.callParent();
	// },

	initComponent: function() {

		// this.store = new GO.data.JsonStore({
		// 	url: GO.url("projects2/status/store"),
		// 	fields: ['id','name','checked'],
		// 	remoteSort: true,
		// 	baseParams:{
		// 		forEditing:true,
		// 		forFilterPanel:true,
		// 		limit:400
		// 	}
		// });

		this.store.on('load', function(store, records, options) {
			var selectedNames = [];
			for(var i = 0; i < records.length; i++) {
				if(records[i].data.checked) selectedNames.push(records[i].data.name);
			}
			//store.insert(0,new store.recordType({id:'none',name: t('Use')}));
			Ext.form.ComboBox.superclass.setValue.call(this, selectedNames.join(', '));
		}, this);

		this.tpl = new Ext.XTemplate(`
			<tpl for=".">
				<div class="x-combo-list-item">
				<i class="icon"><tpl if="values.id=='none'">clear</tpl><tpl if="values.id!='none' && values.checked">check</tpl></i> {name}
				</div>
			</tpl>
		`);

		this.addEvents({filter : true});

		this.callParent();
	}

});