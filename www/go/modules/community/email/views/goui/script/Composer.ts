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
	DataSourceStore, datasourcestore, chips, autocomplete, tree, list, menu, h4, List, store, Store, Component,
	ChipRenderer
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {accountStore} from "@intermesh/community/email";
import {AccountWindow} from "./AccountWindow";

export class Composer extends Window {


	form: DataSourceForm
	private attachmentFld: ArrayField

	constructor(){
		super();

		this.title = t('Compose mail');
		this.width = 995;
		this.height = 800;
		this.resizable = true;

		const idStores: {[accountId:string]: Store} = {};

		const allIdStore = store({
			async onLoad(store) {
				const get = await jmapds("Identity").getAll();
				get.list.forEach((identity: any) => {
					allIdStore.add(identity);
				})
			}
		});

		this.items.add(
			this.form = datasourceform({
				cls: 'vbox',
				flex:1,
					dataSource: jmapds('Email'),
					listeners: {
						'load': ({target}) => {
							const id = target.findField('identityId')!.value;
							if(id)
								jmapds('Identity').single(id as string).then(r => {
									// could have reply quote
									target.findField('htmlBody')!.value = r.htmlSignature + target.findField('htmlBody')!.value;
								});
						}
					}
				},
				comp({cls: 'vbox fit'},
					// identitie selection
					autocomplete({required:true,label: t('From'), name: 'identityId',
						readOnly: true,
						clearable:false,
						async valueToTextField (field, value: any): Promise<string> {

							const record = allIdStore.find(r => r.id == value);
							return record ? record.name + '<'+record.email+'>' : "";
						},
						list: list({
							tagName: 'div',
							store: allIdStore,

							listeners: {
								'render': ({target}) => {
									target.store.load();
								}
							},
							renderer: (v: any, _row: HTMLElement, _list: List, _storeIndex: number) => {
								return  v.name + ' <'+v.email+'>';
							}
						})
					}),


					// autocomplete({required:true,label: t('From'), name: 'identityId',
					// 	readOnly: true,
					// 	clearable:false,
					// 	pickerRecordToValue: (field, record) => {
					// 		field.dataSet.account = record.accountId;
					// 		return record.id;
					// 	},
					// 	async valueToTextField (field, value: any): Promise<string> {
					// 	debugger;
					// 		const record = idStores[field.dataSet.accountId].find(r => r.id == value);
					// 		return record ? record.name + '<'+record.email+'>' : "";
					// 	},
					// 	list: list({
					// 		tagName: 'div',
					// 		store: accountStore,
					//
					// 		listeners: {
					// 			'render': ({target}) => {
					// 				target.store.load();
					// 			}
					// 		},
					// 		renderer: (account: any, _row: HTMLElement, _list: List, _storeIndex: number) => {
					// 			idStores[account.id] = store({
					// 				async onLoad(store) {
					// 					const get = await jmapds("Identity").getAll(account.id);
					// 					get.list.forEach((identity: any) => {
					// 						store.add(identity);
					// 					})
					// 				}
					// 			})
					// 			const idList = list({
					// 				store: idStores[account.id],
					// 				cls:'list',
					// 				renderer: (r) => `${r.name} &lt;${r.email}&gt`
					// 			});
					// 			idList.store.load();
					// 			return [h4(account.name), idList];
					// 		}
					// 	})
					// }),
					// todo: add autocomplete for to field. And add last (valid) email on blur
					chips({label: t('To'), name: 'to',
						async textInputToValue (text: string) {
							return {email:text};
						},
						chipRenderer(chip:Component, value: any) {
							chip.text = value.email.htmlEncode();
						}
					}), //chips
					textfield({name: 'subject', placeholder: t('Subject'), style: {fontSize:'1.2em', height:'42px'}}),
					htmlfield({flex:1, name: 'htmlBody', placeholder: t('Type a message'), iframe:true}),
					this.attachmentFld = arrayfield({cls: 'attachments',name: 'attachments',
						buildField: (data) => containerfield({
								tagName: 'a',
								listeners: {
									render({target}) {
										target.el.on('click', () => {
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
		const draftBox = {id:320};

		const form = this.form;
		if (!form.isNew) return true;

		let now = new DateTime(),
			identityId: string = form.value.identityId;
		jmapds('Identity').single(identityId).then((identity: any) => {
			let email = Object.assign(form.value,{
				mailboxIds: {[draftBox.id]: true},
				keywords: {'$seen': true, '$draft': true},
				sentAt: now.format('Y-m-d\TH:i:s'),
				from: [{name: identity.name, email: identity.email}],
				receivedAt: now.format('Y-m-d\TH:i:s')
			});

			if(email.htmlBody) {
				if (email.attachments.length) {
					email.bodyStructure = {
						type: 'multipart/mixed',
						subParts: [{
							type: 'text/html',
							partId: '1'
						}]
					};
				} else {
					email.bodyStructure = {
						type: 'text/html',
						partId:'1'
					};
				}
				email.bodyValues = { '1': {value:email.htmlBody}}
				delete email.htmlBody;
			}
			for(var a of email.attachments) {
				a.disposition = 'attachment';
				// todo: need part id?
				email.bodyStructure.subParts.push(a)
			}
			delete email.identityId;
			delete email.attachments;
			const store = jmapds('Email');
			store.setParams = {accountId: identity.accountId};
			store.create(email).then((r) => {
				if(r.created?.[email.id] && send) {
					this.send(jmapds('Email').single(r.created[email.id].id), identity);
				}
			});
		})

		return false;


	}

	private send(email: any, identity: any) {
		const draftBox = {id:320},
			sentBox = {id:323};
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
		}).then(() => {
			this.close();
		});
	}
}