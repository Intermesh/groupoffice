/* global Ext, go, t */

/**
 * Freetext chips input for the supported Group-Office branch list. Each chip is
 * a plain string (e.g. "6.8", "25"); type a value and press Enter (or paste a
 * comma-separated list) to add one. Serialises to/from the comma-separated
 * `supportedGoBranches` settings string via getValue()/setValue(), so it drops
 * straight into the settings form as a normal named field.
 *
 * Self-contained on purpose: go.form.Chips' combo machinery (allowNew) is built
 * for entity-backed values, not freetext tags.
 */
go.modules.community.marketplaceserver.BranchChips = Ext.extend(Ext.Container, {
    isFormField: true,
    name: 'supportedGoBranches',
    layout: 'form',
    autoHeight: true,
    _isDirty: false,

    initComponent: function () {
        var me = this;

        me.dataView = new go.form.ChipsView({
            valueField: 'value',
            displayField: 'display'
        });
        // ChipsView removes the record itself on the delete button click; we
        // only need to flag dirty and re-broadcast the value.
        me.dataView.store.on('remove', function () { me._markDirty(); });

        me.input = new Ext.form.TextField({
            emptyText: t("Type a branch and press Enter, e.g. 6.8", "marketplaceserver", "community"),
            anchor: '100%',
            enableKeyEvents: true,
            submitValue: false,
            listeners: {
                specialkey: function (f, e) {
                    if (e.getKey() === e.ENTER) {
                        e.stopEvent();
                        me._addFromInput();
                    }
                },
                blur: function () { me._addFromInput(); }
            }
        });

        me.items = [me.input, me.dataView];

        go.modules.community.marketplaceserver.BranchChips.superclass.initComponent.call(me);
    },

    _addFromInput: function () {
        var raw = (this.input.getValue() || '').trim();
        this.input.setValue('');
        if (!raw) {
            return;
        }
        var added = false;
        raw.split(',').forEach(function (part) {
            var v = part.trim();
            if (v && this.dataView.store.find('value', v) === -1) {
                this.dataView.store.add([new this.dataView.store.recordType({value: v, display: v})]);
                added = true;
            }
        }, this);
        if (added) {
            this._markDirty();
        }
    },

    _markDirty: function () {
        this._isDirty = true;
        this.fireEvent('change', this, this.getValue());
    },

    getName: function () {
        return this.name;
    },

    isDirty: function () {
        return this._isDirty;
    },

    getValue: function () {
        var out = [];
        this.dataView.store.each(function (r) { out.push(r.get('value')); });
        return out.join(',');
    },

    setValue: function (val) {
        this.dataView.store.removeAll();
        var records = [];
        (val == null ? '' : String(val)).split(',').forEach(function (p) {
            var v = p.trim();
            if (v) {
                records.push(new this.dataView.store.recordType({value: v, display: v}));
            }
        }, this);
        this.dataView.store.add(records);
        this._isDirty = false;
    },

    reset: function () {
        this.dataView.store.removeAll();
        this._isDirty = false;
    },

    // Form-field contract no-ops (branches are always valid; blank is allowed).
    markInvalid: Ext.emptyFn,
    clearInvalid: Ext.emptyFn,
    validate: function () { return true; },
    isValid: function () { return true; }
});

Ext.reg('sfmpsbranchchips', go.modules.community.marketplaceserver.BranchChips);
