/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PersonalPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.PersonalPanel = function(config)
{
	if(!config)
	{
		config={};
	}
	
	//config.autoScroll=true;
	config.border=false;
	config.hideLabel=true;

	config.layout='column';
	//config.cls='go-form-panel';
	config.labelWidth=120;
	config.autoHeight=true;

	this.comboBoxId = config.cb_id;
	
	var rightColItems = [
					{fieldLabel: t("Address"), name: 'address'},
					{fieldLabel: t("Address 2"), name: 'address_no'},
					{fieldLabel: t("ZIP/Postal"), name: 'zip'},
					{fieldLabel: t("City"), name: 'city'},
					{fieldLabel: t("State"), name: 'state'},
					new GO.form.SelectCountry({
						fieldLabel: t("Country"),
						id:this.comboBoxId,
						hiddenName: 'country',
						value: GO.settings.country
					}),
					new GO.form.HtmlComponent({html: '<br />'})];

	rightColItems.push({fieldLabel: t("Fax"), name: 'fax'});
	rightColItems.push({fieldLabel: t("Mobile"), name: 'cellular'});			
	
	config.items= [
			{
				columnWidth: .5,
				layout: 'form',
				border: false,
				bodyStyle:'padding-right:5px',
				waitMsgTarget:true,
				defaults: {anchor: '100%'},
				defaultType: 'textfield',
				items: [
					{fieldLabel: t("First name"), name: 'first_name', allowBlank: false},
					{fieldLabel: t("Middle name"), name: 'middle_name'},
					{fieldLabel: t("Last name"), name: 'last_name', allowBlank: false},
					{fieldLabel: t("Title"), name: 'title'},
					{fieldLabel: t("Initials"), name: 'initials'},
					new Ext.form.ComboBox({
						fieldLabel: t("Sex"),
						hiddenName:'sex',
						store: new Ext.data.SimpleStore({
							fields: ['value', 'text'],
							data: [
							['M', t("Male")],
							['F', t("Female")]
							]
						}),
						value:'M',
						valueField:'value',
						displayField:'text',
						mode: 'local',
						triggerAction: 'all',
						editable: false,
						selectOnFocus:true,
						forceSelection: true
					}),
					new Ext.form.DateField({
						fieldLabel: t("Birthday"),
						name: 'birthday',
						format: GO.settings['date_format']
					}),
					new GO.form.HtmlComponent({html: '<br />'}),
					{
						fieldLabel: t("E-mail"),
						name: 'email',
						allowBlank: false,
						vtype:'emailAddress'
					},
					{
						fieldLabel: t("Phone"),
						name: 'home_phone'
					}
				]
			},{
				columnWidth: .5,
				bodyStyle:'padding-left:5px',
				layout: 'form',
				border: false,
				waitMsgTarget:true,
				defaults: {anchor:'100%', allowBlank: true},
				defaultType: 'textfield',
				items: rightColItems
			}];
	
	

	GO.users.PersonalPanel.superclass.constructor.call(this, config);		
}


Ext.extend(GO.users.PersonalPanel, Ext.Panel,{
	

});			