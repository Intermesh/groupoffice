/* global go, Ext, dp, t */

/**
 * Create/edit dialog for a module Release. A release belongs to a module-type
 * Product (productId) and targets one Group-Office version BRANCH (goVersion,
 * e.g. "6.8"). The ZIP package is uploaded via go.form.FileField (a text field +
 * "Browse..." button with auto-upload) — OR by dragging a .zip anywhere onto the
 * dialog, which reuses the same upload path (FileField.startUpload).
 *
 * The dialog title reflects the release as it is edited: "Release: [branch]
 * Module version" (e.g. "Release: [6.8] Tours 1.0").
 */
go.modules.community.marketplaceserver.ReleaseDialog = Ext.extend(go.form.Dialog, {
	entityStore: "MarketplaceServerRelease",
	title: t("Release", "marketplaceserver", "community"),
	redirectOnSave: false, // manager-only entity — no detail route to navigate to
	width: dp(800),
	height: dp(800),

	/**
	 * Branch options from the module settings ("6.8,25,26"), for the goVersion
	 * dropdown. Falls back to a sane default if settings aren't loaded.
	 *
	 * @return {Array} rows of [branch]
	 */
	branchData: function () {
		var mod = go.Modules.get("community", "marketplaceserver"),
			raw = (mod && mod.settings && mod.settings.supportedGoBranches) || '6.8,25,26';
		return String(raw).split(',')
			.map(function (s) { return s.trim(); })
			.filter(function (s) { return s.length > 0; })
			.map(function (s) { return [s]; });
	},

	initFormItems: function () {
		var me = this;

		// The module-type product this is a release of. hiddenName so the product
		// id (not the title) is submitted under productId.
		me.moduleCombo = new Ext.form.ComboBox({
			hiddenName: 'productId',
			fieldLabel: t("Module", "marketplaceserver", "community"),
			store: me.productStore = new go.data.Store({
				fields: ['id', 'title', 'moduleName', 'type'],
				entityStore: "MarketplaceServerProduct",
				sortInfo: {field: 'title', direction: 'ASC'}
			}),
			valueField: 'id',
			displayField: 'title',
			mode: 'remote',
			triggerAction: 'all',
			editable: false,
			forceSelection: true,
			allowBlank: false
		});

		me.branchCombo = new Ext.form.ComboBox({
			hiddenName: 'goVersion',
			fieldLabel: t("Group-Office branch", "marketplaceserver", "community"),
			store: new Ext.data.ArrayStore({fields: ['branch'], data: me.branchData()}),
			valueField: 'branch',
			displayField: 'branch',
			mode: 'local',
			triggerAction: 'all',
			editable: true,          // allow typing a branch not yet in the list
			forceSelection: false,
			allowBlank: false
		});

		me.versionField = new Ext.form.TextField({
			name: 'version',
			fieldLabel: t("Version", "marketplaceserver", "community"),
			allowBlank: false
		});

		me.packageField = new go.form.FileField({
			name: 'blobId',
			fieldLabel: t("Package (ZIP)", "marketplaceserver", "community"),
			autoUpload: true,
			accept: '.zip,application/zip',
			allowBlank: false, // a release without its ZIP is meaningless (blobId is NOT NULL)
			anchor: '100%'
		});

		return [{
			xtype: 'fieldset',
			defaults: {anchor: '100%'},
			items: [
				me.moduleCombo,
				me.branchCombo,
				me.versionField,
				{
					xtype: 'textarea',
					name: 'changelog',
					fieldLabel: t("Changelog", "marketplaceserver", "community"),
					height: dp(120)
				},
				me.packageField,
				{
					xtype: 'box',
					style: 'padding:2px 0 6px; color:var(--fg-secondary-text);',
					html: '<small>' + Ext.util.Format.htmlEncode(
						t("…or drag & drop a .zip package anywhere onto this dialog.", "marketplaceserver", "community")
					) + '</small>'
				},
				{
					// datetime (not just date): a release records when it went live.
					// Defaulted to "now" on create via formValues from the caller
					// (ReleaseGrid Add); the server backstops an empty value to now.
					xtype: 'datetimefield',
					name: 'publishedAt',
					fieldLabel: t("Published at", "marketplaceserver", "community")
				},
				{
					xtype: 'xcheckbox',
					name: 'active',
					fieldLabel: t("Active", "marketplaceserver", "community"),
					checked: true
				}
			]
		}];
	},

	initComponent: function () {
		go.modules.community.marketplaceserver.ReleaseDialog.superclass.initComponent.call(this);

		// Only module-type products can hold releases.
		this.productStore.setFilter('type', {type: 'module'});

		// After the ZIP finishes uploading, pull the module's CHANGELOG.md out of
		// the package and pre-fill the changelog — but only when it's still empty.
		this.packageField.on('change', this.onPackageUploaded, this);

		// Preload the module products so the combo can resolve the productId to
		// the module TITLE on edit (a remote combo that gets setValue(id) before
		// its store is loaded shows the raw id, e.g. "1").
		this.on('show', function () { this.productStore.load(); }, this, {single: true});

		// Keep the dialog title in sync with the release being edited.
		this.moduleCombo.on('select', this.updateTitle, this);
		this.productStore.on('load', function () {
			if (this.copyFrom && !this._copyApplied) {
				// "Release new version": pre-fill module + branch from the source
				// release so only version + ZIP need filling in.
				this._copyApplied = true;
				this.moduleCombo.setValue(this.copyFrom.productId);
				this.branchCombo.setValue(this.copyFrom.goVersion);
			} else {
				// Edit flow: re-resolve the loaded productId to the module title.
				var v = this.moduleCombo.getValue();
				if (v !== null && v !== '') {
					this.moduleCombo.setValue(v);
				}
			}
			this.updateTitle();
		}, this);
		this.branchCombo.on('select', this.updateTitle, this);
		this.branchCombo.on('change', this.updateTitle, this);
		this.branchCombo.on('keyup', this.updateTitle, this);
		this.versionField.on('change', this.updateTitle, this);
		this.versionField.on('keyup', this.updateTitle, this);
		this.on('load', this.updateTitle, this);

		// Drag & drop a .zip anywhere onto the dialog to upload it.
		this.on('afterrender', this.initDropZone, this);
	},

	/**
	 * Rebuild the title as "Release: [branch] Module version" from the current
	 * field values, falling back to the plain "Release" label when nothing is set yet.
	 *
	 * @return {void}
	 */
	updateTitle: function () {
		var branch = this.branchCombo.getValue(),
			version = this.versionField.getValue(),
			moduleTitle = this.moduleCombo.getRawValue(),
			label = t("Release", "marketplaceserver", "community"),
			parts = [];

		if (branch) { parts.push('[' + branch + ']'); }
		if (moduleTitle) { parts.push(moduleTitle); }
		if (version) { parts.push(version); }

		// Keep the "Release" prefix + the framework's ": " separator (go.form.Dialog
		// appends "title: value"), then the compact "[branch] Module version" descriptor.
		this.setTitle(parts.length
			? label + ': ' + Ext.util.Format.htmlEncode(parts.join(' '))
			: label);
	},

	/**
	 * Wire drag & drop of a .zip onto the whole dialog body. A dropped file is
	 * routed through the package FileField's own upload path (startUpload), so it
	 * behaves exactly like picking it via "Browse..." (auto-upload -> blobId ->
	 * changelog prefill).
	 *
	 * @return {void}
	 */
	initDropZone: function () {
		var me = this,
			body = me.body;
		if (!body || !body.dom) {
			return;
		}
		var dom = body.dom,
			depth = 0,
			highlight = function (on) {
				body.setStyle('box-shadow', on ? 'inset 0 0 0 2px var(--c-primary)' : '');
			};

		dom.addEventListener('dragenter', function (e) {
			e.preventDefault();
			e.stopPropagation();
			depth++;
			highlight(true);
		});
		dom.addEventListener('dragover', function (e) {
			e.preventDefault();
			e.stopPropagation();
		});
		dom.addEventListener('dragleave', function (e) {
			e.preventDefault();
			e.stopPropagation();
			depth = Math.max(0, depth - 1);
			if (depth === 0) { highlight(false); }
		});
		dom.addEventListener('drop', function (e) {
			e.preventDefault();
			e.stopPropagation();
			depth = 0;
			highlight(false);

			var files = e.dataTransfer && e.dataTransfer.files;
			if (!files || !files.length) {
				return;
			}
			var file = files[0],
				name = (file.name || '').toLowerCase(),
				isZip = name.slice(-4) === '.zip' ||
					file.type === 'application/zip' ||
					file.type === 'application/x-zip-compressed';

			if (!isZip) {
				Ext.MessageBox.alert(
					t("Package (ZIP)", "marketplaceserver", "community"),
					t("Please drop a .zip package file.", "marketplaceserver", "community")
				);
				return;
			}
			me.packageField.startUpload([file]);
		});
	},

	/**
	 * @param {go.form.FileField} field
	 * @param {String} blobId the server-assigned blob id (empty if cleared)
	 * @return {void}
	 */
	onPackageUploaded: function (field, blobId) {
		if (!blobId) {
			return;
		}
		var me = this,
			changelogField = this.formPanel.getForm().findField('changelog');

		if (changelogField && changelogField.getValue()) {
			return; // don't overwrite an existing changelog
		}

		go.Jmap.request({
			method: "MarketplaceServerRelease/readPackageInfo",
			params: {blobId: blobId},
			callback: function (options, success, response) {
				if (!success || !response || !response.changelog) {
					return;
				}
				if (changelogField && !changelogField.getValue()) {
					changelogField.setValue(response.changelog);
				}
			},
			scope: me
		});
	}
});
