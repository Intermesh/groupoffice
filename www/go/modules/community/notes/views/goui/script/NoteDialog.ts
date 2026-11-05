import {
	btn,
	Button,
	comp,
	Fieldset,
	fieldset, hiddenfield,
	HiddenField,
	HtmlField,
	htmlfield,
	Notifier,
	root,
	t,
	tbar,
	TextField,
	textfield,
	Toolbar,
	Window
} from "@intermesh/goui";
import {
	client,
	customFields,
	FormFieldset,
	FormWindow,
	Image,
	MarkdownField,
	markdownfield,
	TextType
} from "@intermesh/groupoffice-core";
import {notebookcombo} from "./NoteBookCombo";
import {Note} from "./Index";
import {Encrypt} from "./Encrypt";

export class NoteDialog extends FormWindow<Note> {
	private contentFld!: HtmlField | MarkdownField;
	private contentFieldset: Fieldset;
	private typeFld: HiddenField;
	private typeBtn: Button;

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

		this.typeBtn = btn({
			title: t("Switch between HTML and Markdown editor"),
			handler: () => {
				void this.switchType();
			}
		});

		this.generalTab.cls = "fit";
		this.generalTab.items.add(
			hiddenfield({name: "tmpFiles"}), //savemailas module uses this.
			this.typeFld = hiddenfield({name: "type"}),
			this.contentFieldset = fieldset({cls: "fit vbox gap"},
				comp({cls: "hbox gap wrap"},
					textfield({
						flex: 1,
						name: "name",
						label: t("Name"),
						required: true,
						minWidth: 200
					}),

					notebookcombo({
						width: 240,
						value: client.user.notesSettings.defaultNoteBookId,
					}),
					this.typeBtn,
					encryptBtn
				),
				this.encryptTb = tbar({hidden:true},
					textfield({itemId:'pw',type:'password',label:t('Password'), autocomplete: 'new-password'}),
					textfield({itemId:'pwc',type:'password',label:t('Confirm'), autocomplete: 'new-password'}).on('validate', ({target}) => {
						if(target.value !== (target.previousSibling() as TextField).value) {
							target.setInvalid(t("Passwords do not match"));
						}
					}),
				)
			)
		)

		this.setType(this.defaultType());

		this.form.on("beforeload", ({data}) => {
			// Existing notes keep their own type. Prefilled new notes (eg. from an e-mail) are HTML.
			this.setType(data?.type ?? (data?.content ? "html" : this.defaultType()));
			this.typeBtn.hidden = !this.form.isNew;
		});

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

			if(this.contentFld instanceof HtmlField) {
				void Image.replaceImages(this.contentFld.el).then(() => {
					this.contentFld.trackReset();
				});
			}
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

	private defaultType(): TextType {
		return client.user.notesSettings?.defaultType ?? "html";
	}

	private createContentField(type: TextType) {
		if(type === "markdown") {
			return markdownfield({
				name: "content",
				flex: 1
			});
		}

		return htmlfield({
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
		});
	}

	/**
	 * Set the content type and swap the editor if needed.
	 */
	private setType(type: TextType) {
		this.typeFld.value = type;
		this.typeBtn.icon = type === "markdown" ? "markdown" : "html";

		const isMarkdown = this.contentFld instanceof MarkdownField;
		if(this.contentFld && isMarkdown === (type === "markdown")) {
			return;
		}

		const fld = this.createContentField(type);
		if(this.contentFld) {
			this.contentFieldset.items.replaceAt(this.contentFieldset.items.indexOf(this.contentFld), fld);
		} else {
			this.contentFieldset.items.add(fld);
		}
		this.contentFld = fld;
	}

	/**
	 * Switch the editor of a new note between HTML and Markdown. Content is cleared because it can't be converted.
	 */
	private async switchType() {
		if(!this.contentFld.isEmpty() && !(await Window.confirm(t("Switching the editor will clear the content. Are you sure?")))) {
			return;
		}
		this.setType(this.typeFld.value === "markdown" ? "html" : "markdown");
		this.contentFld.focus();
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