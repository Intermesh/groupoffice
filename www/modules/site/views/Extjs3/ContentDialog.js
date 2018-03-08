/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PageDialog.js 8376 2011-10-24 09:55:16Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.ContentDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	customFieldType : "GO\\Site\\Model\\Content",
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'content',
			title:GO.site.lang.content,
			formControllerUrl: 'site/content',
			updateAction : 'update',
			createAction	: 'create',
			height:400,
			width:700
		});
		
		GO.site.ContentDialog.superclass.initComponent.call(this);		
	},
	
	buildForm : function () {
		
		this.availableTemplatesStore = new GO.data.JsonStore({
			url: GO.url('site/content/templateStore'),
			baseParams: {
				siteId : false
			},	
			fields: ['path', 'name']
		});
		
		this.metaPanel = new Ext.Panel({
			title:GO.site.lang.meta,		
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.metaDescriptionText = new GO.form.HtmlComponent({
					html: '<p class="go-form-text">'+GO.site.lang.metaDescriptionText+'</p>'
				})
				,{
					xtype: 'fieldset',
					title: GO.site.lang.meta,
					autoHeight: true,
					border: true,
					collapsed: false,
					items:[{
						xtype: 'textfield',
						name: 'meta_title',
						width:300,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: GO.site.lang.contentMeta_title
					},{
						xtype: 'textfield',
						name: 'meta_keywords',
						width:300,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: GO.site.lang.contentMeta_keywords
					},{
						xtype: 'textarea',
						name: 'meta_description',
						width:300,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: GO.site.lang.contentMeta_description
					}]
				}
			]
		});
		
		this.infoPanel = new Ext.Panel({
			title:GO.site.lang.info,		
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.metaDescriptionText = new GO.form.HtmlComponent({
					html: '<p class="go-form-text">'+GO.site.lang.infoDescriptionText+'</p>'
				})
				,{
					xtype: 'fieldset',
					title: GO.site.lang.info,
					autoHeight: true,
					border: true,
					collapsed: false,
					items:[{
						xtype: 'textfield',
						name: 'ctime',
						width:300,
						disabled : true,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: GO.site.lang.contentCtime
					},{
						xtype: 'textfield',
						name: 'mtime',
						width:300,
						disabled : true,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: GO.site.lang.contentMtime
					},{
						xtype: 'textfield',
						name: 'user_id',
						width:300,
						disabled : true,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: GO.site.lang.contentUser_id
					}
				]}
				,this.metaDescriptionText = new GO.form.HtmlComponent({
					html: '<p class="go-form-text">'+GO.site.lang.templateDescriptionText+'</p>'
				})
				,{
					xtype: 'fieldset',
					title: GO.site.lang.template,
					autoHeight: true,
					border: true,
					collapsed: false,
					items:[
						this.selectTemplateCombo = new GO.form.ComboBox({
							fieldLabel: GO.site.lang.template,
							hiddenName:'template',
							anchor:'100%',
							store: this.availableTemplatesStore,
							valueField:'path',
							displayField:'name',
							mode: 'remote',
							triggerAction: 'all',
							allowBlank: false
						}),
						this.selectParentTemplateCombo = new GO.form.ComboBox({
							fieldLabel: GO.site.lang.defaultChildTemplate,
							hiddenName:'default_child_template',
							anchor:'100%',
							store: this.availableTemplatesStore,
							valueField:'path',
							displayField:'name',
							mode: 'remote',
							triggerAction: 'all',
							allowBlank: true
						})
					]
				}
			]
		});

		this.addPanel(this.infoPanel);
		this.addPanel(this.metaPanel);
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.availableTemplatesStore.setBaseParam('siteId', action.result.data.site_id);
		this.availableTemplatesStore.load();
	},	
	
	setSiteId : function(siteId){
		this.addBaseParam('site_id', siteId);
	},
	
	focus : function(){

	}
});