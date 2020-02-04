/* global go, Ext, GO */
GO.email.AddressListGrid = Ext.extend(go.grid.GridPanel, {

	mode: "email", // or "id" in the future "phone" or "address"
	entityName: "Contact",
	title: t("Address lists","email"),

	getColumnChecks: function() {
		return this.allChecks;
	},
	initComponent: function () {

		if(this.singleSelect) {
			this.disabled = true;
		}

		this.searchField = new go.SearchField({
			anchor: "100%",
			handler: function(field, v){
				this.search(v);
			},
			emptyText: null,
			scope: this,
			value: this.query
		});

		Ext.apply(this, {

			tbar: new Ext.Toolbar({
				layout: "fit",
				items: [{
					xtype: 'fieldset',
					layout: 'fit',
					items: [this.searchField]
				}]


			}),
			autoScroll: true,
			store: new go.data.Store({
				fields: [
					'id',
					'name'
				],

				entityStore: "AddressList"
			}),
			columns: [
				this.checkColumn = new GO.grid.CheckColumn({
					id:'percentageComplete',
					dataIndex: 'percentageComplete',
					hideInExport:true,
					header: '<i class="icon ic-check"></i>',
					width: dp(56),
					hideable:false,
					menuDisabled: true,
					sortable:false,
					groupable:false,
					disabled: true
				}),
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'name'
				}
			],
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			},
		});


		this.allChecks = [];
		this.checkColumn.on('change', function(record, checked){
			if(checked) {
				this.allChecks.push(record.data.id);
			} else {
				var index = this.allChecks.indexOf(record.data.id);

				if (index !== -1)  {
					this.allChecks.splice(index, 1);
				}
			}
		}, this);

		// filters: {
		// 	"defaults": {
		// 		entity: {
		// 			id: 42,
		// 			name: "Contact"
		// 		}
		// 	}
		// }
		var me = this;

		go.Db.store("Contact").query({
			filter: {
				email: this.email,
				permissionLevel: go.permissionLevels.write
			},
			limit: 1
		}).then(function(result) {
			// contact found
			if(result.ids.length) {
				var contact = go.Db.store("Contact").single(result.ids[0]).then(function(contact) {
					var contactId = contact.id;
					if(me.delete) {
						// only show available address lists
						me.store.setFilter("entity", {
							"entity": {
								id: contactId,
								name: "Contact"
							}
						});
						me.store.load();
					} else {
						me.store.setFilter("entity", {
							"exclude":contact.addressLists ?  Object.keys(contact.addressLists) : []
						});
						me.store.load();
					}
				});
			}
		});



		// this.cstore.setFilter("contact",{
		// 	contact: 42
		// });
		go.modules.business.newsletters.SelectDialogPanel.superclass.initComponent.call(this);

		this.on("render", function () {
			this.search();
		}, this);

		this.on("show", function() {
			this.searchField.focus();
		}, this);
	},
	search : function(v) {
		this.store.setFilter("search", v ? {text: v} : null);
		this.store.load();
		this.searchField.focus();
	},


	addAll: function () {
		// var me = this;
		// var promise = new Promise(function (resolve, reject) {

		// 	var s = go.Db.store("User");
		// 	me.getEl().mask(t("Loading..."));
		// 	s.query({
		// 		filter: me.grid.store.baseParams.filter
		// 	}, function (response) {
		// 		me.getEl().unmask();
		// 		Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to select all {count} results?").replace('{count}', response.ids.length), function (btn) {
		// 			if (btn != 'yes') {
		// 				reject();
		// 			}
		// 			resolve(response.ids);
		// 		}, me);

		// 	}, me);
		// });

		// return promise;
	},

	addSelection: function () {
		var me = this;
		var records = this.getSelectionModel().getSelections();

		var promise = new Promise(function(resolve, reject) {

			var s = go.Db.store("Contact");
			me.getEl().mask(t("Loading..."));
			s.query({
				filter: {
					hasEmailAddresses: true,
					addressListId: records.column('id')
				}
			}, function(response) {
				me.getEl().unmask();
				resolve(response.ids);

			}, me);
		});

		return promise;
	}

});
