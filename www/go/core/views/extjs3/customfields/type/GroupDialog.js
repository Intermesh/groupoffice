/* global go, Ext */

go.customfields.type.GroupDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.customfields.type.GroupDialog.superclass.initFormItems.call(this);

		return items;
	}
});
