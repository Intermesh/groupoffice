/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: DiskQuotaPanel.js 16018 2013-10-22 12:26:17Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
GO.users.DiskQuotaPanel = Ext.extend(Ext.Panel,{

	initComponent : function(){
		
		Ext.apply(this,{
			border:false,
			//title: GO.users.lang['diskQuota'],
			layout:'form',
			items: [{
					xtype: 'fieldset',
					title: GO.users.lang['diskSpace'],
					items: [{
						xtype: 'compositefield',
						items: [{
								xtype: 'numberfield',
								name: 'disk_quota',
								fieldLabel: GO.users.lang['diskQuota'],
								decimals: 0
							},{
								xtype: 'displayfield',
								value: 'MB'
						}]
					},
					{
						xtype: 'displayfield',
						name: 'disk_usage',
						fieldLabel: GO.users.lang['spaceUsed'],
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