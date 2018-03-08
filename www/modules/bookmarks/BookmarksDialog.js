/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: BookmarksDialog.js 19244 2015-07-27 07:17:02Z wsmits $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

GO.bookmarks.BookmarksDialog = function(config){
	
	if(!config)
	{
		config={};
	}

	this.buildForm(config);
	
	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=500;
	config.height=350;
	config.closeAction='hide';
	config.items= this.formPanel;
	config.title=GO.bookmarks.lang.bookmark;
	config.buttons=[{
		text: GO.lang['cmdOk'],
		handler: function(){
			this.submitForm(true);
		},
		scope: this
	},{
		text: GO.lang['cmdClose'],
		handler: function(){
			this.hide();
		},
		scope:this
	}];

	GO.bookmarks.BookmarksDialog.superclass.constructor.call(this, config);
	
	this.addEvents({
		'save' : true
	});
}

Ext.extend(GO.bookmarks.BookmarksDialog, Ext.Window,{

	focus : function(){
		this.formPanel.form.findField('content').focus();
	},

	show : function (config) {		

		if(!this.selectCategory.store.loaded){
			return this.selectCategory.store.load({
				callback:function(){
					this.show(config);
				},
				scope:this
			});			
		}

		if(!this.rendered)
			this.render(Ext.getBody());
		
		

		var logo='icons/bookmark.png';
		this.formPanel.baseParams.public_icon='1';
  
		if (config.edit==1) // edit bookmark
		{
			// vul form met gegevens van aangeklikte bookmark
			this.formPanel.form.setValues(config.record.data);
			//this.selectCategory.setRemoteText(config.record.category_name);
			this.formPanel.baseParams.id=config.record.data.id;
			// thumb voorbeeld

			logo = config.record.data.logo;
			this.formPanel.baseParams.public_icon = config.record.data.public_icon;
			
		}
		else // add bookmark
		{
			// lege velden in form
			this.formPanel.baseParams.id=0;
			this.formPanel.form.reset();
			this.selectCategory.selectFirst();
		// leeg voorbeeld
		}

		this.setIcon(logo, this.formPanel.baseParams.public_icon);

		GO.bookmarks.BookmarksDialog.superclass.show.call(this);
	},
	

	submitForm : function(hide){
		
		this.formPanel.form.submit(
		{
			url : GO.url('bookmarks/bookmark/submit'),
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				if(action.result.bookmark_id){
					this.formPanel.baseParams['id']=action.result.bookmark_id;
				}
				if(hide)
				{
					this.hide();
				}
				this.fireEvent('save', this);
			},
			failure: function(form, action) {
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);			
				} else {
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
				}
			},
			scope: this
		});
	},

	setIcon : function(icon, pub){

		var now = new Date();
		var url = GO.bookmarks.getThumbUrl(icon, pub);
		if(pub==0){
			url += '&amp;time='+now.format('U');
		}

		this.formPanel.baseParams.public_icon=pub;

		this.thumbExample.getEl().update(GO.bookmarks.thumbTpl.apply({
			logo:url,
			title:GO.bookmarks.lang.title,
			description:GO.bookmarks.lang.description
		}));
	},

	buildForm : function (config) {
	
		this.bookmarkPanel = new Ext.Panel({
			layout:'form',
			border: false,
			cls:'go-form-panel',
			waitMsgTarget:true,
			items: [ // de invoervelden
			this.selectCategory = new GO.form.ComboBox({
				fieldLabel: GO.bookmarks.lang.category,
				hiddenName:'category_id',
				anchor:'100%',
				store: GO.bookmarks.writableCategoriesStore,
				displayField:'name',
				valueField:'id',
				triggerAction: 'all',
				editable: false,
				allowBlank: false,
				selectOnFocus:true,
				forceSelection: true,
				mode:'local'
			}),{
				name: 'content',
				xtype: 'textfield',
				fieldLabel: 'URL',
				anchor: '100%',
//				vtype: 'url',
				value:'http://',
				allowBlank: false,
				validator: function(value) {
					// The following allows also URLs like 'https://wiki:username@wiki.mydomain.org'
//					var urlRegexp = /(((^https?)|(^ftp)):\/\/(([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)|([\-\w]+\:)+([\-\w]+\@)+([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)/i;
					// The following also accepts: http://intranet/foo/bar
					var urlRegexp = /(((^https?)|(^ftp)):\/\/(\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)|([\-\w]+\:)+([\-\w]+\@)+([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)/i;
					
					return urlRegexp.test(value);
				},
				listeners:{
					change:function(combo){
						this.el.mask(GO.lang.waitMsgLoad);
						Ext.Ajax.request({
							url : GO.url('bookmarks/bookmark/description'),
							params:{
								
								url: combo.getValue()
							},
							callback:function(options, success, response){
								var result = Ext.decode(response.responseText);
								if(!GO.util.empty(result.description))
									this.formPanel.form.findField('description').setValue(result.description);

								if(!GO.util.empty(result.title))
									this.formPanel.form.findField('name').setValue(result.title);

								if(result.logo){
									this.setIcon(result.logo);
									this.selectFile.setValue(result.logo);
								}

								this.el.unmask();
							},
							scope:this
						});
					},
					scope:this
				}

			},{
				name: 'name',
				xtype: 'textfield',
				fieldLabel: GO.bookmarks.lang.title, 
				anchor: '100%',
				allowBlank: false
			},this.externCheck = new Ext.ux.form.XCheckbox({
				name: 'open_extern',
				xtype: 'checkbox',
				boxLabel: GO.bookmarks.lang.extern,
				hideLabel:true,
				anchor: '100%',
				checked:true
			}),this.moduleCheck = new Ext.ux.form.XCheckbox({
				name: 'behave_as_module',
				xtype: 'checkbox',
				boxLabel: GO.bookmarks.lang.behaveAsModule,
				hideLabel:true,
				anchor: '100%'
			}),
			{
				name: 'description',
				xtype: 'textarea',
				fieldLabel: GO.bookmarks.lang.description,
				anchor: '100%',
				height:65
			},
			this.selectFile = new GO.bookmarks.SelectFile({
				fieldLabel: GO.bookmarks.lang.logo, 
				name: 'logo',
				anchor: '100%',
				value:'icons/bookmark.png',
				dialog: this
			}),
			
			this.thumbExample = new Ext.Component({
				style: {
					marginLeft: '100px'
				}})
			]
		});
		

		

		this.moduleCheck.on('check', function(cb, checked)
		{
			this.externCheck.setValue(0);
			this.externCheck.setDisabled(checked);

		},this)
                
		this.items = [this.bookmarkPanel];
		this.formPanel = new Ext.form.FormPanel({
			baseParams : {
				id: 0
			},
			items: this.items
		});
	}
});