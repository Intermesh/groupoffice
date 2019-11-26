GO.files.ImageViewer = Ext.extend(go.Window, {
	
	originalImgSize : false,
	
	fullSize:false,
	
	viewerImages : Array(),
	
	currentImgIndex : 0,
	
	stateId : 'go-imageviewer',
	
	initComponent : function(){
		
		
		this.border=false;
		this.plain=true;
		this.maximizable=!GO.util.isMobileOrTablet();
		this.width=dp(1000);
		this.height=dp(800);
		this.bodyStyle='text-align:center;vertical-align:middle';
		this.title=t("Image viewer", "files");
		this.autoScroll=true;

		this.tbarCfg = {
			enableOverflow : true
		};

		this.tbar=new Ext.Toolbar({enableOverflow: true, items: [this.previousButton = new Ext.Button({
			iconCls: 'btn-left-arrow',
			tooltip:t("Previous"),
			handler: function(){
				this.loadImage(this.currentImgIndex-1);
			},
			scope:this
		}),this.nextButton = new Ext.Button({
			iconCls: 'btn-right-arrow',
			tooltip:t("Next"),
			handler: function(){
				this.loadImage(this.currentImgIndex+1);
			},
			scope:this
		}),
			'-',
		this.normalSizeBtn=new Ext.Button({
			text: t("Normal size", "files"),
			iconCls: 'ic-zoom-in',
			handler: function(){
				this.loadImage(this.currentImgIndex, true);
			},
			scope: this
		}),
		this.fitImageBtn=new Ext.Button({
			text: t("Fit image", "files"),
			iconCls: 'ic-zoom-out-map',
			handler: function(){
				this.syncImgSize();
			},
			scope: this
		}),
		'-',
		{
			iconCls: 'btn-download',
			text: t("Download"),
			handler: function(){
				go.util.downloadFile(this.viewerImages[this.currentImgIndex].download_path);
			},
			scope: this
		},{
			iconCls: 'ic-open-in-browser',
			text: t("Open in browser"),
			handler: function(){
				window.open(this.viewerImages[this.currentImgIndex].src);
			},
			scope: this
		},{
			iconCls: 'ic-print',
			text: t("Print"),
			handler: function() {
				function PrintHtml(source, title) {
					return "<html><head><title>" + Ext.encode(title) + "</title><script>function step1(){\n" +
									"setTimeout('step2()', 10);}\n" +
									"function step2(){window.print();window.close()}\n" +
									"</scri" + "pt></head><body onload='step1()'>\n" +
									"<img src='" + source + "' /></body></html>";
				}
				
				function closePrint () {
					if ( win ) {
						win.close();
					}
				}
				
				var win = window.open('about:blank', "_new");
				win.document.open();
				win.document.write(PrintHtml(this.viewerImages[this.currentImgIndex].src, this.viewerImages[this.currentImgIndex].name));
				win.document.close();
				win.onbeforeunload = closePrint;
				win.onafterprint = closePrint;
			
			},
			scope: this

		}
		]});
		
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
		
		this.body.mask(t("Loading..."));
		
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
			//separator
			this.getTopToolbar().items.itemAt(2).hide();
		}else
		{
			this.previousButton.show();
			this.nextButton.show();
			//separator
			this.getTopToolbar().items.itemAt(2).show();

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
