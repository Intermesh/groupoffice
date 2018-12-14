/* global go, Ext */

go.modules.core.customfields.type.GroupDialog = Ext.extend(go.modules.core.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.modules.core.customfields.type.GroupDialog.superclass.initFormItems.call(this);

		return items;
	}
});
