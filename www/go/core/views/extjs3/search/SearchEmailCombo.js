/* global Ext, go, GO */

/**
 * 
 * @type |||
 */
go.search.SearchEmailCombo = Ext.extend(go.form.ComboBox, {	
	emptyText: t("Search..."),
	pageSize: 20,
	valueField: 'entityId',
	displayField: 'email',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,	
	initComponent: function () {
		
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
			
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['entityId', 'name', 'entity', 'type', 'email', 'photoBlobId'],				
				method: "Search/email"				
			})
		});

		go.search.SearchEmailCombo.superclass.initComponent.call(this);

	}
});

Ext.reg("searchemailcombo", go.search.SearchEmailCombo);
