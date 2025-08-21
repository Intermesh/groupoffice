/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * @version $Id: MessagePanel.js 22346 2018-02-08 15:57:36Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @since Group-Office 1.0
 */


GO.email.MessagePanel = Ext.extend(Ext.Panel, {

	uid : 0,

	mailbox:  "",

	account_id: 0,

	initComponent : function(){

		GO.email.MessagePanel.superclass.initComponent.call(this);

		this.attachmentContextMenu = new GO.email.AttachmentContextMenu();
		this.allAttachmentContextMenu = new GO.email.AllAttachmentContextMenu();
		
		this.addEvents({
			attachmentClicked : true,
			linkClicked : true,
			emailClicked : true,
			load : true,
			reset : true
		});
		this.bodyId = Ext.id();
		this.attachmentsId = Ext.id();

		this.linkMessageId = Ext.id();
		this.downloadAllMenuId = Ext.id();

		let templateStr =
						
		'<div class="message-header">'+
			
			'<table class="message-header-table">'+
			'<tr>'+

			'<td style="width:70px"><b>'+t("From", "email")+'</b></td>'+

			'<td>: {from} &lt;<a href="mailto:&quot;{[GO.util.html_entity_decode(values.from, \'ENT_QUOTES\')]}&quot; &lt;{sender}&gt;">{sender}</a>&gt;</td>'+

			'</tr>'+
			'<tr><td><b>'+t("Subject", "email")+'</b></td><td>: {subject}</td></tr>'+
			'<tr><td><b>'+t("Date")+'</b></td><td>: {date}</td></tr>'+
			'<tr><td><b>'+t("To", "email")+'</b></td><td>: '+
			'<tpl for="to">'+
			'{personal} <tpl if="email.length">&lt;<a href="mailto:&quot;{[GO.util.html_entity_decode(values.personal, \'ENT_QUOTES\')]}&quot; &lt;{email}&gt;">{email}</a>&gt;; </tpl>'+
			'</tpl>'+
			'</td></tr>'+
			'<tpl if="cc.length">'+
			'<tr><td><b>'+t("CC", "email")+'</b></td><td>: '+
			'<tpl for="cc">'+
			'{personal} <tpl if="email.length">&lt;<a href="mailto:&quot;{[GO.util.html_entity_decode(values.personal, \'ENT_QUOTES\')]}&quot; &lt;{email}&gt;">{email}</a>&gt;; </tpl>'+
			'</tpl>'+
			'</td></tr>'+
			'</tpl>'+
			'<tpl if="bcc.length">'+
			'<tr><td><b>'+t("BCC", "email")+'</b></td><td>: '+
			'<tpl for="bcc">'+
			'{personal} <tpl if="email.length">&lt;<a href="mailto:&quot;{[GO.util.html_entity_decode(values.personal, \'ENT_QUOTES\')]}&quot; &lt;{email}&gt;">{email}</a>&gt;; </tpl>'+
			'</tpl>'+
			'</td></tr>'+
			'</tpl>'+
			'</table>'+
			'<div class="em-contact-link-container"><span id="'+this.linkMessageId+'" class="em-contact-link"></span></div>'+
			'<tpl if="attachments.length">'+
			'<div style="clear:both;"></div>'+
			// '<table>'+
			// '<tr><tr><td id="'+this.attachmentsId+'">'+
			'<div class="em-attachments" id="'+this.attachmentsId+'">'+
			'<tpl for="attachments">'+
				'<tpl if="extension==\'vcf\'">';
				templateStr += '<a class="filetype-link filetype-{extension}" id="'+this.attachmentsId+'_{[xindex-1]}">{name:htmlEncode} ({human_size})</a> ';
				templateStr += '</tpl>'+
				'<tpl if="extension!=\'vcf\'">'+
				'<a class="filetype-link filetype-{extension}" id="'+this.attachmentsId+'_{[xindex-1]}">{name:htmlEncode} ({human_size})</a> '+
				'</tpl>'+
			'</tpl>'+

			'<tpl if="attachments.length&gt;0">'+
				'<i class="icon ic-more-vert" id="downloadAllMenu-'+this.downloadAllMenuId +'"></i>'+
			'</tpl>'+
							
			// '</td></tr>'+
			// '</table>'+
					'</div>'+
			'</tpl>'+			
			'<div style="clear:both;"></div>'+
			
			'<tpl if="links.length">'+
				'<div class="em-links">'+
				'<tpl for="links">'+
					'<div class="go-icon-list"><p class="more-btn"><i class="label entity {[this.linkIconCls(values)]}"></i> ' +
					'<a href="#email"  onclick="const win = new go.links.LinkDetailWindow({entity\:\'{entity}\'});win.load({model_id});">'+
					'{name:htmlEncode}</a> <label>{description:htmlEncode}</label>' +
					'{[this.addDeleteBtn(values)]}</p>' +
					'</div>' +
				'</tpl>'+
			'</div></tpl>'+
			
			
			'<tpl if="blocked_images&gt;0">'+
			'<div class="go-warning-msg em-blocked">'+t("{blocked_images} external images were blocked for your security.", "email")+' <a id="em-unblock-'+this.bodyId+'" class="normal-link">'+t("Click here to unblock them", "email")+'</a></div>'+
			'</tpl>'+
			'<tpl if="xssDetected">'+
			'<div class="go-warning-msg em-blocked"><a id="em-filterxss-'+this.bodyId+'" class="normal-link">'+t("This message may contain malicious content. Click here to view the filtered message anyway.", "email")+'</a></div>'+
			'</tpl>'+

			'<tpl if="labels.length">' +
				'<div class="em-message-labels-container">' +
				'<tpl for="labels">'+
					'<span style="background-color: #{color}">{name}</span>' +
				'</tpl>'+
				'</div>' +
				'<div style="clear: both"></div>' +
			'</tpl>' +
			
			'<a href="mailto:&quot;{[GO.util.html_entity_decode(values.from, \'ENT_QUOTES\')]}&quot; &lt;{sender}&gt;" class="avatar" style="{[this.getAvatarStyle(values.contact)]}">{[this.getAvatarHtml(values.contact)]}</a>'+

		'</div><ul class="actions"></ul>';

		templateStr += '<tpl if="values.isInSpamFolder==\'1\';">'+
				'<div class="message-move">'+
					t("This message has been identified as spam. Click", "email")+' <a id="em-move-mail-link-'+this.bodyId+'" class="normal-link" style="background-repeat:no-repeat;" onclick="GO.email.moveToInbox(\'{values.uid}\',\'{values.account_id}\');" >'+t("here", "email")+'</a> '+t("if you think this message is NOT spam.", "email")+
				'</div>'+
			'</tpl>'+
			'<tpl if="values.isInSpamFolder==\'0\';">'+
				'<div class="message-move">'+
					t("Click", "email")+' <a id="em-move-mail-link-'+this.bodyId+'" class="normal-link" style="background-repeat:no-repeat;" onclick="GO.email.moveToSpam(\'{values.uid}\',\'{values.mailbox}\',\'{values.account_id}\');" >'+t("here", "email")+'</a> '+t("if you think this message is spam.", "email")+
				'</div>'+
			'</tpl>';

		templateStr += '<div id="'+this.bodyId+'" class="message-body go-html-formatted">{htmlbody:raw}'+
			'<tpl if="body_truncated">'+
			'<br /><a href="javascript:GO.email.showMessageDialog({uid},\'{[this.addSlashes(values.mailbox)]}\',{account_id},true);" class="normal-link">'+t("The actual message is larger than can be shown here. Click here to see the entire message.", "email")+'</a>'+
			'</tpl>'+
			'</div>';

		this.template = new Ext.XTemplate(templateStr,{

			getAvatarHtml: function (v) {

				if(!v || v.photoBlobId) {
					return "";
				}
				return v.isOrganization ? '<i class="icon">business</i>' : go.util.initials(v.name);
			},
			getAvatarStyle: function (v) {
				if(!v) {
					return "cursor:pointer;";
				}
				return v.photoBlobId ? 'background-image: url(' + go.Jmap.thumbUrl(v.photoBlobId, {w: 40, h: 40, zc: 1})  + ')"' : "background-image:none;cursor:pointer;background-color: #" + v.color;;
			},

			defaultFormatFunc : false,
			linkIconCls : function(link) {				
				
				return go.Entities.getLinkIcon(link.entity, link.filter);

			},
			addDeleteBtn: function(link) {
				return '<a class="icon" onclick="GO.email.unlink('+link.link_id+');">delete</a>';
			},
			addSlashes : function(str)
			{
				str = GO.util.html_entity_decode(str, 'ENT_QUOTES');
				str = GO.util.add_slashes(str);
				return str;
			},

		});
		
		this.template.compile();
	},

	data: null,

	print : function() {
		this.body.print({title: this.data.date + " - " + this.data.sender + " - " + this.data.subject});
	},

	popup : function(){

		if(this.loading){
			this.on('load', function(){this.popup()}, this, {single:true});
		} else{
			this.messageDialog = new GO.email.MessageDialog({
				 closeAction:"close"
			});
			this.messageDialog.messagePanel.on('attachmentClicked', GO.email.openAttachment, this);

			this.messageDialog.showData(this.data);
			this.messageDialog.messagePanel.uid=this.uid;
			this.messageDialog.messagePanel.account_id=this.account_id;
			this.messageDialog.messagePanel.mailbox=this.mailbox;
			this.messageDialog.messagePanel.params=this.params;
		}

	},

	loadMessage : function(uid, mailbox, account_id, password, no_max_body_size)
	{
		if(uid) {
			this.uid=uid;
			this.account_id=account_id;
			this.mailbox=mailbox;

			this.params = {
				uid: uid,
				mailbox: mailbox,
				account_id: account_id
			};
			if(password) {
				this.params.password=password;
			}
		}

		if(!this.params) {
			return;
		}

		this.params['no_max_body_size'] = GO.util.empty(no_max_body_size) ? false : true;


		this.loading=true;
		this.el.mask(t("Loading..."));
		GO.request({
			url: "email/message/view",
			params: this.params,
			scope: this,
			success: function(options, response, data)
			{
				this.setData(data);
				this.loading=false;
				this.fireEvent('load', options, true, response, data, password);
			},
			fail: function(response, options, result) {
				Ext.Msg.alert(t("Error"), result ? result.feedback : t('An error occurred. More details can be found in the console.'));
				this.loading=false;
				this.el.unmask();
			}
		});
	},
	
	reload : function() {
		this.loadMessage();
	},

	setData : function (data){

		if(data.htmlbody) {
			data.htmlbody = Autolinker.link(
				data.htmlbody,
				{stripPrefix: false, stripTrailingSlash: false, className: "normal-link", newWindow: true, phone: false}
			)
		}

		this.data=data;

		data.mailbox=this.mailbox;

		if(data.askPassword)
		{
			if(!this.passwordDialog)
			{
				this.passwordDialog = new GO.dialog.PasswordDialog({
					title: t("Please enter the password of your SMIME certificate.", "smime"),
					fn:function(button, password, passwordDialog){
						if(button=='cancel')
						{
							this.reset();
							this.el.unmask();
						}else
						{
							this.loadMessage(passwordDialog.data.uid, passwordDialog.data.mailbox, passwordDialog.data.account_id, password);
						}
					},
					scope:this
				});
			}
			this.passwordDialog.data={
				uid:this.uid,
				mailbox:this.mailbox,
				account_id:this.account_id
			};
			this.passwordDialog.show();
		}else
		{
			this.setMessage(data);
			this.el.unmask();
		}

		if(data.feedback)
		{
			GO.errorDialog.show(data.feedback);
		}
	},

	reset : function(){
		this.data=false;
		this.uid=0;

		if(this.contactImageEl)
		{
			this.contactImageEl.removeAllListeners();
		}

		if(this.messageBodyEl)
		{
			this.messageBodyEl.removeAllListeners();
		}
		if(this.attachmentsEl)
		{
			this.attachmentsEl.removeAllListeners();
		}

		if(this.unblockEl)
		{
			this.unblockEl.removeAllListeners();
		}

		this.body.update('');

		this.fireEvent('reset', this);
	},

	setMessage : function(data)
	{
		this.data = data;

		//remove old listeners
		[this.messageBodyEl,
			this.attachmentsEl,
			this.unblockEl,
			this.contactImageE].forEach(e => e && e.removeAllListeners());

		this.template.overwrite(this.body, data);


		this.unblockEl = Ext.get('em-unblock-'+this.bodyId);
		if(this.unblockEl)
		{
			this.unblockEl.on('click', function(){
				this.params.unblock='true';
				this.loadMessage();
			}, this);
		}

		this.filterXssEl = Ext.get('em-filterxss-'+this.bodyId);
		if(this.filterXssEl)
		{
			this.filterXssEl.on('click', function(){
				this.params.filterXSS='true';
				this.params.unblock='true';
				this.loadMessage();
			}, this);
		}

		if(data.attachments.length)
		{
			this.attachmentsEl = Ext.get(this.attachmentsId);
			this.attachmentsEl.on('click', this.openAttachment, this);
			if(this.attachmentContextMenu) {
				this.attachmentContextMenu.messagePanel = this;
				this.attachmentsEl.on('contextmenu', this.onAttachmentContextMenu, this);
			}
		}

		// Add 'Delete all attachmennts' option to allAttachment Context menu
		if(data.attachments.length && this.allAttachmentContextMenu) {
			this.allAttachmentsMenuEl = Ext.get('downloadAllMenu-'+this.downloadAllMenuId);
			const single = data.attachments.length === 1;
			this.allAttachmentContextMenu.downloadButton.setVisible(!single);
			if(go.Modules.isAvailable("legacy", "files")) {
				this.allAttachmentContextMenu.saveButton.setVisible(!single);
				this.allAttachmentContextMenu.saveToItemButton.setVisible(!single);
			}

			this.allAttachmentsMenuEl.on('click', this.onAllAttachmentContextMenu, this);
			this.allAttachmentContextMenu.messagePanel = this;
			this.allAttachmentsMenuEl.on('contextmenu', this.onAllAttachmentContextMenu, this);
		}

		if(data.inlineAttachments.length) {
			this.attachmentContextMenu.messagePanel = this;
			this.allAttachmentsMenuEl = Ext.get(this.bodyId);
			this.allAttachmentsMenuEl.on('contextmenu', this.onImageContextMenu, this);
		}

		this.actions = this.body.dom.querySelector('ul.actions');
		try {
			GO.email.handleITIP(this.actions, data); // function is defined in calendar module
		} catch(e) {
			GO.errorDialog.show(t("Failed to process invitation"))
			console.error(e);
		}

		this.body.scrollTo('top',0);

		if(GO.savemailas && this.data.sender_contact_id){
			this.linkMessageCB = new Ext.form.Checkbox({
				name:'link',
				boxLabel:t("Link e-mail conversation to contact %s", "savemailas").replace('%s', Ext.util.Format.htmlEncode(this.data.contact_name)),
				hideLabel:true,
				renderTo:this.linkMessageId,
				checked:this.data.contact_linked_message_id>0,
				listeners:{
					scope:this,
					check:function(cb, checked){
						if(checked){
							GO.request({
								url:'savemailas/linkedEmail/linkContact',
								params:{
									account_id:this.account_id,
									mailbox:this.mailbox,
									uid:this.uid,
									contact_id:this.data.sender_contact_id
								},
								maskEl:Ext.getBody(),
								success: function(options, response, result) {
									if (result.success) {
										this.data.contact_linked_message_id = result.linked_email_id;
									}
									this.getEl().unmask();
									this.reload();
								},
								scope:this
							});
						}else{
							var me = this;

							Ext.getBody().mask(t("Saving..."));
							go.Db.store("Link").set({
								destroy: [this.data.contact_link_id]
							}).finally(function() {
								Ext.getBody().unmask();
								me.reload();
							});
							
						}
							
					}
				}
			});
		}

		if(GO.savemailas && this.data.sender_company_id){
			this.linkCompanyMessageCB = new Ext.form.Checkbox({
				name:'link',
				boxLabel:t("Link e-mail conversation to company %s", "savemailas").replace('%s', Ext.util.Format.htmlEncode(this.data.company_name)),
				hideLabel:true,
				renderTo:this.linkMessageId,
				checked:this.data.company_linked_message_id>0,
				listeners:{
					scope:this,
					check:function(cb, checked){
						if(checked){
							GO.request({
								url:'savemailas/linkedEmail/linkCompany',
								params:{
									account_id:this.account_id,
									mailbox:this.mailbox,
									uid:this.uid,
									company_id:this.data.sender_company_id
								},
								maskEl:Ext.getBody(),
								success: function(options, response, result) {									
									this.getEl().unmask();
									this.reload();
								},
								scope: this
							});
						}else{
							var me = this;
							Ext.getBody().mask(t("Saving..."));
							go.Db.store("Link").set({
								destroy: [this.data.company_link_id]
							}).finally(function() {
								Ext.getBody().unmask();
								me.reload();
							});
						}
					}
				}
			});
		}

	},
	onImageContextMenu : function (e, target) {

		if(target.tagName != "IMG") {
			return;
		}
		var token = this.getParameterByName("token",target.src), path = null;
		for(var i = 0; i < this.data.inlineAttachments.length;i++) {

			if(this.data.inlineAttachments[i].token == token) {
				var attachment = this.data.inlineAttachments[i];
			}
		}

		e.preventDefault();
		this.attachmentContextMenu.showAt(e.getXY(),attachment, target);


	},
	getParameterByName : function(name, url) {
		if (!url) url = window.location.href;
		name = name.replace(/[\[\]]/g, '\\$&');
		var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
			results = regex.exec(url);
		if (!results) return false;
		if (!results[2]) return false;
		return decodeURIComponent(results[2].replace(/\+/g, ' '));
	},
	onAttachmentContextMenu : function (e, target){
		if(target.id.substr(0,this.attachmentsId.length)==this.attachmentsId)
		{
			var attachment_no = target.id.substr(this.attachmentsId.length+1);

			e.preventDefault();
			var attachment = this.data.attachments[attachment_no];
			this.attachmentContextMenu.showAt(e.getXY(), attachment);
		}

	},
	
	onAllAttachmentContextMenu : function (e, target){
		e.preventDefault();
		this.allAttachmentContextMenu.showAt(e.getXY());
	},

	openAttachment :  function(e, target)
	{

		if(e.browserEvent.detail > 1) {
			//prevent double clicking
			return;
		}
		if(target.id.substr(0,this.attachmentsId.length)==this.attachmentsId)
		{
			var attachment_no = target.id.substr(this.attachmentsId.length+1);

			var attachment = this.data.attachments[attachment_no];
			this.fireEvent('attachmentClicked', attachment, this);
		}
	}
});


GO.email.readVCard = function(url) {
	if(go.Modules.isAvailable("legacy", "addressbook"))
		Ext.Ajax.request({
			url: url,
			callback: function(options, success, response)
			{
				var responseData = Ext.decode(response.responseText);
				if(!success || !responseData.success)
				{
					Ext.MessageBox.alert(t("Error"), responseData['feedback']);
				} else {
					if (!GO.util.empty(responseData.contacts[0])) {
						GO.addressbook.showContactDialog(0,{contactData : responseData.contacts[0]});
					}
				}
			},
			scope: this
		});
}
