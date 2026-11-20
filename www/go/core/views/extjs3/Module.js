go.Modules.register("core", 'core', {
	title: t("Core"),
	entities: [
		{
			name: 'Alert',
			relations: {
				user: {store: "Principal", fk:'userId'}
			}
		},
		{
			name: 'Group',
			relations: {
				users: {store: "Principal", fk: "users"},
				user: {store: "Principal", fk:'isUserGroupFor'}
			}
		},

		{
			name: 'User',
			filters: [
				{
					wildcards: false,
					name: 'text',
					type: "string",
					multiple: false,
					title: t("Query")
				},
				{
					title: t("Comment"),
					name: 'comment',
					multiple: true,
					type: 'string'
				},
				{
					title: t("Commented at"),
					name: 'commentedat',
					multiple: false,
					type: 'date'
				}, {
					title: t("Modified at"),
					name: 'modifiedat',
					multiple: false,
					type: 'date'
				}, {
					title: t("Modified by"),
					name: 'modifiedBy',
					multiple: true,
					type: 'string'
				}, {
					title: t("Created at"),
					name: 'createdat',
					multiple: false,
					type: 'date'
				}, {
					title: t("Created by"),
					name: 'createdby',
					multiple: true,
					type: 'string'
				},
				{
					title: t("Username"),
					name: 'username',
					multiple: true,
					type: 'string'
				},{
					title: t("Display name"),
					name: 'displayName',
					multiple: true,
					type: 'string'
				},{
					title: t("E-mail"),
					name: 'email',
					multiple: true,
					type: 'string'
				},
			]

		},
		'Principal',
		'Field', 
		{
			name: 'FieldSet', 
			title: t("Custom field set")
		}, 
		'Module',
		{
			name: 'Link',
			relations: {
				to: {store: "Search", fk: "toSearchId"}
			}
		}, 
		'Search', 
		'EntityFilter',
		'SmtpAccount',
		'EmailTemplate',
		'PdfTemplate',
		'ImportMapping',
		'CronJobSchedule',
		{
			name: 'AuthAllowGroup',
			relations: {
				group: {store: "Group", fk:'groupId'}
			}
		},
		'OauthClient',
		'SpreadSheetExport'
	],

	userSettingsPanels: [
		"go.users.UserGroupGrid"
	],
	selectDialogPanels: [
		"go.users.SelectDialogPanel"
	],

	customFieldTypes: [
		"go.customfields.type.Checkbox",
		"go.customfields.type.Date",
		"go.customfields.type.DateTime",
		"go.customfields.type.EncryptedText",
		"go.customfields.type.FunctionField",
		"go.customfields.type.Group",
		"go.customfields.type.Html",
		"go.customfields.type.MultiSelect",
		"go.customfields.type.Attachments",
		"go.customfields.type.Notes",
		"go.customfields.type.Number",
		"go.customfields.type.Select",
		"go.customfields.type.Text",
		"go.customfields.type.TextArea",
		"go.customfields.type.Data",
		"go.customfields.type.User",
		"go.customfields.type.YesNo",
		"go.customfields.type.TemplateField"
	]
});


/**
 * Open the global search
 *
 * @param {string} query Search for this text right away
 */
go.util.search = function (query) {
	groupofficeCore.main.search(query);
};
