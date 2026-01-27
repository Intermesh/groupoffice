import {
	btn, Button,
	comp,
	fieldset,
	HtmlField,
	htmlfield,
	Notifier,
	root,
	t,
	tbar,
	TextField,
	textfield, Toolbar, Window
} from "@intermesh/goui";
import {client, customFields, FormFieldset, FormWindow, Image} from "@intermesh/groupoffice-core";
import {notebookcombo} from "./NoteBookCombo";
import {Note} from "./Index";
import {Encrypt} from "./Encrypt";

export class NoteDialog extends FormWindow<Note> {
	private contentFld: HtmlField;

	private encryptTb: Toolbar
	private pw?: string // if set, will be used to decrypt content
	constructor() {
		super("Note");

		this.title = t("Note");

		this.stateId = "note-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.hasLinks = true;

		this.width = 800;
		this.height = 800;

		const encryptBtn = btn({icon:'lock', title: t('Encrypt'), hidden: !crypto.subtle}).on('click',e => {
			this.toggleEncrypt(e.target);
		});

		this.generalTab.cls = "fit";
		this.generalTab.items.add(
			fieldset({cls: " fit vbox gap"},
				comp({cls: "hbox gap"},
					textfield({
						flex: 1,
						name: "name",
						label: t("Name"),
						required: true
					}),

					notebookcombo({
						width: 240
					}),
					encryptBtn
				),
				this.encryptTb = tbar({hidden:true},
					textfield({itemId:'pw',type:'password',label:t('Password')}),
					textfield({itemId:'pwc',type:'password',label:t('Confirm')}).on('validate', ({target}) => {
						return target.value === (target.previousSibling() as TextField).value;
					}),
				),
				this.contentFld = htmlfield({
					name: "content",
					flex: 1,
					listeners: {

						insertimage: ({file, img}) => {
							root.mask();

							client.upload(file).then(r => {
								if (img) {
									img.dataset.blobId = r.id;
									img.removeAttribute("id");
								}
								Notifier.success(`Uploaded ${file.name} successfully`);
							}).catch((err) => {
								console.error(err);
								Notifier.error(`Failed to upload ${file.name}`);
							}).finally(() => {
								root.unmask();
							});
						}

					}
				})
			)
		)

		this.form.on("load", ({data}) => {

			if(Encrypt.isEncrypted(data.content)) {
				this.toggleEncrypt(encryptBtn);
				this.setPassword(Encrypt.lastPass);
				if(this.pw) {
					Encrypt.aesGcmDecrypt(data.content, this.pw).then(decryptedText => {
						this.contentFld.value = decryptedText;
					}).catch(e => {
						Encrypt.prompt(data.content).then(decryptedText => {
							this.setPassword(Encrypt.lastPass);
							this.contentFld.value = decryptedText;
						}).catch(e => {
							this.close();
						});
					})
				} else {
					Encrypt.prompt(data.content).then(decryptedText => {
						this.setPassword(Encrypt.lastPass);
						this.contentFld.value = decryptedText;
					})
				}

			}

			void Image.replaceImages(this.contentFld.el).then(() => {
				this.contentFld.trackReset();
			})
		})

		this.form.on("beforesave", ({data}) => {
			const pw =  this.encryptTb.hidden ? null : (this.encryptTb.findChild("pw") as TextField)!.value;
			if(pw && data.content) {
				if (!Encrypt.isEncrypted(data.content)) {
					Encrypt.aesGcmEncrypt(data.content, pw).then(encryptedText => {
						this.contentFld.value = encryptedText;

						this.form.handler!(this.form);
					});
					return false;
				}
			}
		});

		this.addCustomFields();
	}

	toggleEncrypt(btn: Button) {
		if(this.encryptTb.hidden) {
			btn.icon = 'lock_open';
			this.encryptTb.show();
		} else {
			btn.icon = 'lock';
			this.encryptTb.hide();
		}
	}

	setPassword(pw: string) {
		this.pw = pw;
		(this.encryptTb.findChild("pw") as TextField)!.value = pw;
		(this.encryptTb.findChild("pwc") as TextField)!.value = pw;
	}

	protected addCustomFields() {
		//for notes all are tabs
		const fieldsets = customFields.getFieldSets(this.entityName).map(fs => new FormFieldset(fs))

		fieldsets.forEach((fs) => {
			//if (fs.fieldSet.isTab) {
				fs.title = fs.fieldSet.name;
				fs.legend = "";
				this.cards.items.add(fs);
		}, this);
	}
}