go.form.RecipientCombo = Ext.extend(GO.form.ComboBoxMulti, {
	sep: ',',

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
						let curName = go.util.addSlashes(data.name);

						// Names with an apostrophe need not be escaped
						curName = curName.replace(/\\'/g, '\'');
						return '"' + curName + '" <' + data.email + '>';
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

		go.form.RecipientCombo.superclass.initComponent.call(this);

		let win;

		this.on({
			autosize: function (el, h, old) {

				if(!old) {
					return;
				}
				if(!win) {
					win = this.findParentByType("window");
				}

				// for some reason this is required twice when pasting multiple lines :(
				win.doLayout();
				win.doLayout();
			},
			scope: this
		}, this);


	}
});