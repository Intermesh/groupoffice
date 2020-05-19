/* global go, Ext, GO */



	var panels = [];

	go.util.SelectDialog = Ext.extend(go.Window, {
		entities: null,
		layout: "fit",
		width: dp(1000),
		height: dp(800),
		modal: true,
		query: "",
		singleSelect: false,
		mode: "email", // or "id" in the future "phone" or "address"
		title: t("Select people"),
		selectMultiple: function (ids, entityName) {

		},

		selectSingleEmail: function (name, email, id, entityName) {

		},

		scope: null,


		/**
		 * Panel must implement "selectsingle" event,
		 * "EntityName" string property,
		 * function addAll() returning Promise with ids
		 * function addSelection() returning Promise with ids
		 * 
		 * @param {Ext.Panel} pnl 
		 */
		
		addSelection : function(pnl) {
			var me = this;
			pnl.addSelection().then(function (ids) {
				me.selectMultiple.call(me.scope, ids, me.tabPanel.getActiveTab().entityName);
				me.close();
			}).catch(function() {
				me.close();
			});
		},

		initComponent: function () {

			if (!this.scope) {
				this.scope = this;
			}

			if(!this.singleSelect) {
				this.bbar = [
					'->',
					{
						text: t("Add all results"),
						handler: function () {
							var me = this;
							this.tabPanel.getActiveTab().addAll().then(function (ids) {

								me.selectMultiple.call(me.scope, ids, me.tabPanel.getActiveTab().entityName);
								me.close();
							}).catch(function(reason) {
								debugger;
								me.close();
							});
						},
						scope: this
					},
					this.addSelectionButton = new Ext.Button({
						text: t("Add selection"),
						handler: function () {
							this.addSelection(this.tabPanel.getActiveTab());
						},
						scope: this
						// disabled: true
					})
				];
			} else
			{
				this.bbar = [
					'->',
					
					this.addSelectionButton = new Ext.Button({
						text: t("Select"),
						handler: function () {
							this.addSelection(this.tabPanel.getActiveTab());
						},
						scope: this
						// disabled: true
					})
				];
			}

			this.tabPanel = new Ext.TabPanel({
				defaults: {
					autoScroll: true,
					hideMode: "offsets"
				},
				activeTab: Ext.state.Manager.get('select-dialog-tab') || 0,
				enableTabScroll: true
			});

			this.tabPanel.on('tabchange', function(tabpanel, tab) {
				Ext.state.Manager.set('select-dialog-tab', tabpanel.items.indexOf(tab));
			},this);

			this.loadModulePanels();

			if(!this.tabPanel.getActiveTab()) {
				this.tabPanel.setActiveTab(0);
			}

			this.items = [this.tabPanel];

			go.util.SelectDialog.superclass.initComponent.call(this);
		},

		loadModulePanels : function() {
			var available = go.Modules.getAvailable(), config, pnl, i, i1, sepAdded = false;
			
			for(i = 0, l = available.length; i < l; i++) {
				
				config = go.Modules.getConfig(available[i].package, available[i].name);
				
				if(!config.selectDialogPanels) {
					continue;
				}
				
				if(available[i].package != 'core' && !sepAdded) {
					// this.selectMenu.addSeparator();
					// sepAdded = true;
				}
				
				for(i1 = 0, l2 = config.selectDialogPanels.length; i1 < l2; i1++) {
					pnl = eval(config.selectDialogPanels[i1]);				
					var p = new pnl({
						singleSelect: this.singleSelect,
						query: this.query,
						mode: this.mode
					});
					p.dialog = this;

					if(this.entities && this.entities.indexOf(p.entityName) == -1) {
						continue;
					}

					p.on('selectsingle', function (pnl, name, email, id) {
						this.selectSingleEmail.call(this.scope, name, email, id, pnl.entityName);
						this.close();
					}, this);

					this.tabPanel.add(p);
				}
			}
		},


	});

