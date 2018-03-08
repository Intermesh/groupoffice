/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PricePanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
 
GO.servermanager.PricePanel = function(config)
{
	config = config || {};

	config.border=true;
	config.hideLabel=true;
	config.title = t("userPricing", "servermanager");
	config.layout='border';
	config.labelWidth=140;
	
	config.items=[{
			xtype:'panel',
			region:'north',
			title:t("space", "servermanager"),
			height:80,
			bodyStyle: 'padding:5px',
			items:[
			{
				xtype: 'compositefield',
				labelWidth: 70,
				fieldLabel: t("mbsIncluded", "servermanager"),
				items: [
				{
					xtype:'label',
					text:' ',
					width:10
				},
				this.mbsIncluded = new Ext.form.NumberField({
					name:'mbs_included',
					decimals:0,
					width:100
				}),
				{
					xtype:'label',
					text: 'MB '+t("perUser", "servermanager")
					}
				]
			},
			{
				xtype: 'compositefield',
				labelWidth: 70,
				fieldLabel: t("extraMbs", "servermanager"),
				items: [
				{
					xtype:'label',
					text: GO.settings.currency ,
					width:10
				},
				this.priceExtraGb = new Ext.form.NumberField({
					name:'price_extra_gb',
					width:100
				}),
				{
					xtype:'label',
					text:t("perMonth", "servermanager")+'/GB'
				}
				]
			}
			]
		},
	this.userPriceGrid= new GO.servermanager.UserPriceGrid({
		region:'center'
	}),
	this.modulePriceGrid= new GO.servermanager.ModulePriceGrid({
		region:'east',
		title:"Module prices",
		width:300
	})];
	
	GO.servermanager.PricePanel.superclass.constructor.call(this, config);	
}

Ext.extend(GO.servermanager.PricePanel, Ext.Panel,{
	
	afterRender: function(){
		
		GO.servermanager.PricePanel.superclass.afterRender.call(this);
		
		var requests = {
			moduleprices:{r:"servermanager/modulePrice/store"},				
			userprices:{r:"servermanager/userPrice/store"},
			space: {r:"servermanager/price/load"}
		}

		GO.request({

			url: "core/multiRequest",
			params:{
				requests:Ext.encode(requests)
			},
			success: function(options, response, result)
			{
				this.userPriceGrid.store.loadData(result.userprices);
				this.modulePriceGrid.store.loadData(result.moduleprices);
				
				this.mbsIncluded.setValue(result.space.data.mbs_included);
				this.priceExtraGb.setValue(result.space.data.price_extra_gb);
			},
			scope:this
		});    
		
	}
	
	});