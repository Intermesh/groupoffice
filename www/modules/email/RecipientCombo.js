GO.email.RecipientCombo = Ext.extend(GO.form.ComboBoxMulti, {
	sep: ',',
	fieldLabel: t("To", "email"),
	name: 'to',
	flex: 1,
	valueField: 'full_email',
	displayField: 'full_email',

	initComponent: function () {

		this.store = new go.data.Store({
			method: "Search/email",
			fields: ['entityId', 'entity', 'email', 'name', 'photoBlobId',
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
							 <tpl if="photoBlobId"><div class="avatar" style="background-image:url({[go.Jmap.thumbUrl(values.photoBlobId, {w: 40, h: 40, zc: 1}) ]})"></div></tpl>\
							 <div class="wrap">\
								 <div>{email}</div><small style="color:#333;">{name}</small>\
							 </div>\
						 </div></div>',
					'</tpl>'
					);
	
		GO.email.RecipientCombo.superclass.initComponent.call(this);

		this.on({
			autosize: function () {
				var win = this.findParentByType("window");

				// for some reason this is required twice when pasting multiple lines :(
				win.doLayout();
				win.doLayout();
			},
			scope: this
		}, this);


	}
});