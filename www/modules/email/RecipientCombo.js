GO.email.RecipientCombo = Ext.extend(GO.form.ComboBoxMulti, {
	sep: ',',
	fieldLabel: t("To", "email"),
	name: 'to',
	flex: 1,
	queryDelay: 300,
	valueField: 'full_email',
	displayField: 'full_email',
	hideTrigger: true,
	textarea: true,

	initComponent: function () {

		this.store = new go.data.Store({
			method: "Search/email",
			fields: ['entityId', 'entity', 'email', 'name', 'photoBlobId','extra',
				{
					name: "full_email",
					convert: function (v, data) {						
						return '"' + go.util.addSlashes(data.name) + '" <' + data.email + '>';
					}
				}]
		});
		
		this.tpl = new Ext.XTemplate(
					'<tpl for=".">',
					'<div class="x-combo-list-item"><div class="user">\
							 <tpl if="!photoBlobId"><div class="avatar"></div></tpl>\\n\
							 <tpl if="photoBlobId"><div class="avatar" style="background-image:url({[go.Jmap.thumbUrl(values.photoBlobId, {w: 40, h: 40, zc: 1}) ]})"></div></tpl>\
							 <div class="wrap">\
								 <div>{email}</div><small>{name}<tpl if="extra"> ({extra})</tpl></small>\
							 </div>\
						 </div></div>',
					'</tpl>'
					);
	
		GO.email.RecipientCombo.superclass.initComponent.call(this);

		this.on({
			autosize: function () {
				const win = this.findParentByType("window");

				// for some reason this is required twice when pasting multiple lines :(
				win.doLayout();
				win.doLayout();
			},
			scope: this
		}, this);


	}
});