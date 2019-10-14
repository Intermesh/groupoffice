/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: OtherLoginLocationDetectedDialog.js 21039 2017-04-06 11:33:19Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.dialog.OtherLoginLocationDetectedDialog = Ext.extend(GO.Window , {

	initComponent : function(){
		
		Ext.apply(this, {
			title:t("Already logged in on other location"),
			width: 650,
			height:120,
			closable:false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: this.submitForm,
				scope:this
			}],
			buttons: [
				{
					text: t("Continue"),
					handler: function(){
						this.fireEvent('continue', this);
						this.hide();
					},
					scope:this
				},
				{
					text: t("Cancel"),
					handler: function(){
						this.fireEvent('cancel', this);
						this.hide();
					},
					scope:this
				}
			]
		});
		
		GO.dialog.OtherLoginLocationDetectedDialog.superclass.initComponent.call(this);
		
		// add custom events
		this.addEvents('cancel', 'continue');
	},
	
	show : function(text,userId,userToken){
		this.html = text;
		this.userId = userId;
		this.userToken = userToken;
		GO.dialog.OtherLoginLocationDetectedDialog.superclass.show.call(this);	
	}
});
