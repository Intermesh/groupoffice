Ext.ns('go.core.search');

go.ModuleManager.register('search', {
	//mainPanel: GO.notes.MainPanel,
	entities: ["Search"],
	initModule: function () {

		GO.mainLayout.on('render', function () {
			
			
			var resultTpl = new Ext.XTemplate(
       
					'<tpl for="."><div class="x-combo-list-item">',
						'<i class="entity {entity}"></i> <span>{name}</span>',
        '</div>',
				'</tpl>'

    );
			
			new Ext.Container({
				id: 'global-search-panel',
				items: [{
						xtype: 'button',
						iconCls: 'ic-search',
						tooltip: t("Search"),
						handler: function () {
							this.searchContainer.show();
							this.searchField.focus();
						},
						scope: this
					},
					this.searchContainer = new Ext.Container({
						hidden: true,
						cls: 'search-field-wrap',
						items: [{
								xtype: 'component',
								html: '<i class="icon">search</i>'
							},
							this.searchField = new Ext.form.ComboBox({
								hideTrigger: true,
								maxHeight: dp(1000),
								getParams : function(q) {
									var p = Ext.form.ComboBox.prototype.getParams.call(this, q);
									
									p.filter = [{
										q: q
									}]
									
									return p;
								},
								typeAHead: true,
								editable: true,
								valueField: 'id',
								displayField: 'name',
								tpl: resultTpl,
//								itemSelector: 'div.search-item',
								store: new go.data.Store({
									fields: ['name', 'entity', 'entityId', 'modifiedAt'],
									entityStore: go.stores.Search
								}),
								emptyText: t("Search") + '...',
								listeners: {
									scope: this,
									blur: function (field) {
										field.reset();
										this.searchContainer.hide();
									},
									select: function (cb, record, index) {
										var e = go.entities[record.data.entity];
										e.goto(record.data.entityId);
										this.searchField.reset();
										this.searchContainer.hide();
									}
								}
							})
						]})
				],
				renderTo: "search_query"
			});
		});

	}
});


