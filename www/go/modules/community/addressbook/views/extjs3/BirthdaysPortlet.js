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

		this.store.setFilter('addressBookIds', {addressBookIds: go.User.birthdayPortletAddressBooks})
			.setFilter('isOrganisation', {isOrganization: false})
			.setFilter('birthday', {birthday: 'now..30 days'});

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

					var bday = "";
					record.data.dates.forEach(function(date) {
						if(date.type == "birthday") {
							bday = date.date;
						}
					});

					return go.util.Format.date(bday);
				}
			}, {
				header: t("Age"),
				dataIndex: 'age',
				sortable: false,
				width: 100,
				renderer: function(v, meta, record) {

					var birthDate;
					record.data.dates.forEach(function(date) {
						if(date.type == "birthday") {
							birthDate = new Date(date.date);
						}
					});

					var today = new Date();
					var age = today.getFullYear() - birthDate.getFullYear();
					// var m = today.getMonth() - birthDate.getMonth();
					// if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
					// 	age--;
					// }
					return age;

				}
			}];

		this.sm = new Ext.grid.RowSelectionModel();
		this.loadMask = true;
		this.autoHeight = true;

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
	}
});


GO.mainLayout.onReady(function () {
	if (go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("community", "addressbook")) {
		var birthdaysGrid = new go.modules.community.addressbook.BirthdaysPortlet();

		GO.summary.portlets['portlet-birthdays'] = new GO.summary.Portlet({
			id: 'portlet-birthdays',
			iconCls: 'ic-cake',
			title: t("Upcoming birthdays", "addressbook"),
			layout: 'fit',
			tools: [{
				id: 'gear',
				handler: function () {
					var dlg = new go.modules.community.addressbook.BirthdaysPortletSettingsDialog({
						listeners: {
							hide: function () {
								setTimeout(function() {
									birthdaysGrid.store.setFilter('addressBookIds', {addressBookIds: go.User.birthdayPortletAddressBooks})
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
