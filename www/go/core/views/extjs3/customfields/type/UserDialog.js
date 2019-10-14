/* global go, Ext */

go.customfields.type.UserDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.customfields.type.UserDialog.superclass.initFormItems.call(this);

		return items;
	}
});
