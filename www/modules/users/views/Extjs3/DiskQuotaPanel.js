/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: DiskQuotaPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
GO.users.DiskQuotaPanel = Ext.extend(Ext.Panel,{

	initComponent : function(){
		
		Ext.apply(this,{
			border:false,
			//title: t("Disk Quota", "users"),
			layout:'form',
			items: [{
					xtype: 'fieldset',
					title: t("Disk Space", "users"),
					items: [{
						xtype: 'compositefield',
						items: [{
								xtype: 'numberfield',
								name: 'disk_quota',
								fieldLabel: t("Disk Quota", "users"),
								decimals: 0
							},{
								xtype: 'displayfield',
								value: 'MB'
						}]
					},
					{
						xtype: 'displayfield',
						name: 'disk_usage',
						fieldLabel: t("Space used", "users"),
						setValue: function(v) {
							this.setRawValue(Math.round(v/1024/1024*100)/100+'MB');
							return this;
						}
					}
				]
				}]
		});
	
		GO.users.DiskQuotaPanel.superclass.initComponent.call(this);
	}
});	