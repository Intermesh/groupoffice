/* global go, Ext */

go.modules.core.core.type.UserDialog = Ext.extend(go.modules.core.core.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.modules.core.core.type.UserDialog.superclass.initFormItems.call(this);

		return items;
	}
});
