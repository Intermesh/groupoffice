(function() {
	var cfg = {
		fieldLabel: t("User"),
		hiddenName: 'userId',
		anchor: '100%',
		// emptyText: t("Please select..."),
		pageSize: 50,
		valueField: 'id',
		displayField: 'name',
		triggerAction: 'all',
		editable: true,
		selectOnFocus: true,
		forceSelection: true,
		allowBlank: false,
		store: {
			xtype: 'gostore',
			fields: ['id', 'name', 'description', 'avatarId'],
			entityStore: "Principal",
			sortInfo: {
				field: "name"
			},
			filters: {entity: {entity: 'User'}}
		},
		tpl: '<tpl for=".">\
			<div class="x-combo-list-item">\
				<div class="user">\
					 <tpl if="!avatarId"><div class="avatar"></div></tpl>\
					 <tpl if="avatarId"><div class="avatar" style="background-image:url({[go.Jmap.thumbUrl(values.avatarId, {w: 40, h: 40, zc: 1})]})"></div></tpl>\
					 <div class="wrap">\
						 <div>{name}</div><small>{description}</small>\
					 </div>\
				 </div>\
			 </div>\
			</tpl>',

		initComponent: function() {

			if(!( "value" in this.initialConfig)) {
				this.value = go.User.id;
			}
			this.supr().initComponent.call(this);
		}
	};

	go.users.UserCombo = Ext.extend(go.form.ComboBox, cfg);
	go.users.UserComboReset = Ext.extend(go.form.ComboBoxReset, cfg);

	Ext.reg("usercombo", go.users.UserCombo);

	Ext.reg("usercomboreset", go.users.UserComboReset);

})();



