/**
 * System settings page to leave entities out of the global search.
 *
 * Saves the core setting "searchExcludedEntities". The server removes the search keywords of excluded entities.
 */
go.systemsettings.SearchPanel = Ext.extend(go.systemsettings.Panel, {
	package: "core",
	module: "core",

	hasPermission: function() {
		return go.User.isAdmin;
	},

	initComponent: function () {

		this.entityNames = this.getSearchableEntityNames();

		this.entityFieldset = new Ext.form.FieldSet({
			title: t("Show in search"),
			items: [{
				xtype: 'box',
				autoEl: 'p',
				html: t("Uncheck the items that you don't want to find in the search at the top. Their keywords are removed from the search index, which saves space and makes searching faster. Existing links to these items keep working, but you can't search for them when you create a link.")
			}, {
				xtype: 'box',
				autoEl: 'p',
				cls: 'info',
				html: "<i class='icon'>info</i> " + t("When you check an item again, run \"Update search index\" in Tools to add it to the search index again.")
			}].concat(this.entityNames.map(function(name) {
				return {
					xtype: 'xcheckbox',
					hideLabel: true,
					boxLabel: go.Entities.get(name).title,
					name: 'search-' + name,
					submit: false,
					checked: true
				};
			}))
		});

		Ext.apply(this, {
			itemId: "search", //makes it routable
			title: t('Search'),
			autoScroll: true,
			iconCls: 'ic-search',
			items: [this.entityFieldset]
		});

		go.systemsettings.SearchPanel.superclass.initComponent.call(this);
	},

	/**
	 * Entities that are in the search index have a link configuration. Some entities have more than one, like
	 * Contact and Organization, so take the unique entity names.
	 */
	getSearchableEntityNames: function() {
		const names = [];
		go.Entities.getLinkConfigs().forEach(function(link) {
			if(names.indexOf(link.entity) === -1) {
				names.push(link.entity);
			}
		});
		names.sort(function(a, b) {
			return go.Entities.get(a).title.localeCompare(go.Entities.get(b).title);
		});
		return names;
	},

	loadSettings: function() {
		go.systemsettings.SearchPanel.superclass.loadSettings.call(this);

		const excluded = go.Modules.get("core", "core").settings.searchExcludedEntities || [], f = this.getForm();
		this.entityNames.forEach(function(name) {
			f.findField('search-' + name).setValue(excluded.indexOf(name) === -1);
		});
		this.originalExcluded = this.getExcluded();
	},

	getExcluded: function() {
		const f = this.getForm();
		return this.entityNames.filter(function(name) {
			return !f.findField('search-' + name).getValue();
		});
	},

	onSubmit: function (cb, scope) {
		const excluded = this.getExcluded(), me = this;

		if(this.originalExcluded === undefined || excluded.join(" ") === this.originalExcluded.join(" ")) {
			// we need to use settimeout because we want to fire the callback on the next loop. Otherwise system settings will submit too early.
			setTimeout(function() {
				cb.call(scope, me, true);
			}, 0);
			return;
		}

		const module = go.Modules.get("core", "core"), p = {update: {}};
		p.update[module.id] = {settings: {searchExcludedEntities: excluded}};

		go.Db.store("Module").set(p, function (options, success) {
			cb.call(scope, this, success);
		}, scope);
	}
});
