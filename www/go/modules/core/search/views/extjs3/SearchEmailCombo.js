/* global Ext, go, GO */

/**
 * 
 * @type |||
 */
go.modules.core.search.SearchEmailCombo = Ext.extend(go.form.ComboBox, {	
	emptyText: t("Search..."),
	pageSize: 20,
	valueField: 'id',
	displayField: 'email',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,	
	initComponent: function () {
		
		this.tpl = new Ext.XTemplate(
				 '<tpl for=".">',
				 '<div class="x-combo-list-item">{email}<br /><small style="color:#333;">{name}</small></div>',
				 '</tpl>'
			);
			
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name', 'entity', 'type', 'email'],				
				method: "Search/email"				
			})
		});

		go.modules.core.search.SearchEmailCombo.superclass.initComponent.call(this);

	}
});

Ext.reg("searchemailcombo", go.modules.core.search.SearchEmailCombo);
