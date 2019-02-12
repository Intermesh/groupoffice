/* global go, Ext */

go.modules.core.core.type.GroupDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.modules.core.core.type.GroupDialog.superclass.initFormItems.call(this);

		return items;
	}
});
