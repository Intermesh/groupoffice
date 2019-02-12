/**
 * Usage:
 * 
 * {
								xtype: "combocolumn",
								store: new Ext.data.ArrayStore({
									id: 0,
									fields: [
										'value',
										'display'
									],
									data: [['work', t("Work")], ['home', t("Home")]]
								}),
								id: 'type',
								sortable: false,
								dataIndex: 'type',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								width: dp(100)

							}
 */
go.grid.editor.ComboColumn = Ext.extend(Ext.grid.Column, {
	constructor: function (config) {
		Ext.apply(this, config);

		this.editor = {
			xtype: 'combo',
			name: 'type',
			mode: 'local',
			editable: false,
			triggerAction: 'all',
			store: this.store,
			valueField: 'value',
			displayField: 'display'
		};

		var store = this.store;


		this.renderer = function (v, meta) {
			var r = store.getById(v);

			meta.style = "position:relative";
			meta.css = 'go-editable-col';
			return r ? r.get('display') + "<i class='trigger'>arrow_drop_down</i></div>" : v + "<i class='trigger'>arrow_drop_down</i></div>";
		};

		go.grid.editor.ComboColumn.superclass.constructor.call(this, config);
	}

});

Ext.grid.Column.types['combocolumn'] = go.grid.editor.ComboColumn;