/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: BookmarksDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

go.modules.community.bookmarks.BookmarksDialog = Ext.extend(go.form.Dialog,{
	entityStore: "Bookmark",
	title: t("Bookmark"),
	initFormItems: function () {
		var thumbExample = null;
		var items = [{
			xtype: "fieldset",
			items: [new go.modules.community.bookmarks.BookmarkCombo(), 
			{
				name: 'content',
				xtype: 'textfield',
				fieldLabel: 'URL',
				anchor: '100%',
				emptyText: 'http://example.com',
				allowBlank: false,
				validator: function(value) {
					var urlRegexp = /((([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/;
					return urlRegexp.test(value);
				},
				listeners:{
					change: function(field,newValue, oldValue){
						this.el.mask(t("Loading..."));

						go.Jmap.request({
							method: "community/bookmarks/Bookmark/description",
							params: {
								url: newValue
							},
							callback: function(options, success, result) {
								this.websiteTitle.setValue(result.title);
								this.websiteDescription.setValue(result.description);
								thumbExample.getEl().dom.style.backgroundImage = 'url(' + go.Jmap.downloadUrl(result.logo) + ')';
								this.thumbField.setValue(result.logo);
								this.el.unmask();								
							},
							scope: this
						});
					},
					scope:this
				}

			},this.websiteTitle = new Ext.form.TextField({
				name: 'name',
				fieldLabel: t("Title"), 
				anchor: '100%',
				allowBlank: false
			}),
			this.websiteDescription = new Ext.form.TextArea({
				name: 'description',
				fieldLabel: t("Description"),
				anchor: '100%',
				grow: true
			}),
			this.thumbField = new Ext.form.Hidden({
				name: 'logo',
				setValue: function(value) {
					thumbExample.getEl().dom.style.backgroundImage = 'url(' + go.Jmap.downloadUrl(value) + ')';
					Ext.form.Hidden.prototype.setValue.call(this, value);
				}
			}),
			thumbExample = new Ext.Button({
				fieldLabel: t("Logo"), 
				dialog: this,
				style: {
					width:'32px',
					height:'32px',
					backgroundRepeat: 'no-repeat',
					backgroundSize:'cover',
					cursor:'pointer',
					border:'1px solid black'
				},
				listeners: {
					click: function() {
						go.util.openFileDialog({
							multiple: false,
							accept: "image/*",
							autoUpload: true,
							listeners: {
								upload: function(response) {
									thumbExample.getEl().dom.style.backgroundImage = 'url(' + go.Jmap.downloadUrl(response.blobId) + ')';
									this.thumbField.setValue(response.blobId);
								},
								scope: this
							}
						});
					}, scope: this
				}
			}),
			this.externCheck = new Ext.ux.form.XCheckbox({
				name: 'openExtern',
				xtype: 'checkbox',
				boxLabel: t("Open in new browser tab"),
				hideLabel:true,
				anchor: '100%',
				checked:true
			}),this.moduleCheck = new Ext.ux.form.XCheckbox({
				name: 'behaveAsModule',
				xtype: 'checkbox',
				boxLabel: t("Behave as a module (Browser reload required)"),
				hideLabel:true,
				anchor: '100%'
			}),
			]
		}];		

		this.moduleCheck.on('check', function(cb, checked)
		{
			this.externCheck.setValue(0);
			this.externCheck.setDisabled(checked);
		},this)
				
		return items;
	}
});
