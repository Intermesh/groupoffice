import {client, jmapds} from "@intermesh/groupoffice-core";
import {Composer} from "./Composer";
import {DateTime, Notifier, router, t} from "@intermesh/goui";
import DOMPurify from 'dompurify';


export class MailCtlr {
	static resend(item: any) {
		const cmp = new Composer();

		jmapds('Email').single(item.id).then(email => {
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

	static compose(to: {name?: string, email: string}[]) {
		const cmp = new Composer();
		cmp.form.create({
			identityId: 1,
			to
		});
		return cmp;
	}

	static async showContact(email: string) {
		const r = await jmapds('Contact').query({filter: {email}, limit: 1});
		if(r.ids.length) {
			router.goto('contact/' + r.ids[0]);
		} else {
			Notifier.notice(t('No contact found for') + ' ' + email);
		}
	}

	static markSeen(emails: {id: string, accountId: string}[], seen: boolean) {
		const s = jmapds('Email');
		for(const email of emails) {
			s.setParams.accountId = email.accountId;
			s.update(email.id, {'keywords/$seen': seen ? true : null});
		}
	}

	static destroy(item: any) {
		const s = jmapds('Email');
		s.setParams.accountId = item.accountId;
		s.destroy(item.id);
	}

	static reply(item :any, all?:boolean) {

		const cmp = new Composer();
		jmapds('Email').single(item.id).then(email => {
			const txt = MailCtlr.emailText(email),
				at = new DateTime(email.sentAt || email.receivedAt);

			let htmlBody = "<br><br>Op "+at.format('j M Y')+ ' om '+ at.format('H:i')+" heeft "+email.from[0].name+' het volgende geschreven:<br>'+MailCtlr.prepareQuoted(txt)+'',
				to = email.from,
				cc = [];

			if(all && email.cc.length) {
				cc = email.cc;
			}

			cmp.form.create({
				identityId: 1,
				htmlBody,
				inReplyTo: email.messageId,
				references: MailCtlr.references(email),
				subject: (/^re:/i.test(email.subject ?? '') ? '' : 'Re: ')+email.subject,
				to,
				cc
			});
		});
		return cmp;
	}

	static forward(item: any) {
		const cmp = new Composer();
		jmapds('Email').single(item.id).then(email => {
			const txt = MailCtlr.emailText(email),
				at = new DateTime(email.sentAt || email.receivedAt),
				htmlBody = "<br><br><blockquote>Begin doorgestuurd bericht:<br>"+
					'<br><b>Van:</b> '+ MailCtlr.addrToText(email.from) +
					'<br><b>Onderwerp:</b> '+ email.subject +
					'<br><b>Datum:</b> '+ at.format('j M Y') + ' om ' + at.format('h:i:s')+ ' CEST'+
					'<br><b>Aan:</b> '+ MailCtlr.addrToText(email.to) +
					'<br><br>'+txt+'</blockquote>'

			cmp.form.create({
				identityId: 1,
				htmlBody,
				inReplyTo: email.messageId,
				subject: 'Fwd: '+email.subject,
				to: [email.from[0]]
			});
		});
		return cmp;
	}

	static flag(rows: any[], name: string, on: true|null) {
		const s = jmapds('Email');
		for(const row of rows) {
			console.log( row.record.keywords[name]);
			s.update(row.id, {['keywords/'+name]: on ?? !row.record.keywords[name]});
		}
		//s.commit();
	}

	/** References of a reply: the references of the parent followed by its Message-ID (RFC 5322 section 3.6.4) */
	static references(email: any): string[] {
		return [...(email.references ?? (email.inReplyTo ?? [])), ...(email.messageId ?? [])];
	}

	// actions
	static addrToText(addrs: any[]) {
		if(!addrs) return '';
		let texts = addrs.map(a => a.name ?
			a.email :
			a.name + '<'+a.email+'>') ;
		return texts.join(', ');
	}

	private static prepareQuoted(html:string) {
		const scope = "q-" + crypto.randomUUID().slice(0, 8);

		// 1. Find style blocks using Regex
		const processedHtml = html.replace(/<style\b[^>]*>([\s\S]*?)<\/style>/gi, (_, cssContent) => {

			const scopedCss = cssContent
				.replace(/\/\*[\s\S]*?\*\//g, "") // remove comments
				.replace(/@font-face\s*\{[\s\S]*?\}/gi, "") // remove font loading
				.replace(/@keyframes\s+[^{]+\{[\s\S]*?\}\s*\}/gi, "") // remove keyframes
				.replace(/([^\r\n,{}]+)(?=[^{}]*{)/g, (match) => {
					const trimmed = match.trim();
					if (trimmed.startsWith('@') || trimmed === '') return trimmed;
					return trimmed.split(",")
						.map(selector => {
							const s = selector.trim();
							if (!s) return "";
							// Normalize root-level tags
							const ls = s.toLowerCase()
							if (ls === 'body' || ls === 'html' || ls === ':root') {
								return `#${scope}`;
							}

							// Handle cases like 'body.classname'
							if (ls.startsWith('body') || ls.startsWith('html')) {
								return s.replace(/^(body|html)/i, `#${scope}`);
							}

							return `#${scope} ${s}`;
						})
						.join(",");
				}
			);

			return `<style>${scopedCss}</style>`;
		});

		return `<blockquote id="${scope}">${processedHtml}</blockquote>`;
	}

	static emailText(data: any) {
		if(data.htmlBody) {
			for(let html,type,i=0; i < data.htmlBody.length; i++) {
				type = data.htmlBody[i].type;
				if(type.substring(0,4) !== 'text') {
					continue;
				}
				html = data.bodyValues[data.htmlBody[i].partId].value;

				if(type == 'text/plain') {
					html = html.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2')
						.replace(/((http|ftp)+(s)?:\/\/[^<>\s]+)/ig, "<a href=\"$1\" target=\"_blank\">$1</a>");
				}
				if(type == 'text/html') {
					// block if sender is untrusted
					html = html.replace(/(https?|ftp):\/\/[^"\s]+/ig,'').replace(/href=/ig, 'xref=');
					html = html.replace(/src="cid:(.*?)"/g, function(_:string, p1:string) {
						for(const a of data.attachments) {
							if(a.cid === p1) {
								p1 = client.downloadUrl(a.blobId) || '';
								break;
							}
						}
						return 'src="'+p1+'"';
					});
				}
				return DOMPurify.sanitize(html, {FORCE_BODY: true});
			}
		} else {
			return 'Email not found on server';
		}
	}
}