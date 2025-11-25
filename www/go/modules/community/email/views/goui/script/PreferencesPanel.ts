import {
	checkbox,
	Component,
	containerfield,
	DataSourceForm,
	datasourceform, DataSourceStore,
	datasourcestore, DefaultEntity,
	fieldset, Notifier, radio,
	select
} from "@intermesh/goui";
import {client, JmapDataSource, jmapds, User} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

export class PreferencesPanel extends Component {

	private form: DataSourceForm<User>;
	private identityStore: DataSourceStore<JmapDataSource<DefaultEntity>>;

	constructor() {
		super();
		this.cls = 'fit scroll';

		this.identityStore = datasourcestore({
			dataSource: jmapds('Identity'),
			filters: {default: {accountId: null}},

			sort: [{property:'sortOrder'},{property:'name'}]
		});

		this.form = datasourceform<User>({
				dataSource: jmapds("User"),
				cls: "vbox",
				flex: 1
			},
			containerfield({name: 'emailPreferences'},
				fieldset({legend: t('Display'), flex:'1 1'},



					checkbox({name: 'enableConversations', label: t('Show emails as conversations')}),
					checkbox({name: 'showNewOnTop', label: t('Show unread messages on top')}),
					checkbox({name: 'showPreview', label: t('Show message preview in list')}),
					checkbox({name: 'showAvatar', label: t('Show avatars')}),
					checkbox({name: 'showReadReceipts', label: t('Show read receipt requests')}),

					radio({type: 'button', value: 'readingPane', name: 'readingPane', label:t('Reading pane'),options: [
							{value: 'right', text: t('Side')},
							{value: 'bottom', text: t('Bottom')}
						]}),
					radio({
						name: 'delaySentSeconds', label: t('Undo send'), options: [
							{value: 15, text: t('Delay sending for 15 seconds to undo')},
							{value: 0, text: t('Send immediately')},
						]
					})
					//,checkbox({name:'useTimeZones', label: t('Enable multiple time zone support')}),
				),
				fieldset({legend: t('Compose'), flex:'1 1'},
					select({
						name: 'defaultIdentityId', label: t('Default identity'), store: this.identityStore, valueField: 'id'
					}),
					checkbox({
						name: 'saveUnknownRecipient', label: t('Save unknown recipients'),
						hint: t('Show an add contact dialog after emailing an unknown recipient'),
					}),
					radio({name: 'loadExternalContent', label:t('Load external images'), options: [
						{value: 'always', text: t('Always show')},
						{value: 'knownOnly', text: t('Only for known recipients')},
						{value: 'ask', text: t('Always ask')}
					]}),
					radio({name: 'defaultReplyAll', label: t('Default reply to'), options: [
						{value: '1', text: t('Everyone in the conversation')},
						{value: '0', text: t('Only the sender')},
					]}),
					radio({label: t('Compose format'), name: 'messageStructure', flex: '1 40%', options: [
						{value: 'html', text: t('Rich text')},
						{value: 'plain', text: t('Plain text')}
					]}),
					checkbox({
						name: 'quoteOriginal', label: t('Quote original message')
					}),
					checkbox({
						name: 'structureAsOriginal', label: t('Compose reply in same format'),
						hint: t('Replies to messages in plain text will also be in plain text')
					})
				)
			)
		);


		this.items.add(this.form);
	}

	onLoad(user:User) {
		this.form.value = user;
		this.form.currentId = user.id;

		//this.personalCalendarStore.setFilter('owner', {ownerId: user.id});
		this.identityStore.load().catch(e => Notifier.error(e))

	}

	onSubmit() {
		return this.form.submit()
	}
}