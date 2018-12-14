/* global go, Ext */

go.modules.core.customfields.type.UserDialog = Ext.extend(go.modules.core.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.modules.core.customfields.type.UserDialog.superclass.initFormItems.call(this);

		return items;
	}
});
