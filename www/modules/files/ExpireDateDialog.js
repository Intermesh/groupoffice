GO.files.ExpireDateDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	emailDownloadLink : false,
	pasteLinks: false,
	fileData : {},
	downloadLinkIds : [],
	
	initComponent : function(){
		
		Ext.apply(this, {
			title: t("Download link expire time", "files"),
			height:310,
			loadOnNewModel : false,
			enableApplyButton: false,
			enableOkButton : false,
			enableCloseButton : false,
			width:260,
			border:false,
			maximizable:true,
			collapsible:true,
			closeAction:'hide'
		});

		GO.files.ExpireDateDialog.superclass.initComponent.call(this);
		
	},
		
	buildForm : function () {
		
		this.deleteWhenExpiredCB = new Ext.ux.form.XCheckbox({
			hideLabel: true,
			anchor: '-20',
			boxLabel: t("Automatically delete file when download link expires", "files"),
			name: 'delete_when_expired',
			value: false,
			disabled: true,
			hidden: true
		});
		
		this.datePicker = new Ext.DatePicker({
			itemId: 'expire_time',
			name : 'expire_time',
			format: GO.settings.date_format,
			hideLabel: true
		});
		
		this.datePicker.on('select', function(field,date){
			let deleteWhenExpired = 0;
			if (!this.deleteWhenExpiredCB.disabled){
				deleteWhenExpired = this.deleteWhenExpiredCB.getValue() ? 1 : 0;
			}
			
			if(this.emailDownloadLink){

				go.showComposer({
					loadUrl:GO.url('files/file/emailDownloadLink'),
					loadParams:{
						ids: Ext.encode(this.downloadLinkIds),
						expire_time: parseInt(date.setDate(date.getDate())/1000),
						delete_when_expired: deleteWhenExpired
					}
				});

			} else {
				GO.request({
					maskEl: this.getEl(),
					url: 'files/file/createDownloadLink',
					params: {
						id:this.fileData.id,
						expire_time: parseInt(date.setDate(date.getDate())/1000),
						delete_when_expired: deleteWhenExpired
					},
					success: function(options, response, result) {
						if(result && result.url) {
							result.url = result.url.replace(/&amp;/g,"&");
							if (this.pasteLinks) {
								this.fireEvent("pasteLinks", result);
							} else {
								go.util.copyTextToClipboard(result.url);
								Ext.MessageBox.alert(t("Success"), t("Value copied to clipboard"));
							}
						}
						this.refreshActiveDisplayPanels();
					},
					scope:this
				});
			}

			this.hide();
		},this);

		this.datePickerWrapper = new Ext.Panel({
			autoHeight:true,
			cls:'go-date-picker-wrap-outer',
			baseCls:'x-plain',
			items:[
				new Ext.Panel({
					cls:'go-date-picker-wrap',
					items:[this.datePicker]
				})
			]
		});

		this.propertiesPanel = new Ext.Panel({
			layout: 'column',
			items: [
				this.deleteWhenExpiredCB,
				this.datePickerWrapper
			]
		});

		this.addPanel(this.propertiesPanel);
	},
	
	show : function(records, sendByMail, pasteLinks){
		
		GO.request({
			url: 'files/email/checkDeleteCron',
			success: function(options, response, result) {
				this.deleteWhenExpiredCB.setValue(false);
				this.deleteWhenExpiredCB.setVisible(result.data.enabled);
				this.deleteWhenExpiredCB.setDisabled(!result.data.enabled);
			},
			scope: this
		});
		
		this.emailDownloadLink=sendByMail;
		this.pasteLinks = pasteLinks;
		
		// reset the file list that will be add to the mail
		this.downloadLinkIds = [];
		for(var i=0; i<records.length; i++){
			this.downloadLinkIds.push(records[i].data.id);
		}
		
		this.fileData = records[0].data;

		GO.files.ExpireDateDialog.superclass.show.call(this);
	}
	
});
