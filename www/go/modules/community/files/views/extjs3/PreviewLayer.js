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
	
	media: null, // current playable media

	initComponent : function(){
		this.tbar = ['->',{
			tooltip: t('Close'),
			iconCls: 'ic-close',
			handler: function() {
				this.setVisible(false);
				if(this.media) {
					this.media.pause();
				}
			},
			scope:this
		}];
	
		this.buttons = [];

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
		this.getFooterToolbar().removeAll();
		switch(contentType) {
			case 'image':
				autoEl = { tag: 'img', src: go.Jmap.downloadUrl(file.blobId) };
				this.imageButtons();
				break;
			case 'video':
			case 'audio':
				autoEl = {id:'playable-preview',tag: contentType, src: go.Jmap.downloadUrl(file.blobId), type: file.contentType, style:'height:100%;' };
				this.videoButtons();
		}
		this.add(new Ext.BoxComponent({
			cls:'preview-item',
			anchor: '80% 80%',
			autoEl: autoEl,
			listeners:{
				afterrender:function(box){
					var el = Ext.get('playable-preview');
					if(el){
						this.media = el.dom;
					}
				},
				scope:this
			}
		}));
		this.doLayout();
		
		go.modules.community.files.PreviewLayer.superclass.show.call(this);
		this.focus();
	},
	
	imageButtons: function () {
		
		this.getFooterToolbar().addButton([{
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
		}]);
	},
	
	videoButtons: function () {
		this.getFooterToolbar().addButton([{
			iconCls: 'ic-replay-10',
			tooltip: t('Replay 10 seconds'),
			handler: function() {
				this.setPosition(-10);
			},scope:this
		},{
			iconCls: 'ic-play-arrow',
			tooltip: t('Play'),
			handler: function(btn) {
				if(!this.media){
					return;
				}
				var playEnded = function() {	
					btn.setIconClass('ic-play-arrow');
					btn.setTooltip(t('Play'));
				}
				this.media.addEventListener('ended',playEnded);
				if(this.media.paused) {
					this.media.play();
					btn.setIconClass('ic-pause');
					btn.setTooltip(t('Pause'));
				} else {
					this.media.pause();
					playEnded();
				}
				
				//todo
			},scope:this
		},{
			iconCls:'ic-forward-10',
			tooltip: t('Forward 10 seconds'),
			handler: function() {
				this.setPosition(10);
			},scope:this
		}]);
	},
	
	setPosition: function (seconds) {
		if(!this.media || this.media.paused) {
			return;
		}
		var time = Math.min(this.media.duration,Math.max(0,this.media.currentTime + seconds));
		this.media.currentTime = time;
		this.media.play();
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
