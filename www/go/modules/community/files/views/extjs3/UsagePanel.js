/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

go.modules.community.files.UsagePanel = Ext.extend(Ext.Panel, {

	usage: 0,
	quota: 0,
	
	padding:dp(16),
	html:'quota',
	height:dp(48),
	
	initComponent: function () {
		
		go.Stores.get("User").get([go.User.id], function(entities){
			this.setData(entities[0].storage.usage,entities[0].storage.quota);
		},this);

		go.modules.community.files.UsagePanel.superclass.initComponent.call(this);
	},
	
	setData : function(usage, quota){
		this.usage = usage;
		this.quota = quota;
		this.updateHtml();
	},
	
	updateHtml : function(){
		var html = t('%usage of %quota used');
		
//		html = html.replace("%usage", Ext.util.Format.fileSize(this.usage));
//		html = html.replace("%quota", Ext.util.Format.fileSize(this.quota));
//		
		html = html.replace("%usage", go.util.humanFileSize(this.usage,true));
		html = html.replace("%quota", go.util.humanFileSize(this.quota,true));
		
		this.update(html);
	}
	
});