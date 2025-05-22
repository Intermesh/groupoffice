import {client, jmapds} from "@intermesh/groupoffice-core";
import {Composer} from "./Composer";

declare var DOMPurify: any;

export class MailCtlr {
	static resend(id: string) {
		const cmp = new Composer();

		jmapds('Email').single(id).then(email => {
			cmp.form.create({
				identityId: 1,
				subject: email.subject,
				to: email.to,
				from: email.from,
				cc: email.cc,
				bcc: email.bcc,
				htmlBody: MailCtlr.emailText(email)
			});
		});
		return cmp;
	}

	static reply(id :string, all?:boolean) {

		const cmp = new Composer();
		jmapds('Email').get(id, email => {
			const txt = MailCtlr.emailText(email),
				at = (email.sentAt || email.receivedAt).date();

			let htmlBody = "Op "+at.to('j M Y')+ ' om '+ at.to('H:i')+" heeft "+email.from[0].name+' het volgende geschreven:<br><blockquote>'+txt+'</blockquote>',
				to = email.from;

			if(all && $.isArray(email.cc)) {
				email.cc.forEach(addr => to.push(addr));
			}

			cmp.form.create({
				identityId: 1,
				htmlBody,
				inReplyTo: email.messageId,
				subject: 'Re: '+email.subject,
				to
			});
		});
		return cmp;
	}

	static forward(id: string) {
		const cmp = new Composer();
		jmapds('Email').get(id, email => {
			const txt = MailCtlr.emailText(email),
				at = (email.sentAt || email.receivedAt).date(),
				htmlBody = "<blockquote>Begin doorgestuurd bericht:<br>"+
					'<br><b>Van:</b> '+ MailCtlr.addrToText(email.from[0]) +
					'<br><b>Onderwerp:</b> '+ email.subject +
					'<br><b>Datum:</b> '+ at.to('j M Y') + ' om ' + at.to('h:i:s')+ ' CEST'+
					'<br><b>Aan:</b> '+ MailCtlr.addrToText(email.to[0]) +
					'<br><br>'+txt+'</blockquote>'

			cmp.form.create({
				identityId: 1,
				htmlBody,
				inReplyTo: email.messageId,
				subject: 'Fwd: '+email.subject,
				to: email.from[0]
			});
		});
		return cmp;
	}

	static flag(rows: any[], name: string, on: true|null) {
		const s = jmapds('Email');
		for(const row of rows) {
			s.update(row.id, {['keywords/'+name]: on ?? !row.record.keywords[name]});
		}
		//s.commit();
	}

	// actions
	static addrToText(addrs) {
		if(!addrs) return '';
		let texts = addrs.map(a => $.isEmpty(a.name) ?
			a.email :
			a.name + '<'+a.email+'>') ;
		return texts.join(', ');
	}

	static emailText(data: any) {
		if(data.htmlBody) {
			for(let html,type,i=0; i < data.htmlBody.length; i++) {
				type = data.htmlBody[i].type;
				if(type.substring(0,4) !== 'text') {
					continue;
				}
				html = data.bodyValues[data.htmlBody[i].partId].value;
				if('DOMPurify' in window) {
					html = DOMPurify.sanitize(html, {FORCE_BODY: true});
				}
				if(type == 'text/plain') {
					html = html.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2')
						.replace(/((http|ftp)+(s)?:\/\/[^<>\s]+)/ig, "<a href=\"$1\" target=\"_blank\">$1</a>");
				}
				if(type == 'text/html') {
					// if block sender is untrusted
					html = html.replace(/(https?|ftp):\/\/[^"\s]+/ig,'').replace(/href=/ig, 'xref=');
					html = html.replace(/src="cid:(.*?)"/g, function(_:string, p1:string) {
						for(const a of data.attachments) {
							if(a.cid === p1) {
								p1 = client.downloadBlobId(a.blobId, a.name) || '';
								break;
							}
						}
						return 'src="'+p1+'"';
					});
				}
				return html;
			}
		} else {
			return 'Email not found on server';
		}
	}
}