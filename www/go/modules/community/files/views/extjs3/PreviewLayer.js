/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
go.modules.community.files.PreviewLayer = Ext.extend(Ext.Panel, {
	cls:'go-fullscreen',
	renderTo: Ext.getBody(),
	buttonAlign:'center',
	buttons:[{
		iconCls: 'ic-remove',
		tooltip: t('Zoom out')
	},{
		iconCls: 'ic-search',
		tooltip: t('Original size')
	},{
		iconCls:'ic-add',
		tooltip: t('Zoom in')
	}],
	initComponent : function(){
		this.tbar = ['->',{
			tooltip: t('Close'),
			iconCls: 'ic-close',
			handler: function() {
				this.setVisible(false);
			},
			scope:this
		}];
		  
		this.items = [new Ext.BoxComponent({
			anchor: '80% 80%',
			autoEl: {
				tag: 'img',
				src: '/images/my-image.jpg'
			}
		})];
		go.modules.community.files.PreviewLayer.superclass.initComponent.call(this, arguments);
	}
});
