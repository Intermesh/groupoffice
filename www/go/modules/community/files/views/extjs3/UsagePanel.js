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
	html:'quota',
	height:dp(40),
	
	initComponent: function () {
		this.setData(0,0);
		
		go.Stores.get("Storage").on('changes', function(store, added, changed, destroyed){
			var storages = store.get(changed.concat(added));
			for(var i=0;i<storages.length;i++) {
				if(storages[i].ownedBy == go.User.id){
					this.setData(storages[i].usage,storages[i].quota);
					break;
				}
			}
		},this);
		
		go.modules.community.files.UsagePanel.superclass.initComponent.call(this);
	},
	afterRender : function(pnl){
		this.updateHtml();
	},
	
	setData : function(usage, quota){
		this.usage = usage?usage:0;
		this.quota = quota?quota:0;
		if(this.rendered){
			this.updateHtml();
		}
	},
	
	updateHtml : function(){
		var html = t('%usage of %quota used');

		html = html.replace("%usage", go.util.humanFileSize(this.usage,true));
		html = html.replace("%quota", go.util.humanFileSize(this.quota,true));
		
		this.update(html);
	}
	
});
