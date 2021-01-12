/* global go, t, Ext, GO */

go.modules.community.addressbook.ContactGrid = Ext.extend(go.grid.GridPanel, {
	cls: 'x-grid3-no-row-borders',
	initComponent: function () {

		if(!go.User.addressBookSettings) {
			go.User.addressBookSettings = {
				sortBy: "firstName"
			};
		}

		this.store = new go.data.Store({
			fields: [
				'id',
				'name',
				'firstName',
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

						var sortBy = record.data.isOrganization ? "name" : go.User.addressBookSettings.sortBy;						

						// if(rowIndex === 0 && value) {
						// 	return '<div class="icon ic-star go-addressbook-star"></div>';
						// } else
						// {

							if(!value) {
								return "";
							}

							var sortState = store.getSortState();
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
						// }
						
						return "";
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
					sortable: true,
					dataIndex: go.User.addressBookSettings.sortBy,
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {

						var icon = record.data.isOrganization ? '<i class="icon">business</i>' : null;
						var sortBy = go.User.addressBookSettings.sortBy, name;
						if(!record.data.isOrganization && sortBy == 'lastName' && !go.util.empty(record.data.lastName)) {
							name = record.data.lastName + ', ' + record.data.firstName;
						} else{
							name = record.get('name');
						}

						if(record.get("color")) {
							metaData.attr = 'style="color: #' + record.get("color") + ';"';
						}

						return '<span class="go-ab-avatar">' + go.util.avatar(record.get('name'), record.data.photoBlobId, icon) + '</span>' + Ext.util.Format.htmlEncode(name);

						// return '<div class="avatar ' + cls + '" style="' + style + '">'+content+'</div>' + Ext.util.Format.htmlEncode(name);
					}
				},
				{
					hidden: true,
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
					hidden: true
				},
				{
					xtype: "datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{
					xtype: "datecolumn",
					hidden: true,
					id: 'modifiedAt',
					header: t('Modified at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifiedAt'
				},
				{
					hidden: true,
					header: t('Created by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'creator',
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				},
				{
					hidden: true,
					header: t('Modified by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifier',
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				}, {
					hidden: true,
					header: t('Job title'),
					width: dp(160),
					sortable: true,
					dataIndex: 'jobTitle'
				}, {
					hidden: true,
					header: t('Department'),
					width: dp(160),
					sortable: true,
					dataIndex: 'department'
				},  {
					hidden: true,
					header: t('Registration number'),
					width: dp(160),
					sortable: true,
					dataIndex: 'registrationNumber'
				},{
					hidden: true,
					header: t('Debtor number'),
					width: dp(160),
					sortable: true,
					dataIndex: 'debtorNumber'
				}, {
					hidden: true,
					header: "IBAN",
					width: dp(160),
					sortable: true,
					dataIndex: 'IBAN'
				}, {
					hidden: true,
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
					hidden: true,
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
					hidden: true,
					renderer: function (emailAddresses, meta, record) {
						return emailAddresses.column("email").join(", ");
					}
				},{
					id: 'firstName',
					header: t('First name'),
					sortable: true,
					dataIndex: "firstName",
					hidden: true
				},{
					id: 'lastName',
					header: t('Last name'),
					sortable: true,
					dataIndex: "lastName",
					hidden: true
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
					hidden: true
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
					hidden: true
				}
			],
			viewConfig: {
				totalDisplay: true,
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
//				enableRowBody: true,
//				showPreview: true,
				getRowClass: function (record, rowIndex, p, store) {					
					
					// if(rowIndex === 0 && record.get("starred")) {
					// 	return '';
					// } else
					// {
					// 	if(record.get("starred")) {
					// 		return "";
					// 	}

						var lastRecord = rowIndex > 0 ? grid.store.getAt(rowIndex - 1) : false;
						var char = record.data.name.substr(0, 1);
						if(!lastRecord || lastRecord.data.name.substr(0, 1) !== char) {
							return 'go-addressbook-index-row';
						}
						return "";
					// }
				}
			},
			autoExpandColumn: 'name',
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

