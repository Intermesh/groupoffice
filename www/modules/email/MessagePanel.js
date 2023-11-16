/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * @version $Id: MessagePanel.js 21549 2017-10-19 07:30:00Z mschering $
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

		this.contactImageId = Ext.id();

		this.linkMessageId = Ext.id();
		this.downloadAllMenuId = Ext.id();


		var templateStr =
		'<div class="message-header">'+
			'<table class="message-header-table">'+
			'<tr>'+

			'<td rowspan="99"><img id="'+this.contactImageId+'" src="{contact_thumb_url}" style="height:60px;border:1px solid #d0d0d0;margin-right:10px;cursor:pointer" /></td>'+


			'<td style="width:70px"><b>'+GO.email.lang.from+'</b></td>'+

			'<td>: {from} &lt;<a class="normal-link" href="#" onclick="GO.email.showAddressMenu(event, \'{sender}\', \'{[this.addSlashes(values.from)]}\');">{sender}</a>&gt;</td>'+
//			'<td rowspan="99"><span id="'+this.linkMessageId+'" class="em-contact-link"></span></td>'+

			'</tr>'+
			'<tr><td><b>'+GO.email.lang.subject+'</b></td><td>: {subject}</td></tr>'+
			'<tr><td><b>'+GO.lang.strDate+'</b></td><td>: {date}</td></tr>'+
			//'<tr><td><b>'+GO.lang.strSize+'</b></td><td>: {size}</td></tr>'+
			'<tr><td><b>'+GO.email.lang.to+'</b></td><td>: '+
			'<tpl for="to">'+
			'{personal} <tpl if="email.length">&lt;<a class="normal-link" href="#" onclick="GO.email.showAddressMenu(event, \'{email}\', \'{[this.addSlashes(values.personal)]}\');">{email}</a>&gt;; </tpl>'+
			'</tpl>'+
			'</td></tr>'+
			'<tpl if="cc.length">'+
			'<tr><td><b>'+GO.email.lang.cc+'</b></td><td>: '+
			'<tpl for="cc">'+
			'{personal} <tpl if="email.length">&lt;<a class="normal-link" href="#" onclick="GO.email.showAddressMenu(event, \'{email}\', \'{[this.addSlashes(values.personal)]}\');">{email}</a>&gt;; </tpl>'+
			'</tpl>'+
			'</td></tr>'+
			'</tpl>'+
			'<tpl if="bcc.length">'+
			'<tr><td><b>'+GO.email.lang.bcc+'</b></td><td>: '+
			'<tpl for="bcc">'+
			'{personal} <tpl if="email.length">&lt;<a class="normal-link" href="#" onclick="GO.email.showAddressMenu(event, \'{email}\', \'{[this.addSlashes(values.name)]}\');">{email}</a>&gt;; </tpl>'+
			'</tpl>'+
			'</td></tr>'+
			'</tpl>'+
			'</table>'+
			'<div class="em-contact-link-container"><span id="'+this.linkMessageId+'" class="em-contact-link"></span></div>'+
			'<tpl if="attachments.length">'+
			'<div style="clear:both;"></div>'+
			'<table>'+
			'<tr><td><b>'+GO.email.lang.attachments+':</b></td></tr><tr><td id="'+this.attachmentsId+'">'+
			'<tpl for="attachments">'+
				'<tpl if="extension==\'vcf\'">';
				if (GO.addressbook)
					templateStr += '<a class="filetype-link filetype-{extension}" id="'+this.attachmentsId+'_{[xindex-1]}" href="javascript:GO.email.readVCard(\'{url}&importVCard=1\');">{name:htmlEncode} ({human_size})</a> ';
				else
					templateStr += '<a class="filetype-link filetype-{extension}" id="'+this.attachmentsId+'_{[xindex-1]}" href="#">{name:htmlEncode} ({human_size})</a> ';
				templateStr += '</tpl>'+
				'<tpl if="extension!=\'vcf\'">'+
				'<a class="filetype-link filetype-{extension}" id="'+this.attachmentsId+'_{[xindex-1]}" href="#">{name:htmlEncode} ({human_size})</a> '+
				'</tpl>'+
			'</tpl>'+
//			ORIGINAL
//			'<tpl if="attachments.length&gt;1 && zip_of_attachments_url!=\'\'">'+
//			'<a class="filetype-link filetype-zip" href="{zip_of_attachments_url}" target="_blank">'+GO.email.lang.downloadAllAsZip+'</a>'+
//			'</tpl>'+

			'<tpl if="attachments.length&gt;1">'+
//				'<a class="filetype-link btn-menu" id="downloadAllMenu" href="#"></a>'+
				'<a class="filetype-link btn-more-vert" id="downloadAllMenu-'+this.downloadAllMenuId +'" href="#"></a>'+
//				'<a class="filetype-link btn-expand-more" id="downloadAllMenu" href="#"></a>'+
			'</tpl>'+

			'</td></tr>'+
			'</table>'+
			'</tpl>'+
			'<div style="clear:both;"></div>'+
			'<tpl if="blocked_images&gt;0">'+
			'<div class="go-warning-msg em-blocked">'+GO.email.lang.blocked+' <a id="em-unblock-'+this.bodyId+'" href="#" class="normal-link">'+GO.email.lang.unblock+'</a></div>'+
			'</tpl>'+
			'<tpl if="xssDetected">'+
			'<div class="go-warning-msg em-blocked"><a id="em-filterxss-'+this.bodyId+'" href="#" class="normal-link">'+GO.email.lang.xssDetected+'</a></div>'+
			'</tpl>'+

			'<tpl if="labels.length">' +
				'<div class="em-message-labels-container">' +
				'<tpl for="labels">'+
					'<span style="background-color: #{color}">{name}</span>' +
				'</tpl>'+
				'</div>' +
				'<div style="clear: both"></div>' +
			'</tpl>' +
		'</div>';

		if(GO.calendar){

			templateStr += '<tpl if="!GO.util.empty(values.iCalendar)">'+
				'<tpl if="iCalendar.feedback">'+
				'<div class="message-icalendar">'+

				'<tpl if="!iCalendar.invitation">' +
				'<div class="go-model-icon-GO_Calendar_Model_Event message-icalendar-icon ">'+
				'{[values.iCalendar.feedback]}</div>'+
				'</tpl>'+



				'<tpl if="iCalendar.invitation">'+

					'<tpl if="!GO.util.empty(iCalendar.invitation.is_processed)">'+
						'<a id="em-icalendar-open-'+this.bodyId+'" class="go-model-icon-GO_Calendar_Model_Event normal-link" style="padding-left:20px;background-repeat:no-repeat;" href="#" class="go-model-icon-GO\\Calendar\\Model\\Event message-icalendar-icon">'+GO.email.lang.appointementAlreadyProcessed+'</a>'+
					'</tpl>'+
					'<tpl if="iCalendar.invitation.is_invitation">'+

								'<a id="em-icalendar-accept-invitation-'+this.bodyId+'" class="go-model-icon-GO_Calendar_Model_Event normal-link" style="padding-left:20px;background-repeat:no-repeat;" href="#" class="go-model-icon-GO\\Calendar\\Model\\Event message-icalendar-icon">'+GO.calendar.lang.clickForAttendance+'</a>'+

					'</tpl>'+

					'<tpl if="iCalendar.invitation.is_cancellation">'+
						'<div class="go-model-icon-GO_Calendar_Model_Event message-icalendar-icon ">'+
						'{[values.iCalendar.feedback]}</div>'+
						'<tpl if="iCalendar.invitation.event_id">'+
							'<div class="message-icalendar-actions">'+
								'<a class="normal-link" id="em-icalendar-delete-event-'+this.bodyId+'" >'+t("Delete Event", "email")+'</a>'+
							'</div>'+
						'</tpl>'+
					'</tpl>'+

					'<tpl if="iCalendar.invitation.is_update">'+
						'<div class="go-model-icon-GO_Calendar_Model_Event message-icalendar-icon ">'+
						'{[values.iCalendar.feedback]}</div>'+
						'<tpl if="iCalendar.invitation.event_id">'+
							'<div class="message-icalendar-actions">'+
							'<a id="em-icalendar-open-'+this.bodyId+'" class="normal-link" style="padding-right:20px;" >'+t("Open Event", "email")+'</a>'+
								'<a class="normal-link" id="em-icalendar-update-event-'+this.bodyId+'" >'+t("Update Event", "email")+'</a>'+
								'</div>'+
							'</tpl>'+
					'</tpl>'+

				'</tpl>'+
				'<div style="clear:both"></div>'+
				'</div>'+
				'</tpl>'+
				'</tpl>';
		}

		templateStr += '<tpl if="values.isInSpamFolder==\'1\';">'+
				'<div class="message-move">'+
					GO.email.lang['thisIsSpam1']+' <a id="em-move-mail-link-'+this.bodyId+'" class="go-model-icon-GO\\Email\\Model\\Message normal-link" style="background-repeat:no-repeat;" href="javascript:GO.email.moveToInbox(\'{values.uid}\',\'{values.account_id}\');" >'+GO.email.lang['thisIsSpam2']+'</a> '+GO.email.lang['thisIsSpam3']+
				'</div>'+
			'</tpl>'+
			'<tpl if="values.isInSpamFolder==\'0\';">'+
				'<div class="message-move">'+
					GO.email.lang['thisIsNotSpam1']+' <a id="em-move-mail-link-'+this.bodyId+'" class="go-model-icon-GO\\Email\\Model\\Message normal-link" style="background-repeat:no-repeat;" href="javascript:GO.email.moveToSpam(\'{values.uid}\',\'{values.mailbox}\',\'{values.account_id}\');" >'+GO.email.lang['thisIsNotSpam2']+'</a> '+GO.email.lang['thisIsNotSpam3']+
				'</div>'+
			'</tpl>';

		templateStr += '<div id="'+this.bodyId+'" class="message-body go-html-formatted">{htmlbody}'+
			'<tpl if="body_truncated">'+
			'<br /><a href="javascript:GO.email.showMessageDialog({uid},\'{[this.addSlashes(values.mailbox)]}\',{account_id},true);" class="normal-link">'+GO.email.lang.clickSeeWholeMessage+'</a>'+
			'</tpl>'+
			'</div>';

		this.template = new Ext.XTemplate(templateStr,{

			addSlashes : function(str)
			{
				str = GO.util.html_entity_decode(str, 'ENT_QUOTES');
				str = GO.util.add_slashes(str);
				return str;
			}

		});
		this.template.compile();
	},

	lookupContact : function(){
		if(this.data.sender_contact_id){
			GO.linkHandlers["GO\\Addressbook\\Model\\Contact"].call(this, this.data.sender_contact_id);
		}else{
			GO.addressbook.searchSender(this.data.sender, this.data.from);
		}
	},

	data: null,


	popup : function(){

		if(this.loading){
			this.on('load', function(){this.popup()}, this, {single:true});
		}else{

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
		if(uid)
		{
			this.uid=uid;
			this.account_id=account_id;
			this.mailbox=mailbox;

			this.params = {
				uid: uid,
				mailbox: mailbox,
				account_id: account_id
			};
			if(password)
			{
				this.params.password=password;
			}
		}

		this.params['no_max_body_size'] = GO.util.empty(no_max_body_size) ? false : true;


		this.loading=true;
		this.el.mask(GO.lang.waitMsgLoad);
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
				Ext.Msg.alert(GO.lang.strError, result.feedback);
				this.loading=false;
			}
		});
	},
	
	reload : function() {
		this.loadMessage();
	},

	setData : function (data){
		this.data=data;



//				if(this.updated)
//				{
//					data.iCalendar.feedback = GO.email.lang.icalendarEventUpdated;
//					this.updated = false;
//				}else
//				if(this.created)
//				{
//					data.iCalendar.feedback = GO.email.lang.icalendarEventCreated;
//					this.created = false;
//				}else
//				if(this.deleted)
//				{
//					data.iCalendar.feedback = GO.email.lang.icalendarEventDeleted;
//					this.deleted = false;
//				}else
//				if(this.declined)
//				{
//					data.iCalendar.feedback = GO.email.lang.icalendarInvitationDeclined;
//					this.declined = false;
//				}

		if(data.iCalendar && this.icalendarFeedback){
			data.iCalendar.feedback = this.icalendarFeedback;
			delete this.icalendarFeedback;
		}

		data.mailbox=this.mailbox;

		if(data.askPassword)
		{
			if(!this.passwordDialog)
			{
				this.passwordDialog = new GO.dialog.PasswordDialog({
					title:GO.smime ? GO.smime.lang.enterPassword : GO.gnupg.lang.enterPassword,
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

		if(this.contactImageEl)
		{
			this.contactImageEl.removeAllListeners();
		}

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

		var acceptInvitationEl = Ext.get('em-icalendar-accept-invitation-'+this.bodyId);
		if(acceptInvitationEl)
		{
			acceptInvitationEl.on('click', function()
			{
				this.processInvitation();
			}, this);
		}
//		var declineInvitationEl = Ext.get('em-icalendar-decline-invitation');
//		if(declineInvitationEl)
//		{
//			declineInvitationEl.on('click', function()
//			{
//				this.processInvitation("DECLINED");
//			}, this);
//		}
//		var tentativeInvitationEl = Ext.get('em-icalendar-tentative-invitation');
//		if(tentativeInvitationEl)
//		{
//			tentativeInvitationEl.on('click', function()
//			{
//				this.processInvitation("TENTATIVE");
//			}, this);
//		}
		var icalDeleteEventEl = Ext.get('em-icalendar-delete-event-'+this.bodyId);
		if(icalDeleteEventEl)
		{
			icalDeleteEventEl.on('click', function()
			{
				this.processInvitation();
			}, this);
		}
		var icalUpdateEventEl = Ext.get('em-icalendar-update-event-'+this.bodyId);
		if(icalUpdateEventEl)
		{
			icalUpdateEventEl.on('click', function()
			{
				//this.processResponse();
				this.processInvitation();
			}, this);
		}

		var icalUpdateOpenEl = Ext.get('em-icalendar-open-'+this.bodyId);
		if(icalUpdateOpenEl)
		{
			icalUpdateOpenEl.on('click', function()
			{
				if(this.data.iCalendar.invitation.is_organizer){
					GO.calendar.showEventDialog({event_id:this.data.iCalendar.invitation.event_id})
				}else
				{
					GO.email.showAttendanceWindow(this.data.iCalendar.invitation.event_id);
				}
			}, this);
		}

		this.messageBodyEl = Ext.get(this.bodyId);
		this.messageBodyEl.on('click', this.onMessageBodyClick, this);
		this.messageBodyEl.on('contextmenu', this.onMessageBodyContextMenu, this);

		if(data.attachments.length)
		{
			this.attachmentsEl = Ext.get(this.attachmentsId);
			this.attachmentsEl.on('click', this.openAttachment, this);
			if(this.attachmentContextMenu)
			{
				this.attachmentContextMenu.messagePanel = this;
				this.attachmentsEl.on('contextmenu', this.onAttachmentContextMenu, this);
			}
		}

		if(data.attachments.length > 1 && this.allAttachmentContextMenu)	{
			this.allAttachmentsMenuEl = Ext.get('downloadAllMenu-'+this.downloadAllMenuId);

			this.allAttachmentsMenuEl.on('click', this.onAllAttachmentContextMenu, this);
			this.allAttachmentContextMenu.messagePanel = this;
			this.allAttachmentsMenuEl.on('contextmenu', this.onAllAttachmentContextMenu, this);
		}

		this.contactImageEl = Ext.get(this.contactImageId);
		this.contactImageEl.on('click', this.lookupContact, this);

		this.body.scrollTo('top',0);

		if(GO.savemailas && this.data.sender_contact_id){
			this.linkMessageCB = new Ext.form.Checkbox({
				name:'link',
				boxLabel:GO.savemailas.lang.linkToContact.replace('%s', this.data.contact_name),
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
								},
								scope:this
							});
						}else{
							GO.request({
								url:'core/unlink',
								params:{
									model_name1:'GO\\Addressbook\\Model\\Contact',
									id1:this.data.sender_contact_id,
									model_name2:'GO\\Savemailas\\Model\\LinkedEmail',
									id2:this.data.contact_linked_message_id
								},
								maskEl:Ext.getBody(),
								success: function(options, response, result) {
									if (result.success) {
										this.data.company_linked_message_id = result.linked_email_id;
									}
									this.getEl().unmask();
								},
								scope:this
							});
						}
					}
				}
			});
		}

		if(GO.savemailas && this.data.sender_company_id){
			this.linkCompanyMessageCB = new Ext.form.Checkbox({
				name:'link',
				boxLabel:GO.savemailas.lang.linkToCompany.replace('%s', this.data.company_name),
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
								maskEl:Ext.getBody()
							});
						}else{
							GO.request({
								url:'core/unlink',
								params:{
									model_name1:'GO\\Addressbook\\Model\\Company',
									id1:this.data.sender_company_id,
									model_name2:'GO\\Savemailas\\Model\\LinkedEmail',
									id2:this.data.company_linked_message_id
								},
								maskEl:Ext.getBody()
							});
						}
					}
				}
			});
		}

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
		if(target.id.substr(0,this.attachmentsId.length)==this.attachmentsId)
		{
			var attachment_no = target.id.substr(this.attachmentsId.length+1);

			var attachment = this.data.attachments[attachment_no];
			this.fireEvent('attachmentClicked', attachment, this);
		}
	},

	launchAddressContextMenu : function(e, href){
		var queryString = '';
		var email = '';
		var indexOf = href.indexOf('?');
		if(indexOf>-1)
		{
			email = href.substr(7, indexOf-7);
			queryString = href.substr(indexOf+1);
		}else
		{
			email = href.substr(7);
		}

		e.preventDefault();

		GO.email.addressContextMenu.showAt(e.getXY(), email, '', queryString);
	},

	onMessageBodyContextMenu :  function(e, target){

		if(target.tagName!='A')
		{
			target = Ext.get(target).findParent('A', 10);
			if(!target)
				return false;
		}

		if(target.tagName=='A')
		{
			var href=target.attributes['href'].value;

			if(href.substr(0,6)=='mailto')
			{
				this.launchAddressContextMenu(e, href);
			}
		}
	},

	onMessageBodyClick :  function(e, target){
		if(target.tagName!='A')
		{
			target = Ext.get(target).findParent('A', 10);
			if(!target)
				return false;
		}

		if(target.tagName=='A')
		{

			var href=target.attributes['href'].value;

			if(href.substr(0,6)=='mailto')
			{
				this.launchAddressContextMenu(e, href);
			}else if(href.substr(0,3)=='go:')
			{
				e.preventDefault();

				var cmd = 'GO.mailFunctions.'+href.substr(3);
				eval(cmd);
			}else
			{
//				if (target.href && target.href.indexOf('#') != -1 && target.pathname == document.location.pathname){
//				//internal link, do default
//
//				}else
//				{
//					e.preventDefault();
//					this.fireEvent('linkClicked', href);
//				}
			}
		}
	},

	cal_id:0,
	status_id:0,
	created:false,
	updated:false,
	deleted:false,
	declined:false,
	processInvitation : function()
	{
//		this.status_id = status_id || 0;

		GO.request({
			url: 'calendar/event/acceptInvitation',
			params: {
//				status: this.status_id,
				account_id: this.account_id,
				mailbox: this.mailbox,
				uid: this.uid
			},
			scope: this,
			success: function(options, response, data)
			{
				this.icalendarFeedback = data.feedback;

				if(data.attendance_event_id){
					GO.email.showAttendanceWindow(data.attendance_event_id);
				}

				this.loadMessage();
			}
		});
	}
});


GO.email.readVCard = function(url) {
	if (GO.addressbook)
		Ext.Ajax.request({
			url: url,
			callback: function(options, success, response)
			{
				var responseData = Ext.decode(response.responseText);
				if(!success || !responseData.success)
				{
					Ext.MessageBox.alert(GO.lang['strError'], responseData['feedback']);
				} else {
					if (!GO.util.empty(responseData.contacts[0])) {
						GO.addressbook.showContactDialog(0,{contactData : responseData.contacts[0]});
					}
				}
			},
			scope: this
		});
}
