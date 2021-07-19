/* global go, t, Ext, GO */

go.modules.community.addressbook.ContactGrid = Ext.extend(go.grid.GridPanel, {
	cls: 'x-grid3-no-row-borders',
	initComponent: function () {

		if(!this.enabledColumns) {
			this.enabledColumns = ['name', 'organizations'];
		}

		if(!go.User.addressBookSettings) {
			go.User.addressBookSettings = {
				sortBy: "firstName"
			};
		}

		this.store = new go.data.Store({
			fields: [
				'id',
				{
					name: 'name',
					sortType: Ext.data.SortTypes.asUCString,
					type: 'string',
					convert: function(name, data) {
						return go.modules.community.addressbook.renderName(data);
					}
				},
				'firstName',
				'middleName',
				'lastName',
				{name: 'createdAt', type: 'date'},
				{name: 'modifiedAt', type: 'date'},
				{name: 'creator', type: "relation"},
				{name: 'modifier', type: "relation"},
				{name: 'addressbook', type: "relation"},
				// 'starred',
				'permissionLevel',
				'photoBlobId',
				"isOrganization",
				"emailAddresses",
				"phoneNumbers",
				"dates",
				"birthday", //dummy
				"actionDate", //dummy
				"gender",
				"streetAddresses",
				{name: 'organizations', type: "relation"},
				"jobTitle",
				"department",
				"debtorNumber",
				"registrationNumber",
				"IBAN",
				"vatNo",
				"color"
			],
			sortInfo :{field: go.User.addressBookSettings.sortBy, direction: "ASC"},
			entityStore: "Contact"
		});
		
		var grid = this;


		function getIndexChar(record, rowIndex) {
			var sortBy = record.data.isOrganization ? "name" : go.User.addressBookSettings.sortBy;

			var sortState = grid.store.getSortState();
			if(sortState.field != "name" && sortState.field != "firstName"  && sortState.field != "lastName") {
				return "";
			}

			//sometimes the field is null.
			if(!Ext.isString(record.data[sortBy])) {
				return "";
			}

			var lastRecord = rowIndex > 0 ? grid.store.getAt(rowIndex - 1) : false;
			var lastSortBy = !lastRecord || !lastRecord.data.isOrganization ? go.User.addressBookSettings.sortBy : "name" ;

			var char = record.data[sortBy].substr(0, 1).toUpperCase();
			if(!lastRecord || !lastRecord.data[lastSortBy] || lastRecord.data[lastSortBy].substr(0, 1).toUpperCase() !== char) {
				return "<h3>" + char + "</h3>";
			}

			return "";
		}

		Ext.apply(this, {

			columns: [
				{
					width: dp(48),
					id: "index",
					dataIndex: "name",
					sortable: false,
					draggable: false,
					hideable: false,
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {

						if(!value) {
							return "";
						}

						return getIndexChar(record, rowIndex);

					}
				},
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: dp(60),
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					width: dp(300),
					sortable: true,
					dataIndex: go.User.addressBookSettings.sortBy,
					hidden: this.enabledColumns.indexOf('name') == -1,
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {

						const icon = record.data.isOrganization ? '<i class="icon">business</i>' : null;

						if(record.get("color")) {
							metaData.attr = 'style="color: #' + record.get("color") + ';"';
						}

						return '<span class="go-ab-avatar">' + go.util.avatar(value, record.data.photoBlobId, icon) + '</span>' + value;

					}
				},
				{
					hidden: this.enabledColumns.indexOf('gender') == -1,
					header: t('Gender'),
					width: dp(160),
					sortable: true,
					dataIndex: 'gender',
					renderer: function (v) {
						if(v === 'M') {
							return t("Male", 'addressbook');
						} else if (v === 'F') {
							return t("Female", 'addressbook');
						}
						return "";
					}
				},
				{
					id: 'organizations',
					header: t('Organizations'),
					sortable: false,
					dataIndex: "organizations",
					width: dp(300),
					hidden: this.enabledColumns.indexOf('organizations') == -1,
					renderer: function (organizations, meta, record) {
						return organizations.column("name").join(", ");
					}
				},
				{
					id: 'addressbook',
					header: t('Address Book'),
					sortable: false,
					dataIndex: "addressbook",
					renderer: function(v) {
						return v.name;
					},
					width: dp(200),
					hidden: this.enabledColumns.indexOf('addressbook') == -1
				},
				{
					xtype: "datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'createdAt',

					hidden: this.enabledColumns.indexOf('createdAt') == -1
				},
				{
					xtype: "datecolumn",
					hidden: this.enabledColumns.indexOf('modifiedAt') == -1,
					id: 'modifiedAt',
					header: t('Modified at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifiedAt'
				},
				{
					hidden: this.enabledColumns.indexOf('creator') == -1,
					header: t('Created by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'creator',
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				},
				{
					hidden: this.enabledColumns.indexOf('modifier') == -1,
					header: t('Modified by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifier',
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				}, {
					hidden: this.enabledColumns.indexOf('jobTitle') == -1,
					header: t('Job title'),
					width: dp(160),
					sortable: true,
					dataIndex: 'jobTitle'
				}, {
					hidden: this.enabledColumns.indexOf('department') == -1,
					header: t('Department'),
					width: dp(160),
					sortable: true,
					dataIndex: 'department'
				},  {
					hidden: this.enabledColumns.indexOf('registrationNumber') == -1,
					header: t('Registration number'),
					width: dp(160),
					sortable: true,
					dataIndex: 'registrationNumber'
				},{
					hidden: this.enabledColumns.indexOf('debtorNumber') == -1,
					header: t('Debtor number'),
					width: dp(160),
					sortable: true,
					dataIndex: 'debtorNumber'
				}, {
					hidden: this.enabledColumns.indexOf('IBAN') == -1,
					header: "IBAN",
					width: dp(160),
					sortable: true,
					dataIndex: 'IBAN'
				}, {
					hidden: this.enabledColumns.indexOf('vatNo') == -1,
					header: t("VAT number"),
					width: dp(160),
					sortable: true,
					dataIndex: 'vatNo'
				},
				{
					id: 'phoneNumbers',
					header: t('Phone numbers'),
					sortable: false,
					dataIndex: "phoneNumbers",
					width: dp(300),
					hidden: this.enabledColumns.indexOf('phoneNumbers') == -1,
					renderer: function (phoneNumbers, meta, record) {
						return phoneNumbers.column("number").join(", ");
					}
				},
				{
					id: 'emailAddresses',
					header: t('E-mail addresses'),
					sortable: false,
					dataIndex: "emailAddresses",
					width: dp(300),
					hidden: this.enabledColumns.indexOf('emailAddresses') == -1,
					renderer: function (emailAddresses, meta, record) {
						return emailAddresses.column("email").join(", ");
					}
				},{
					id: 'firstName',
					header: t('First name'),
					sortable: true,
					dataIndex: "firstName",
					hidden: this.enabledColumns.indexOf('firstName') == -1,
				},{
					id: 'middleName',
					header: t('Middle name'),
					sortable: true,
					dataIndex: "middleName",
					hidden: this.enabledColumns.indexOf('middleName') == -1,
				},{
					id: 'lastName',
					header: t('Last name'),
					sortable: true,
					dataIndex: "lastName",
					hidden: this.enabledColumns.indexOf('lastName') == -1,
				},{
					id: 'birthday',
					header: t('dateTypes')['birthday'],
					sortable: true,
					dataIndex: "birthday",
					renderer: function(v, meta, record) {
						if(!record.data.dates) {
							return "";
						}
						var bday = "";
						record.data.dates.forEach(function(date) {
							if(date.type == "birthday") {
								bday = date.date;
							}
						});

						return go.util.Format.date(bday);
					},
					hidden: this.enabledColumns.indexOf('birthday') == -1,
				},{
					id: 'actionDate',
					header: t('Action date'),
					sortable: true,
					dataIndex: "actionDate",
					renderer: function(v, meta, record) {
						if(!record.data.dates) {
							return "";
						}
						var bday = "";
						record.data.dates.forEach(function(date) {
							if(date.type == "action") {
								bday = date.date;
							}
						});

						return go.util.Format.date(bday);
					},
					hidden: this.enabledColumns.indexOf('actionDate') == -1,
				}
			],
			viewConfig: {
				totalDisplay: true,
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				getRowClass: function (record, rowIndex, p, store) {

					const char = getIndexChar(record, rowIndex);

					return char ? 'go-addressbook-index-row' : '';
				}
			},
			// config options for stateful behavior
			stateful: true,
			stateId: 'contact-grid'
		});
		

		go.modules.community.addressbook.ContactGrid.superclass.initComponent.call(this);
	},

	applyState: function(state) {

		this.supr().applyState.call(this, state);

		var sort = this.store.getSortState();
		if(!sort) {
			return;
		}

		// If user changed sort preference in my account then change the saved sort state
		if((sort.field == 'name' || sort.field == 'lastName') && go.User.addressBookSettings.sortBy != sort.field) {
			this.store.setDefaultSort(go.User.addressBookSettings.sortBy, sort.direction);
		}

	},
	

	//when filtering on a group then offer to delete contacts from a group when delting.
	deleteSelected: function () {

		var filter = this.store.getFilter('addressbooks');
		if (!filter || !filter.groupId) {
			return go.grid.GridTrait.deleteSelected.call(this);
		}

		var groupId = filter.groupId;

		var selectedRecords = this.getSelectionModel().getSelections(), ids = selectedRecords.column('id'), strConfirm;

		switch (ids.length)
		{
			case 0:
				return;
			case 1:
				strConfirm = t("Are you sure you want to delete the selected item?");
				break;

			default:
				strConfirm = t("Are you sure you want to delete the {count} items?").replace('{count}', ids.length);
				break;
		}

		Ext.Msg.show({
			title: t("Confirm delete"),
			msg: t(strConfirm),
			buttons: {ok: t("Remove from group"), yes: t("Delete"), "cancel": t("Cancel")},
			fn: function (btn) {

				if (btn === "yes") {
					this.getStore().entityStore.set({
						destroy: ids
					});
				}

				if (btn ==="ok") {
					var updates = {}, me = this;

					go.Db.store("Contact").get(ids).then(function(result) {
						result.entities.forEach(function (contact) {
							var groupIndex = contact.groups.indexOf(groupId);
//							console.log(groupIndex, groupId, r.json.groups);
							updates[contact.id] = {
								groups: go.util.clone(contact.groups)
							};
							updates[contact.id].groups.splice(groupIndex, 1);
						});

						me.getStore().remove(selectedRecords);

						me.getStore().entityStore.set({
							update: updates
						});
					});

				}
			},
			scope: this,
			icon: Ext.MessageBox.QUESTION
		});
	}
});

