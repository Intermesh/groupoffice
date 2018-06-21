/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: SelectAddressbook.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.SelectAccount = function (config) {

	config = config || {};

	if (!config.hiddenName)
		config.hiddenName = 'account_id';

	if (!config.fieldLabel)
	{
		config.fieldLabel = t("E-mail Account", "email");
	}

	Ext.apply(config, {
		fieldLabel: t("E-mail Account", "email"),
		anchor:'-20',
		emptyText:t("Please select..."),
		store: new GO.data.JsonStore({
			url: GO.url("email/account/store"),
			fields: ['id', 'username'],
			remoteSort: true
		}),
		valueField:'id',
		displayField:'username',
		typeAhead: true,
		mode: 'remote',
		triggerAction: 'all',
		editable: false,
		selectOnFocus:true,
		forceSelection: true
	});

	GO.email.SelectAccount.superclass.constructor.call(this, config);

}
Ext.extend(GO.email.SelectAccount, GO.form.ComboBox, {
	setValue: function (id) {

		if (!id) {
			GO.email.SelectAccount.superclass.setValue.call(this, id);
			return;
		}
		var r = this.findRecord(this.valueField, id);

		if (!r)
		{
			GO.request({
				url: 'email/account/load',
				params: {id: id},
				success: function (response, options, result) {

					var comboRecord = Ext.data.Record.create([{
							name: this.valueField
						}, {
							name: this.displayField
						}]);

					var recordData = {};

					if (this.store.fields && this.store.fields.keys) {
						for (var i = 0; i < this.store.fields.keys.length; i++) {
							recordData[this.store.fields.keys[i]] = "";
						}
					}

					recordData[this.valueField] = id;
					recordData[this.displayField] = result.data.username;

					var currentRecord = new comboRecord(recordData);
					this.store.add(currentRecord);
					GO.email.SelectAccount.superclass.setValue.call(this, id);
				},
				scope: this
			});
		} else
		{
			GO.email.SelectAccount.superclass.setValue.call(this, id);
		}


	}
});



