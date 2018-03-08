/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AutomaticInvoiceTab.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */

GO.servermanager.AutomaticInvoiceTab = function(config){
	config = config || {};
	
	config.title = t("automaticInvoice", "servermanager");
	config.layout = 'column';
	config.autoScroll = true;
	config.items = [this.leftCol = new Ext.Panel({
			cls:'go-form-panel',
			waitMsgTarget:true,
			layout:'form',
			labelWidth:140,
			border:false,
			columnWidth:.5,
			items:[this.enableBox = new Ext.form.Checkbox({
				name: 'enable_invoicing',
				anchor: '-20',
				hideLabel:true,
				boxLabel: t("enabled", "servermanager"),
				checked:false,
				listeners:{
					check:function(cb, checked){
						this.enableFields(checked);
					},
					scope:this
				}
			}),
			new Ext.form.ComboBox({
				fieldLabel: t("invoiceTimespan", "servermanager"),
				hiddenName:'invoice_timespan',
				store: new Ext.data.SimpleStore({
						fields: ['value', 'text'],
						data : [
							['1', 'Monthly'],
							['3', 'Every 3 months'],
							['6', 'Every 6 months'],
							['12', 'Yearly']
						]

				}),
				value:'1',
				valueField:'value',
				displayField:'text',
				typeAhead: true,
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus:true,
				forceSelection: true,
				anchor: '-20'
			}),{
				xtype: 'numberfield',
				name: 'discount_price',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("discountPrice", "servermanager"),
				value: 0
			},{
				xtype: 'textfield',
				name: 'discount_description',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("discountDescription", "servermanager"),
				value: 0
			},{
				xtype: 'numberfield',
				decimals: 0,
				name: 'discount_percentage',
				anchor: '-20',
				fieldLabel: t("discountPercentage", "servermanager"),
				value: 0
			},{
				xtype: 'displayfield',
				name: 'next_invoice_time',
				anchor: '-20',
				fieldLabel: t("nextInvoiceOn", "servermanager")
			}]
	}),this.rightCol = new Ext.Panel({
		cls:'go-form-panel',
		waitMsgTarget:true,
		layout:'form',
		columnWidth:.5,
		labelWidth:140,
		border:false,
		items:[{
				anchor: '-20',
				xtype: 'textfield',
				name: 'customer_name',						
				allowBlank:false,
				fieldLabel: t("Name")
			},{
				xtype: 'textfield',
				name: 'customer_address',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("Address")
			},{
				xtype: 'textfield',
				name: 'customer_address_no',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("Address 2")
			},{
				xtype: 'textfield',
				name: 'customer_zip',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("ZIP/Postal")
			},{
				xtype: 'textfield',
				name: 'customer_city',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("City")
			},{
				xtype: 'textfield',
				name: 'customer_state',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("State")
			},new GO.form.SelectCountry({
				fieldLabel: t("Country"),
				hiddenName: 'customer_country',
				anchor: '-20',
				value: GO.settings.config.default_country
			}),{
				xtype: 'textfield',
				name: 'customer_vat',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: t("customerVat", "servermanager")
			}]
	})];
	
	GO.servermanager.AutomaticInvoiceTab.superclass.constructor.call(this, config);
	
	this.enableFields(false);
}

Ext.extend(GO.servermanager.AutomaticInvoiceTab, Ext.Panel,{
	enableFields : function(enabled) {
		this.leftCol.items.each(function(itm){itm.setDisabled(!enabled)}); //disable all fields
		this.rightCol.items.each(function(itm){itm.setDisabled(!enabled)}); //disable all fields
		this.enableBox.setDisabled(false);
	}
});