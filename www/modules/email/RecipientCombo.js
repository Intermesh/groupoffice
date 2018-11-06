GO.email.RecipientCombo = Ext.extend(GO.form.ComboBoxMulti, {
	sep: ',',
	fieldLabel: t("To", "email"),
	name: 'to',
	flex: 1,
	valueField: 'full_email',
	displayField: 'name',

	initComponent: function () {

		this.store = new go.data.Store({
			method: "Search/email",
			fields: ['id', 'entity', 'email', 'name', 'photoBlobId',
				{
					name: "full_email",
					convert: function (v, data) {						
						return '"' + data.name.replace(/"/g, '\\"') + '" <' + data.email + '>';
					}
				}]
		});
		
		this.tpl = new Ext.XTemplate(
					'<tpl for=".">',
					'<div class="x-combo-list-item"><div class="user">\
							 <tpl if="!photoBlobId"><div class="avatar"></div></tpl>\\n\
							 <tpl if="photoBlobId"><div class="avatar" style="background-image:url({[go.Jmap.downloadUrl(values.photoBlobId)]})"></div></tpl>\
							 <div class="wrap">\
								 <div>{email}</div><small style="color:#333;">{name}</small>\
							 </div>\
						 </div></div>',
					'</tpl>'
					);
	
		GO.email.RecipientCombo.superclass.initComponent.call(this);

		this.on({
			grow: function () {
				this.findParentByType("window").doLayout();
			},
			scope: this
		}, this);


	}
});