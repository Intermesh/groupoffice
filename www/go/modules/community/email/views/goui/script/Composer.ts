import {
	Window,
	t,
	comp,
	tbar,
	btn,
	ArrayField,
	listfield,
	textfield,
	htmlfield,
	containerfield,
	arrayfield,
	Format,
	datasourceform,
	hiddenfield,
	DataSourceForm,
	browser,
	DateTime,
	select,
	DataSourceStore, datasourcestore
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";

class Composer extends Window {


	form: DataSourceForm
	private attachmentFld: ArrayField

	constructor(){
		super();

		this.title = t('Compose mail');
		this.width = 695;
		this.height = 600;
		this.resizable = true;


		this.items.add(
			this.form = datasourceform({
					cls: 'vbox',
					dataSource: jmapds('Email'),
					listeners: {
						'load': me => {
							const id: string = me.findField('identityId')!.value;
							if(id)
								jmapds('Identity').single(id).then(r => {
									// could have reply quote
									me.findField('htmlBody')!.value = r.htmlSignature + me.findField('htmlBody')!.value;
								});
						}
					}
				},
				comp({cls: 'vbox',flex:1},
					select({cls: 'w100',required:true,label: t('From'), name: 'identityId', store: datasourcestore({dataSource:jmapds('Identity')}), textRenderer: (r) => `${r.name} &lt;${r.email}&gt`}),
					textfield({cls: 'w100', label: t('To'), name: 'to'}), //chips
					textfield({cls: 'w100',name: 'subject', placeholder: t('Subject'), style: 'font-size:1.2em; height:42px;'}),
					htmlfield({cls: 'w100',flex:1, name: 'htmlBody', placeholder: t('Type a message')}),
					this.attachmentFld = arrayfield({cls: 'pad attachments',name: 'attachments',
						buildField: (data) => containerfield({
								tagName: 'a',
								listeners: {
									render(me) {
										me.el.on('click', () => {
											// todo : .file(data);
										})
									}
								}
							},
							hiddenfield({name: 'blobId', value: data.blobId}),
							comp({cls: 'mime '+`${data.name.split('.').pop()} ${data.type.split('/').join(' ')}`}),
							comp({tagName:'span', html: `${data.name}<br> <small>${data.name.split('.').pop().toUpperCase()} &bull; ${Format.fileSize(data.size)}</small>`})
						)
					}),
					tbar({},
						'->',
						btn({icon: 'attachment', title: t('Attach files'), handler: () => {this.attachFile();}}),
						btn({icon: 'save', title: t('Save'), handler: () => { this.submit(false); }}),
						btn({icon: 'send', cls: 'primary', text: t('Send'), handler: () => { this.submit(true); }})
					)
				)
			)
		);
	}

	private async attachFile() {
			const files = await browser.pickLocalFiles(true);
			this.attachmentFld.mask();
			const blobs = await client.uploadMultiple(files);
			this.attachmentFld.unmask();
			for(const r of blobs)
				this.attachmentFld.addValue({
					blobId:r.id,
					title:r.name,
					size:r.size,
					type:r.type
				});
	}

	submit(send = false) {
		// todo: find draft and send folder so drafts and sent box is known
		const draftBox = {id:1};

		const form = this.form;
		if (!form.isNew) return true;

		let now = new DateTime(),
			identityId: string = form.value.identityId,
			emailId = '123'; //random
		jmapds('Identity').single(identityId).then((identity: any) => {
			let email = Object.assign(form.value,{
				mailboxIds: {[draftBox.id]: true},
				keywords: {'$seen': true, '$draft': true},
				sentAt: now,
				from: [{name: identity.name, email: identity.email}],
				receivedAt: now
			});

			for(var a of email.attachments) {
				a.disposition = 'attachment';
				email.bodyStructure.subParts.push(a)
			}

			delete email.attachments;

			jmapds('Email').create(email,emailId).then((r) => {
				if(r.created?.[email.id] && send) {
					this.send(jmapds('Email').single(r.created[email.id].id), identity);
				}
			});
		})

		return false;


	}

	private send(email: any, identity: any) {
		const id = "123";
		jmapds('EmailSubmission').setParams = {onSuccessUpdateEmail: {[id]: {
					["mailboxIds/"+draftBox.id]: null,
					["mailboxIds/"+sentBox.id]: true,
					"keywords/$draft": null
				}}};
		jmapds('EmailSubmission').create({
			identityId: identity.id,
			emailId: email.id
			// envelope: {
			// 	mailFrom: {name: identity.name, email:identity.email},
			// 	rcptTo: [email.to]
			// }
		},id).then(() => {
			this.close();
		});
	}
}