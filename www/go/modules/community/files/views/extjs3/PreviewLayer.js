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
	scales: [25, 50, 75, 100, 150, 200, 400],
	itemScale: 100,
	renderTo: Ext.getBody(),
	hidden: true,
	buttonAlign:'center',

	initComponent : function(){
		this.tbar = ['->',{
			tooltip: t('Close'),
			iconCls: 'ic-close',
			handler: function() {
				this.setVisible(false);
			},
			scope:this
		}];
	
		this.buttons = [{
			iconCls: 'ic-remove',
			tooltip: t('Zoom out'),
			handler: function() {
				this.setScale(-1);
			},scope:this
		},{
			iconCls: 'ic-search',
			tooltip: t('Original size'),
			handler: function() {
				this.setScale(0);
			},scope:this
		},{
			iconCls:'ic-add',
			tooltip: t('Zoom in'),
			handler: function() {
				this.setScale(1);
			},scope:this
		}];

		go.modules.community.files.PreviewLayer.superclass.initComponent.call(this, arguments);
	
		this.on('afterrender',function(p) {
			p.el.set({tabindex: '-1'});
			p.keyMap = new Ext.KeyMap(p.el, {
				scope: p,
				key: Ext.EventObject.ESC,
				fn: function () {
					 this.setVisible(false);
				}
		  });
		});
	
	},
	
	show : function(file) {
		if(!file) {
			return;
		}
		var autoEl,contentType = file.contentType.split('/').shift();
		
		this.removeAll();
		switch(contentType) {
			case 'image':
				autoEl = { tag: 'img', src: go.Jmap.downloadUrl(file.blobId) };
				break;
		}
		this.add(new Ext.BoxComponent({
			cls:'preview-item',
			anchor: '80% 80%',
			autoEl: autoEl
		}));
		this.doLayout();
		
		go.modules.community.files.PreviewLayer.superclass.show.call(this);
		this.focus();
	},
	
	setScale: function(scale) {
		if(!this.scales[this.scales.indexOf(this.itemScale)+scale]) {
			return;
		}
		this.items.get(0).el.removeClass('scale-'+this.itemScale);
		this.itemScale = (scale === 0) ? 100 : this.scales[this.scales.indexOf(this.itemScale)+scale];
		this.items.get(0).el.addClass('scale-'+this.itemScale);
	}
});
