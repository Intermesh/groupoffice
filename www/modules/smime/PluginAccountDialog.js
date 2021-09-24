/* global Ext, GO */

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: PluginAccountDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.moduleManager.onModuleReady('email',function(){

	Ext.override(GO.email.AccountDialog, {

		mimeRowActions: function() {
			var me = this;
			return new Ext.ux.grid.RowActions({
				header : '&nbsp;',
				width:120,
				listeners: {
					scope:this,
					action:function(grid, record, action, row, col) {
						//grid.getSelectionModel().selectRow(row);
						switch(action){
							case 'ic-receipt':
								window.open(GO.url("smime/certificate/download",{id:record.get('id')}));
								break;
							case 'ic-delete':
								Ext.Msg.confirm(
									t('Delete certificate',"smime"),
									t('Old SMIME certificates are needed to decrypt old e-mail')+'<br>'+t('Are you sure you want to delete this?'),
									function(btn) {
										if (btn == 'yes'){
											GO.request({
												url: 'smime/certificate/delete',
												params: {id: record.get('id')},
												success: () => { me.certStore.reload(); }
											});
										}
									}
								);
								break;
						}
					}
				},
				actions : [{
					iconCls : 'ic-receipt',
					qtip : t("Download certificate", "smime")
				},{
					iconCls : 'ic-delete',
					qtip : t("Delete certificate", "smime")
				}]
			});
		},

		initComponent : GO.email.AccountDialog.prototype.initComponent.createInterceptor(function(){
			//
			// this.propertiesPanel.fileUpload=true;
			// this.propertiesPanel.bodyCfg.enctype="multipart/form-data";


			this.mimeUploadWindow = new Ext.Window({
				title: t("PKCS12 certificate", "smime"),
				width: 500,
				closeAction: 'hide',
				cls:'go-form-panel',
				items:[
					this.certUploadForm= new Ext.form.FormPanel({
						url: GO.url("smime/certificate/upload"),
						fileUpload: true,
						bodyCfg: {enctype:"multipart/form-data"},
						labelWidth: 160,
						items:[// {
							// 	id:'smimeHasCert',
							// 	xtype:'label',
							// 	style:'display:block;margin-bottom:15px',
							// 	html:t("You have uploaded a certificate already. SMIME support is enabled for this account.", "smime")
							// },
							{
								xtype:'label',
								html:t("To upload a new PKCS12 certificate you must enter your Group-Office password. The Group-Office password must be different than your PCSK12 certificate for security reasons. No password is also prohibited.", "smime"),
								style:'display:block;margin-bottom:10px'
							},
							this.accountIdFld = new Ext.form.Hidden({name:'account_id'}),
							this.goPasswordField = new Ext.form.TextField({
								fieldLabel:GO.settings.config.product_name + ' ' + t("Password"),
								inputType:'password',
								name:'go_password',
								width:200
							}),this.passwordField = new Ext.form.TextField({
								fieldLabel:'SMIME ' + t("Password"),
								inputType:'password',
								name:'smime_password',
								width:200
							}),
							this.uploadFile = new GO.form.UploadFile({
								addText:t("Select new PKCS12 Certificate", "smime"),
								inputName : 'cert',
								max: 1
							})
						]
					})

				],
				buttons: ['->',{text:'Upload', handler: () => {this.certUploadForm.form.submit({
						success: () => {
							this.certStore.reload();
							this.passwordField.setValue('');
							this.goPasswordField.setValue('');
							this.uploadFile.clearQueue();
							this.uploadFile.createUploadInput();
							this.mimeUploadWindow.close();
						},
						failure: (form,action) => {
							Ext.Msg.alert(t('Error'),action.result.feedback);
						}
					}) } }]
			});

			var btns = this.mimeRowActions();

			this.smimePanel=new Ext.Panel({
				layout:'fit',
				title:t("SMIME settings", "smime"),
				disabled:true,
				items:[{
					xtype:'grid',
					plugins: [btns],
					viewConfig: {forceFit:true},
					tbar: [
						this.alwaysSignCheckbox = new Ext.ux.form.XCheckbox({
							//xtype:'checkbox',
							hideLabel:true,
							boxLabel:t("Always sign messages", "smime"),
							disabled:true,
							name:'always_sign'
						}),'->', {
							xtype: 'button',
							text: t('Upload certificate'),
							handler: function() {
								this.mimeUploadWindow.show();
								this.accountIdFld.setValue(this.account_id);
							},
							scope:this
						}
					],
					title:t("PKCS12 certificates", "smime"),
					store: this.certStore = new GO.data.JsonStore({
						url:GO.url("smime/certificate/store"),
						fields:['id','serial','valid_until','valid_since', 'provided_by'],
						listeners: {load: (store) => {
								this.alwaysSignCheckbox.setDisabled(!store.data.length);
							}},
						baseParams: {
							limit: 1000
						}
					}),
					colModel: new Ext.grid.ColumnModel({
						defaults: {sortable: false, menuDisabled: true},
						columns:[
						{dataIndex:'id', header: 'ID', width: 30, hidden:true},
						{dataIndex: 'serial', header: 'Serial'},
						{dataIndex:'valid_until', header: t('valid'), renderer: (v) => go.util.Format.dateTime(v)},
						{dataIndex:'valid_since', header: t('since'), renderer: (v) => go.util.Format.dateTime(v)},
						{dataIndex:'provided_by', header: t('by')},
						btns
					]})
				}]
			});
							
			this.tabPanel.add(this.smimePanel);

			this.smimePanel.on('show', function() {
				this.certStore.load({params:{account_id: this.account_id}});
			},this);
								
			this.on('show', function() {
				this.smimePanel.setDisabled(true);
			}, this);
								
			this.propertiesPanel.form.on("actioncomplete", function(form, action){
				//console.log(action.result);
				if(action.type!='submit') {
					this.alwaysSignCheckbox.setValue(action.result.data.always_sign);
				}
				this.smimePanel.setDisabled(false);
			}, this);
		})
	});
});

