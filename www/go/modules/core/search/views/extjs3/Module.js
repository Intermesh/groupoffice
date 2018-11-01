Ext.ns('go.modules.community.search');

go.Modules.register("core", 'search', {
	//mainPanel: GO.notes.MainPanel,
	entities: ["Search"],
	initModule: function () {
   
		GO.mainLayout.on('render', function () {

			var container, searchField, searchContainer, panel;

			var search = function () {
					
					panel.setWidth(searchField.getWidth());
					panel.setHeight(dp(500));
					panel.getEl().alignTo(searchField.getEl(), "tl-bl");
					panel.search(searchField.getValue());
			},
			
			enableSearch = function() {
				searchContainer.show();
				searchField.focus();	

				if (!panel) {
					panel = new go.modules.community.search.Panel({
						searchContainer : searchContainer
					});
					panel.render(Ext.getBody());
					panel.on("collapse", function() {
						searchField.setValue("");
						searchContainer.hide();
					});
				};
			}
			
			var dqTask = new Ext.util.DelayedTask(search);

			container = new Ext.Container({
				id: 'global-search-panel',
				items: [{
						xtype: 'button',
						iconCls: 'ic-search',
						tooltip: t("Search"),
						handler: function () {
							enableSearch();
						},
						scope: this
					},
					searchContainer = new Ext.Container({
						hidden: true,
						cls: 'search-field-wrap',
						items: [
							searchField = new Ext.form.TriggerField({								
								emptyText: t("Search"),
								hideLabel: true,
								anchor: "100%",
								validationEvent: false,
								validateOnBlur: false,
								//trigger1Class: 'x-form-search-trigger',
								triggerClass: 'x-form-clear-trigger',
								enableKeyEvents: true,
// private
								
//								onTrigger1Click: function () {
//									search();
//								},
								onTriggerClick: function () {
									this.setValue("");
									search();
								},
								listeners: {
//									keyup : function(field, e) {
//										if(e.getKey() != e.ESC) {
//											dqTask.delay(500);
//										}
//									},
									specialkey: function (field, e) {
										switch (e.getKey()) {
											case e.ESC:
												panel.collapse();
//											
//											case e.ENTER:
//												search();
//												break;
											case e.DOWN:
												if (panel.isVisible()) {
													panel.grid.getSelectionModel().selectRow(0);
													panel.grid.getView().focusRow(0);
												}
												break;
										}
									}
								}
							})

						]})
				],
				renderTo: "search_query"
			});
			
			
			searchField.getEl().on("input", function() {
				dqTask.delay(500);	
			});
			
			
			//Global accessor to search with go.searchField.setValue("test");
			go.util.search = function(query) {
				enableSearch();
				searchField.setValue(query);
				search();
			}




		});

	}
});


