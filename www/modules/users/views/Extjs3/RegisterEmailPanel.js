/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: RegisterEmailPanel.js 22445 2018-03-06 08:36:59Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.users.RegisterEmailPanel = Ext.extend(Ext.Panel,{

	initComponent : function(){
		
		this.autoScroll=true;
		this.border=false;
		this.hideLabel=true;
		this.title = t("Registration Email", "users");
		this.layout='form';
		this.cls='go-form-panel';
		this.labelWidth=60;
		
		this.emailSubjectField = new Ext.form.TextField({
			name: 'register_email_subject',
			anchor: '100%',
			fieldLabel:t("Subject")
		});
		
		this.emailBodyField = new Ext.form.TextArea({
			hideLabel:true,
			name: 'register_email_body',
			anchor:'100% '+dp(-40),
			fieldLabel:t("Subject"),
			height:250
		});
		
		this.items = [
			this.emailSubjectField,
			this.emailBodyField
		]
	
		GO.users.RegisterEmailPanel.superclass.initComponent.call(this);
	}
});			
