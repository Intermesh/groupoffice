/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CategoryDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
go.modules.community.bookmarks.CategoryDialog = Ext.extend(go.form.Dialog, {

	title:t("Category"),
	entityStore: "BookmarksCategory",
	height:450,
	width: 500,

	initFormItems : function () {
        this.addPanel(new go.permissions.SharePanel());

		return [{
			xtype: 'fieldset',
			items: [
				{
					xtype: 'textfield',
					name: 'name',
					fieldLabel: t("Name"),
					anchor: '100%',
					required: true
				}
			]
		}];
	}
});
