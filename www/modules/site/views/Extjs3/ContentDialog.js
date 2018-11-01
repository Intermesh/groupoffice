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
			title:t("Content", "site"),
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
			title:t("Meta", "site"),		
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.metaDescriptionText = new GO.form.HtmlComponent({
					html: '<p class="go-form-text">'+t("Meta tags are a great way for webmasters to provide search engines with information about their sites.", "site")+'</p>'
				})
				,{
					xtype: 'fieldset',
					title: t("Meta", "site"),
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
						fieldLabel: t("Meta title", "site")
					},{
						xtype: 'textfield',
						name: 'meta_keywords',
						width:300,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: t("Meta keywords", "site")
					},{
						xtype: 'textarea',
						name: 'meta_description',
						width:300,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: t("Meta description", "site")
					}]
				}
			]
		});
		
		this.infoPanel = new Ext.Panel({
			title:t("Info", "site"),		
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.metaDescriptionText = new GO.form.HtmlComponent({
					html: '<p class="go-form-text">'+t("Information about this content item", "site")+'</p>'
				})
				,{
					xtype: 'fieldset',
					title: t("Info", "site"),
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
						fieldLabel: t("Created", "site")
					},{
						xtype: 'textfield',
						name: 'mtime',
						width:300,
						disabled : true,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: t("Modified", "site")
					},{
						xtype: 'textfield',
						name: 'user_id',
						width:300,
						disabled : true,
						anchor: '100%',
						maxLength: 100,
						allowBlank:true,
						fieldLabel: t("User", "site")
					}
				]}
				,this.metaDescriptionText = new GO.form.HtmlComponent({
					html: '<p class="go-form-text">'+t("Choose a template in which you want to view this content item", "site")+'</p>'
				})
				,{
					xtype: 'fieldset',
					title: t("Template", "site"),
					autoHeight: true,
					border: true,
					collapsed: false,
					items:[
						this.selectTemplateCombo = new GO.form.ComboBox({
							fieldLabel: t("Template", "site"),
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
							fieldLabel: t("Default child template", "site"),
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
