go.modules.community.addressbook.BirthdaysPortlet = Ext.extend(go.grid.GridPanel, {

	initComponent : function() {
		this.id = 'addressbook-birthdays-portlet';

		//this.addressBookIds = go.User.birthdayPortletAddressBooks;

		this.store = new go.data.GroupingStore({
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
				field: 'birthday',
				direction: 'ASC'
			},
			groupField: 'addressbookName',
			remoteGroup: true,
			remoteSort: true
		});

		this.store.setFilter('addressBookIds', {addressBookIds: go.User.birthdayPortletAddressBooks})
			.setFilter('isOrganisation', {isOrganization: false})
			.setFilter('birthday', {birthday: '< 30 days'});
		this.store.load().then(function (result) {
			// this.store.data = result;
			if (this.rendered) {
				this.ownerCt.ownerCt.ownerCt.doLayout();
			}
		}, this);

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
				sortable: false,
				renderer: function (value, metaData, record) {
					return '<a href="#contact/' + record.json.id + '">' + value + '</a>';
				}
			}, {
				header: t("Address book"),
				dataIndex: 'addressbookName',
				sortable: true
			}, {
				id: 'birthday',
				header: t('dateTypes')['birthday'],
				sortable: true,
				dataIndex: "birthday",
				renderer: function(v, meta, record) {

					var bday = "";
					record.data.dates.forEach(function(date) {
						if(date.type == "birthday") {
							bday = date.date;
						}
					});

					return go.util.Format.date(bday);
				},
				hidden: true
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
		this.view = new Ext.grid.GroupingView({
			scrollOffset: 2,
			hideGroupedColumn: true
		});
		this.sm = new Ext.grid.RowSelectionModel();
		this.loadMask = true;
		this.autoHeight = true;


		this.supr().initComponent.call(this);
	},

	saveListenerAdded: false,

	initCustomFields : function() {

	},
	afterRender: function () {
		go.modules.community.addressbook.BirthdaysPortlet.superclass.afterRender.call(this);

		Ext.TaskMgr.start({
			run: function () {
				this.store.load();
			},
			scope: this,
			interval: 960000
		});
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
