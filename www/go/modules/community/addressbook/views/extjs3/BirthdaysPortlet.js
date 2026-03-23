go.modules.community.addressbook.BirthdaysPortlet = Ext.extend(go.grid.GridPanel, {

	initComponent : function() {
		this.id = 'addressbook-birthdays-portlet';

		this.store = new go.data.Store({
			autoDestroy: true,
			fields: [
				'id',
				'addressBookId',
				'name',
				'birthday',
				'age',
				'photoBlobId',
				'dates',
				{name: "addressbook", type: "relation"},
				{name: 'addressbookName', mapping: "addressbook.name"},
			],
			entityStore: "Contact",
			autoLoad: false,
			sortInfo: {
				field: 'upcomingBirthday',
				direction: 'ASC'
			}
		});

		this.store.setFilter('isOrganisation', {isOrganization: false})
			.setFilter('birthday', {birthday: 'now..30 days'});
		if (go.User.birthdayPortletAddressBooks && go.User.birthdayPortletAddressBooks.length > 0) {
			console.log("birthdayPortletAddressBooks " , go.User.birthdayPortletAddressBooks);
			this.store.setFilter('addressBookId', {addressBookId: go.User.birthdayPortletAddressBooks})
		}

		this.paging = false;
		this.autoExpandColumn = 'birthday-portlet-name-col';
		this.autoExpandMax = 2500;
		this.enableColumnHide = false;
		this.enableColumnMove = false;

		this.columns = [
			{
				header: '',
				dataIndex: 'photoBlobId',

				renderer: function (value, metaData, record) {
					return go.util.avatar(record.get('name'), record.data.photoBlobId, null);
				}
			}, {
				id: 'birthday-portlet-name-col',
				header: t("Name"),
				dataIndex: 'name',
				sortable: false
			}, {
				header: t("Address book"),
				dataIndex: 'addressbookName',
				sortable: false
			}, {
				id: 'birthday',
				header: t('dateTypes')['birthday'],
				sortable: false,
				dataIndex: "birthday",
				renderer: function(v, meta, record) {
					let bdate = "";
					record.data.dates.forEach(function(date) {
						if(date.type === "birthday") {
							bdate = date.date;
						}
					});
					return go.util.Format.date(bdate);
				}
			}, {
				header: t("Age"),
				dataIndex: 'age',
				sortable: false,
				width: 100,
				renderer: function(v, meta, record) {
					let bdate = "";
					record.data.dates.forEach(function(date) {
						if(date.type === "birthday") {
							bdate = date.date;
						}
					});
					const today = new Date(), bdt = new Date(bdate);
					if(today.getUTCDate() !== bdt.getUTCDate() || today.getUTCMonth() !== bdt.getUTCMonth()) {
						v++
					}
					return v;
				}
			}];

		this.sm = new Ext.grid.RowSelectionModel();
		this.loadMask = true;
		this.autoHeight = true;

		this.viewConfig =	{
			emptyText: '<i>cake</i><p>' + t("No items to display") + '</p>'
		};


		this.supr().initComponent.call(this);

		this.on("rowclick", function(grid, index, e) {
			go.Entities.get("Contact").goto(grid.store.getAt(index).id);
		}, this);

	},

	initCustomFields : function() {

	},

	afterRender: function () {
		go.modules.community.addressbook.BirthdaysPortlet.superclass.afterRender.call(this);

		this.store.load();
	},

	getBirthDate: function(record) {

		return bdate;
	}
});


GO.mainLayout.onReady(function () {
	if (go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("community", "addressbook")) {
		var birthdaysGrid = new go.modules.community.addressbook.BirthdaysPortlet();

		GO.summary.portlets['portlet-birthdays'] = new GO.summary.Portlet({
			id: 'portlet-birthdays',
			iconCls: 'ic-cake',
			title: t("Upcoming birthdays", "addressbook", "community"),
			layout: 'fit',
			tools: [{
				id: 'gear',
				handler: function () {
					var dlg = new go.modules.community.addressbook.BirthdaysPortletSettingsDialog({
						listeners: {
							hide: function () {
								setTimeout(function() {
									if (go.User.birthdayPortletAddressBooks && go.User.birthdayPortletAddressBooks.length > 0) {
										birthdaysGrid.store.setFilter('addressBookId', {addressBookId: go.User.birthdayPortletAddressBooks})
									}
									birthdaysGrid.store.reload();
								})
							},
							scope: this
						}
					})
					dlg.load(go.User.id).show();
				}
			}, {
				id: 'close',
				handler: function (e, target, panel) {
					panel.removePortlet();
				}
			}],
			items: birthdaysGrid,
			autoHeight: true
		});
	}
});
