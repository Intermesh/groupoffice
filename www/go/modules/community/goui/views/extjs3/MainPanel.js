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
go.modules.community.goui.MainPanel = Ext.extend(go.modules.ModulePanel, {

	title: t("GOUI Demo"),

	layout: "fit",

	initComponent: function () {

		go.modules.community.goui.MainPanel.superclass.initComponent.call(this);

		this.on("afterrender", () => {
			goui("./go/modules/community/goui/views/extjs3/build/Notes.js", this.body.dom);
		}, this);
	},

});

