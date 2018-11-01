/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: HtmlEditorImageDialog.js 8376 2011-10-24 09:55:16Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.HtmlEditorImageDialog = Ext.extend(GO.Window , {
	
	_panels : [],
	
	generatedTag : '',
	
	imgUrl : false,
	imgPath : false,
	imgId : false,
	imgWidth : false,
	imgHeight : false,
	imgAlign : false,
	imgCrop : false,
	imgZoom : false,
	imgAlt : false,
	
	imgLinkToOriginal: true,
	
	dimensionSmallWidth : 100,
	dimensionSmallHeight : 100,
	
	dimensionMediumWidth : 200,
	dimensionMediumHeight : 200,
	
	dimensionLargeWidth : 300,
	dimensionLargeHeight : 300,
	
	
	
	initComponent : function(){
		
		this.buttonOk = new Ext.Button({
			text: t("Ok"),
			handler: function(){
				this.generateTag();
				this.hide();
			},
			scope: this
		});

		this.buttonClose = new Ext.Button({
			text: t("Close"),
			handler: function(){
				this.clearTag();
				this.hide();
			},
			scope:this
		});
		
		Ext.apply(this, {
			goDialogId:'imageEditor',
			title:t("Customize image", "site"),
			height:400,
			width:700,
			layout:'fit',
			modal:false,
			buttons: [this.buttonOk,this.buttonClose]
		});

		this.buildForm();
		
		this.formPanelConfig=this.formPanelConfig || {};
		this.formPanelConfig = Ext.apply(this.formPanelConfig, {
			waitMsgTarget:true,			
			border: false,
			baseParams : {},
			layout:'fit'
		});
		
		this.formPanel = new Ext.form.FormPanel(this.formPanelConfig);

		if(this._panels.length > 1 || this.forceTabs) {		    
			this._tabPanel = new Ext.TabPanel({
				activeTab: 0,
				enableTabScroll:true,
				deferredRender: false,
				border: false,
				anchor: '100% 100%',
				items: this._panels
			});
		    
			this.formPanel.add(this._tabPanel);
		} else if (this._panels.length===1) {			

			delete this._panels[0].title;
			this._panels[0].header=false;
			if(this._panels[0].elements)
				this._panels[0].elements=this._panels[0].elements.replace(',header','');

			this.formPanel.add(this._panels[0]);
		}
		
		this.items=this.formPanel;				
	
		GO.site.HtmlEditorImageDialog.superclass.initComponent.call(this);		
	},
				
	buildForm : function(){
		
		this.widthField = new Ext.form.TextField({
			name: 'width',
			width:30,
			maxLength: 255,
			value: this.dimensionMediumWidth,
			allowBlank:false,
			fieldLabel: t("Width", "site"),
			listeners:{
				change:function(oldValue,newValue){
					this.imgWidth = newValue;
				},
				scope:this
			}
		});
		
		this.betweenLabel = new Ext.form.Label({
			name: 'betweenlabel',
			width:10,
			text: ' X '
		});
		
		this.heightField = new Ext.form.TextField({
			name: 'height',
			width:30,
			maxLength: 255,
			value: this.dimensionMediumHeight,
			allowBlank:false,
			fieldLabel: t("Height", "site"),
			listeners:{
				change:function(oldValue,newValue){
					this.imgHeight = newValue;
				},
				scope:this
			}
		});
		
		this.dimensionsComp = new Ext.form.CompositeField({
			fieldLabel: t("Dimensions (WxH)", "site"),
			items: [this.widthField,this.betweenLabel,this.heightField],
			disabled:true
		});
		
		this.dimensionOptionsRadioGroup = new Ext.form.RadioGroup({
			columns: 1,
			width:300,
			items: [{
				boxLabel: t("Zoom", "site"), 
				name: 'dimensionoptions', 
				inputValue: 'zoom', 
				style: 'margin-left: 4px; margin-right: -2px;', 
				checked: true
			},{
				boxLabel: t("Crop", "site"), 
				name: 'dimensionoptions', 
				inputValue: 'crop', 
				style: 'margin-left: 4px; margin-right: -2px;'
			}],
			hideLabel:true,
			listeners:{
				change:function(group,checked){
					
					switch(checked.inputValue){
						case 'crop':
							this.imgCrop = true;
							this.imgZoom = false;
							break;
						case 'zoom':
							this.imgCrop = false;
							this.imgZoom = true;
							break;
					}
				},
				scope:this
			}	
		});
		
		this.dimensionsRadioGroup = new Ext.form.RadioGroup({
			columns: 1,
			width:300,
			items: [{
				boxLabel: t("Small", "site"), 
				name: 'dimensions', 
				inputValue: 'small', 
				style: 'margin-left: 4px; margin-right: -2px;'
			},{
				boxLabel: t("Medium", "site"), 
				name: 'dimensions', 
				inputValue: 'medium', 
				style: 'margin-left: 4px; margin-right: -2px;', 
				checked: true
			},{
				boxLabel: t("Large", "site"), 
				name: 'dimensions', 
				inputValue: 'large', 
				style: 'margin-left: 4px; margin-right: -2px;'
			},{
				boxLabel: t("Custom", "site"), 
				name: 'dimensions', 
				inputValue: 'custom', 
				style: 'margin-left: 4px; margin-right: -2px;'
			}],
			hideLabel:true,
			listeners:{
				change:function(group,checked){
					
					switch(checked.inputValue){
						case 'small':
							this.dimensionsComp.setDisabled(true);
							this.widthField.setValue(this.dimensionSmallWidth);
							this.heightField.setValue(this.dimensionSmallHeight);
							this.imgWidth = this.dimensionSmallWidth;
							this.imgHeight = this.dimensionSmallHeight;
							break;
						case 'medium':
							this.dimensionsComp.setDisabled(true);
							this.widthField.setValue(this.dimensionMediumWidth);
							this.heightField.setValue(this.dimensionMediumHeight);
							this.imgWidth = this.dimensionMediumWidth;
							this.imgHeight = this.dimensionMediumHeight;
							break;
						case 'large':
							this.dimensionsComp.setDisabled(true);
							this.widthField.setValue(this.dimensionLargeWidth);
							this.heightField.setValue(this.dimensionLargeHeight);
							this.imgWidth = this.dimensionLargeWidth;
							this.imgHeight = this.dimensionLargeHeight;
							break;
						case 'custom':
							this.dimensionsComp.setDisabled(false);
							this.widthField.setValue('');
							this.heightField.setValue('');
							break;
					}
				},
				scope:this
			}	
		});
		
		this.alignmentRadioGroup = new Ext.form.RadioGroup({
			columns: 4,
			width:300,
			items: [
				{ items:
					{
						boxLabel: t("Left", "site"), 
						name: 'alignment', 
						inputValue: 'left', 
						style: 'margin-left: 4px; margin-right: -2px;'
					}
				},{
					items:
					{
					boxLabel: t("Center", "site"), 
					name: 'alignment', 
					inputValue: 'center', 
					style: 'margin-left: 4px; margin-right: -2px;'
					}
				},{
					items:
					{
						boxLabel: t("Right", "site"), 
						name: 'alignment', 
						inputValue: 'right', 
						style: 'margin-left: 4px; margin-right: -2px;'
					}
				},{
					items:
					{
						boxLabel: t("Inline", "site"), 
						name: 'alignment', 
						inputValue: 'inline', 
						style: 'margin-left: 4px; margin-right: -2px;', 
						checked: true
					}
				}
			
			],
			hideLabel:true,
			listeners:{
				change:function(group,checked){
					if(checked.inputValue === 'inline')
						this.imgAlign = false;
					else
						this.imgAlign = checked.inputValue;
				},
				scope:this
			}	
		});
		
		
		this.dimensionsFieldset = new Ext.form.FieldSet({
			title: t("Dimensions", "site"),
			autoHeight: true,
			border: true,
			collapsed: false,
			items:[
				this.dimensionsRadioGroup,
				this.dimensionsComp,
				this.dimensionOptionsRadioGroup
			]
		});
		
		this.alignmentFieldset = new Ext.form.FieldSet({
			title: t("Alignment", "site"),
			autoHeight: true,
			border: true,
			collapsed: false,
			items:[
				this.alignmentRadioGroup
			]
		});
		
		this.NotavailableLabel = new Ext.form.Label({
			html: t("Not yet implemented", "site")
		});
		
		this.imageFieldset = new Ext.form.FieldSet({
			title: t("Image example", "site"),
			height:203,
			border: true,
			collapsed: false,
			items:[
				this.NotavailableLabel
			]
		});
		
		
		this.altTextField = new Ext.form.TextField({
			name: 'alt',
			width:250,
			allowBlank:true,
			fieldLabel: t("Alt text", "site"),
			listeners:{
				change:function(oldValue,newValue){
					this.imgAlt = newValue;
				},
				scope:this
			}
		});
		
		this.linkToOriginal = new Ext.form.Checkbox({
			name: 'link_to_original',
			checked:true,			
			hideLabel:true,
			boxLabel: t("Link to original", "site"),
			listeners:{
				check:function(cb,checked){
					this.imgLinkToOriginal = checked;
				},
				scope:this
			}
		});
		
		this.otherOptionsFieldset = new Ext.form.FieldSet({
			title: t("Other options", "site"),
			height: 200,
			labelWidth: 45,
			border: true,
			collapsed: false,
			items:[
				this.altTextField,
				this.linkToOriginal
			]
		});

		this.propertiesPanel = new Ext.Panel({
			labelWidth: 120,
			cls:'go-form-panel',
			layout:'column',
			items:[{
				itemId:'leftCol',
				columnWidth: .5,
				items: [
					this.dimensionsFieldset,
					this.alignmentFieldset
				]
			},{
				itemId:'rightCol',
				columnWidth: .5,
				style: 'margin-left: 5px;',
				items: [
					this.imageFieldset,
					this.otherOptionsFieldset
				]
			}]
		});

		this.addPanel(this.propertiesPanel);
	},

	show : function(config){
		
		this.setDefaults();
		
		if(config.url)
			this.imgUrl = config.url;
		
		if(config.path)
			this.imgPath = config.path;
		
		if(config.id)
			this.imgId = config.id;
		
		GO.site.HtmlEditorImageDialog.superclass.show.call(this);		
	},
	
	getTag : function(){
		this.generateTag();
		
//		console.log(this.generatedTag);
		
		return this.generatedTag;
	},
					
	generateTag : function(){
		this.clearTag();
		var tag = '';
		var tagImg = '<img src="'+this.imgUrl+'"';
		
		
		tag += '<site:img id="'+this.imgId+'" path="'+this.imgPath+'"';
		
//		console.log('width:'+this.imgWidth);
//		console.log('height:'+this.imgHeight);
//		console.log('zoom:'+this.imgZoom);
//		console.log('crop:'+this.imgCrop);
//		console.log('align:'+this.imgAlign);
		
		if(this.imgWidth){
			tag += ' width="'+this.imgWidth+'"';
			
			if(this.imgWidth >= this.imgHeight)
				tagImg += ' width="'+this.imgWidth+'"';
		}
		
		if(this.imgHeight){
			tag += ' height="'+this.imgHeight+'"';
			
			if(this.imgHeight > this.imgWidth)
				tagImg += ' height="'+this.imgHeight+'"';
		}
		
		if(this.imgZoom)
			tag += ' zoom="'+this.imgZoom+'"';
		
		if(this.imgCrop)
			tag += ' crop="'+this.imgCrop+'"';

		if(this.imgAlt)
			tag += ' alt="'+this.imgAlt+'"';
		
		if(this.imgLinkToOriginal)
			tag += ' link_to_original="true"';

		if(this.imgAlign){
			if(this.imgAlign === 'center'){
				tag += ' align="margin-left:auto;margin-right:auto;"';
				tagImg += ' style="margin-left:auto;margin-right:auto;"';
			} else if(this.imgAlign === 'right'){
				tag += ' align="float:right;"';
				tagImg += ' style="float:right;"';
			} else {
				tag += ' align="float:left;"';
				tagImg += ' style="float:left;"';
			}
		}
		
		tagImg += '/>';
		tag += '>';
		
		tag += tagImg;
		
		
		tag += '</site:img>';
		
		
		this.generatedTag = tag;
	},
	
	clearTag : function(){
		this.generatedTag = '';
	},
	/**
	 * Use this function to add panels to the window.
	 * 
	 * @var relatedGridParamName Set to the field name of the has_many relation. 
	 * eg. Addressbook dialog showing contacts would have this value set to addressbook_id
	 */
	addPanel : function(panel, relatedGridParamName){
		this._panels.push(panel);
	},
	setDefaults : function(){
		this.imgWidth = this.dimensionMediumWidth;
		this.imgHeight = this.dimensionMediumHeight;
		this.imgZoom = true;
		this.imgCrop = false;
		this.imgAlign = false;
		this.imgAlt = false;
	}
	
});
