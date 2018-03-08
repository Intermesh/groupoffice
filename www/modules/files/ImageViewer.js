GO.files.ImageViewer = Ext.extend(GO.Window, {
	
	originalImgSize : false,
	
	fullSize:false,
	
	viewerImages : Array(),
	
	currentImgIndex : 0,
	
	stateId : 'go-imageviewer',
	
	initComponent : function(){
		
		
		this.border=false;
		this.plain=true;
		this.maximizable=true;
		this.width=800;
		this.height=600;
		this.bodyStyle='text-align:center;vertical-align:middle';
		this.title=GO.files.lang.imageViewer;
		this.autoScroll=true;
		
		this.tbar=[this.previousButton = new Ext.Button({
			iconCls: 'btn-left-arrow',
			text:GO.lang.cmdPrevious,
			handler: function(){
				this.loadImage(this.currentImgIndex-1);
			},
			scope:this
		}),this.nextButton = new Ext.Button({
			iconCls: 'btn-right-arrow',
			text:GO.lang.cmdNext,
			handler: function(){
				this.loadImage(this.currentImgIndex+1);
			},
			scope:this
		}),
		'-',
		{
			iconCls: 'btn-save',
			text: GO.lang.download,
			handler: function(){
				window.open(this.viewerImages[this.currentImgIndex].download_path);
			},
			scope: this
		},'-',
		this.normalSizeBtn=new Ext.Button({
			text: GO.files.lang.normalSize,
			iconCls: 'fs-btn-normal-size',
			handler: function(){
				this.loadImage(this.currentImgIndex, true);
			},
			scope: this
		}),
		this.fitImageBtn=new Ext.Button({
			text: GO.files.lang.fitImage,
			iconCls: 'fs-btn-fit-image',
			handler: function(){
				this.syncImgSize();
			},
			scope: this
		})];
		
		GO.files.ImageViewer.superclass.initComponent.call(this);
		this.on('resize', function(){this.syncImgSize(this.fullSize);}, this);
	},

	
	show : function(images, index)
	{
		if(!index)
			index=0;
		
		GO.files.ImageViewer.superclass.show.call(this);
		
		this.viewerImages = images;
		
		this.loadImage(index);		
	},
		
	loadImage : function(index, fullSize)
	{
		
		this.fullSize=fullSize;
		
		this.body.mask(GO.lang.waitMsgLoad);
		
		this.setTitle(this.viewerImages[index].name);
		
		this.currentImgIndex = index;

		if(this.imgEl)
		{
			this.imgEl.remove();
		}
		this.originalImgSize=false;
		this.imgEl = this.body.createChild({
			tag:'img',
			src: fullSize ? this.viewerImages[index].download_path : this.viewerImages[index].src,
			cls:'fs-img-viewer'
		});

		if (!this.viewerImages[index].download_path)
			this.viewerImages[index].download_path = this.viewerImages[index].src;

//		this.imgEl.initDD(null);
		
		this.syncImgSize(fullSize);
		
		
		
		
		
		if(this.viewerImages.length==1){
			this.previousButton.hide();
			this.nextButton.hide();
		}else
		{
			this.previousButton.show();
			this.nextButton.show();

			this.previousButton.setDisabled(index==0);
			this.nextButton.setDisabled(index==(this.viewerImages.length-1));
		}
	},
	
	syncImgSize : function(fullSize){	
		
		if(this.imgEl)
		{
			if(!this.imgEl.dom.complete)
			{
				this.syncImgSize.defer(100, this, [fullSize]);
			}else
			{			
				var imgSize = this.imgEl.getSize();
				
				if(!this.originalImgSize)
				{
					this.originalImgSize = imgSize;
				}
				
				var bodySize = this.body.getSize();
				
				var h = imgSize.height;
				var w = imgSize.width;
				if(this.originalImgSize.width > bodySize.width){
					w = bodySize.width;					
					h= this.originalImgSize.height*bodySize.width/this.originalImgSize.width;
				}

				if(h>bodySize.height){
					var ratio = bodySize.height/h;
					w=w*ratio
					h=h*ratio
				}

				if(!fullSize && (w!=this.originalImgSize.width || h!=this.originalImgSize.height)){
					this.normalSizeBtn.setDisabled(false);
//					this.fitImageBtn.setDisabled(false);

					this.imgEl.setWidth(w);
					this.imgEl.setHeight(h);
					this.imgEl.setPositioning({left:0,top:0});

					
				}else
				{
					this.normalSizeBtn.setDisabled(fullSize);
//					this.fitImageBtn.setDisabled(fullSize);
				}

				if(h<bodySize.height){
					var topMargin = (bodySize.height-h)/2;
					this.imgEl.setStyle('margin-top', topMargin+'px');
				}
				
//				if(fullSize){
//					this.imgEl.setSize(this.originalImgSize.width, this.originalImgSize.height);
//				}
				
				this.body.unmask();
			}
		}
	}/*,
	
	onResize : function(w, h){
		
		this.syncImgSize();
		
		 GO.files.ImageViewer.superclass.onResize.call(this, [w, h]);
	}*/
	
});