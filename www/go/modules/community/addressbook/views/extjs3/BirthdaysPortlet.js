go.modules.community.addressbook.BirthdaysPortlet = function (config) {
	if (!config) {
		config = {};
	}

	config.id = 'su-birthdays-grid';

	var reader = new Ext.data.JsonReader({
		totalProperty: 'total',
		root: 'results',
		fields: ['addressBookId', 'addressbook', 'photoBlobId', 'name', 'birthday', 'age'],
		id: 'name'
	});

	config.store = new Ext.data.GroupingStore({
		url: GO.url('addressbook/portlet/birthdays'),
		reader: reader,
		sortInfo: {
			field: 'addressBookId',
			direction: 'ASC'
		},
		groupField: 'addressBookId',
		remoteGroup: true,
		remoteSort: true
	});

	config.store.on('load', function () {
		//do layout on Startpage
		if (this.rendered)
			this.ownerCt.ownerCt.ownerCt.doLayout();
	}, this);


	config.paging = false,
		config.autoExpandColumn = 'birthday-portlet-name-col';
	config.autoExpandMax = 2500;
	config.enableColumnHide = false;
	config.enableColumnMove = false;
	config.columns = [
		{
			header: '',
			dataIndex: 'photoBlobId',

			renderer: function (value, metaData, record) {
				return  go.util.avatar(record.get('name'), record.data.photoBlobId, null);
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
			header: t("Address book", "addressbook"),
			// dataIndex: 'addressBookId',
			dataIndex: 'addressbook',
			sortable: true
		}, {
			header: t("Birthday", "addressbook"),
			dataIndex: 'birthday',
			width: 100,
			sortable: true,
			renderer: function(value, metaData, record) {
				return go.util.Format.date(value);
			}
		}, {
			header: t("Age"),
			dataIndex: 'age',
			sartable: false,
			width: 100
		}];
	config.view = new Ext.grid.GroupingView({
		scrollOffset: 2,
		hideGroupedColumn: true
	});
	config.sm = new Ext.grid.RowSelectionModel();
	config.loadMask = true;
	config.autoHeight = true;

	go.modules.community.addressbook.BirthdaysPortlet.superclass.constructor.call(this, config);

};

Ext.extend(go.modules.community.addressbook.BirthdaysPortlet, GO.grid.GridPanel, {

	saveListenerAdded: false,

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
			//iconCls: 'go-module-icon-tasks',
			title: t("Upcoming birthdays", "addressbook"),
			layout: 'fit',
			tools: [{
				id: 'gear',
				handler: function () {
					if (!this.selectAddressbookWin) {
						this.selectAddressbookWin = new GO.base.model.multiselect.dialog({
							url: 'addressbook/portlet',
							columns: [{header: t("Name"), dataIndex: 'name', sortable: true}],
							fields: ['id', 'name'],
							title: t("Birthdays", "addressbook"),
							model_id: 0,
							addAttributes: {userId: GO.settings.user_id},
							listeners: {
								hide: function () {
									birthdaysGrid.store.reload();
								},
								scope: this
							}
						});
					}
					this.selectAddressbookWin.show();
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
