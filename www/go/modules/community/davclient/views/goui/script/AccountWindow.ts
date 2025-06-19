import {
	checkbox,
	comp, select,
	textfield, t, btn, table, mapfield, containerfield, menu, hr, Window, Format, displayfield
} from "@intermesh/goui";
import {client, FormWindow} from "@intermesh/groupoffice-core";
import {statusIcons} from "@intermesh/community/calendar";
export class AccountWindow extends FormWindow {

	protected closeOnSave = false
	constructor() {
		super('DavAccount');
		this.title = t('Account');
		this.width = 740;
		this.height = 650;

		const enabledCb = checkbox({type:'switch',hidden:true,name:'active',value:true,label:t('Enabled'),
			listeners:{'change':(me,v)=> {this.submitBtn.text = t(v ? "Connect":"Save")}}});

		this.generalTab.items.add(comp({cls:'flow pad'},
				enabledCb,
				textfield({name:'name', label: t('Name')}),
				textfield({name:'host', label: t('Host')}),
				// textfield({name:'principalUri', label: t('Path')}),
				textfield({name:'username', label: t('Username')}),
				textfield({name:'password', label: t('Password'), type:'password'}),
				//textfield({name:'uri', readOnly:true, label: t('Common name')}),
				select({name:'refreshInterval', label: t('Refresh calendars'), value:15,options: [
					{name: t('Every quarter'), value: 15},
					{name: t('Every hour'), value: 60}
				]}),
				displayfield({itemId: 'lastError',name:'lastError', cls:'warning'})
			)
		);

		this.submitBtn.text = t("Connect");
		this.generalTab.title = t('Server');

		this.cards.items.add(comp({title:t('Collections')},
			mapfield({name: 'collections',
				buildField: (v: any) => {

				const icon = v.lastError ? comp({tagName:'i',cls:'icon',html:'warning', title:v.lastError, style:{margin:'0 8px'}}) :
					comp({tagName:'i',cls:'icon',html:'event', style:{margin:'0 8px'}});

					const f = containerfield({cls:'hbox', style: {alignItems: 'center', cursor:'default'}},
						icon,
						comp({flex: '1 0 30%',html: v.uri}),
						comp({width:220, html: v.name}),
						comp({width:160, html: v.lastSync ? Format.dateTime(v.lastSync) : t('Never')}),
						btn({icon:'more_vert', menu: menu({},
							btn({icon:'sync',text:t('Sync now'), handler: _ =>{
									icon.mask();
								client.jmap('DavAccount/sync', {accountId:this.form.currentId, collectionId : v.id}).then((response)=> {
									// will account update info
									if(response.collection) {
										v.lastError = response.collection.lastError;
										v.lastSync = response.collection.lastSync;
										this.form.load(this.form.currentId!);
									}

								}).catch((err) => {
									Window.error(err);
								}).finally(() => {
									icon.unmask();
								})
							}}),
							hr(),
							btn({icon:'delete',text:t('Remove from sync'), handler: _ => {
									f.remove();
								}
							})

						)})
					);
					return f;
				}
			})
		));

		this.addSharePanel();

		this.form.on('load' , (m,data) => {
			this.submitBtn.text = t(data.lastError  ? "Connect":"Save")
			enabledCb.hidden = !!data.lastError;
			if(!data.lastError){
				this.submitBtn.text = t("Save");
			}
		});

		this.form.on('beforesave', (me ,data) => {
			// if there was an error reactivate with new details.
			if(me.value.lastError) {
				data.active = true;
			}
		});

		this.form.on('save', (me,e) => {
			// if connect failed. show error and prevent close
			this.form.findField('lastError')!.value = e.lastError
			this.form.trackReset();
			if(!e.lastError && e.active) {
				this.cards.activeItem = 1;
			} else if(!e.lastError && !e.active) {
				this.close();
			}
		});
	}
}