/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
go.modules.community.test.MainPanel = Ext.extend(go.modules.ModulePanel, {

	title: t("Test"),

	layout: "fit",

	initComponent: function () {

		go.modules.community.test.MainPanel.superclass.initComponent.call(this);

		this.on("afterrender", () => {
			goui("./go/modules/community/test/views/extjs3/js/GouiTest.js", this.body.dom);
		}, this);
	},

});

