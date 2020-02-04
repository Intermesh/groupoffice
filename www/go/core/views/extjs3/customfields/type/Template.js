Ext.ns("go.customfields.type");

go.customfields.type.Template = Ext.extend(go.customfields.type.Text, {

    name: "Template",

    label: t("Template"),

    iconCls: "ic-functions",

    prefix: "",
    /**
     * Return dialog to edit this type of field
     *
     * @returns {go.customfields.FieldDialog}
     */
    getDialog: function () {
        return new go.customfields.type.TemplateDialog();
    },

    /**
     * No support for form field
     *
     * @returns {boolean}
     */
    renderFormField: function() {
        return false;
    },
    recursiveReplace: function(tpl,data,prefix = "") {
        var tempPrefix = prefix;

        for (var k in data) {
            if (typeof data[k] == "object" && data[k] !== null) {
                prefix += k + ".";
                tpl = this.recursiveReplace(tpl,data[k],prefix);
                prefix = tempPrefix;
            }else {
                for(var propName in data) {
                    var fullProp = prefix + propName;
                    var replacement = data[propName] != null ? data[propName] : "";
                    var re = new RegExp('{{' + fullProp + '}}', 'g');
                    tpl = tpl.replace(re,replacement);
                }
                prefix = tempPrefix;
            }
        }
        return tpl;
    },

    /**
     * Render's the custom field value for the detail views
     *
     * If nothing is returned then you must manage the value in the function itself
     * by calling detailComponent.setValue();
     *
     * See User.js for an asynchronous example.
     *
     * @param {mixed} value
     * @param {object} data Complete entity
     * @param {object} customfield Field entity from custom fields
     * @param {go.detail.Property} detailComponent The property component that renders the value
     * @returns {string}|undefined
     */
    renderDetailView: function (value, data, customfield, detailComponent) {
        var tpl = customfield.options.template;

        if(tpl.indexOf("createdAtShortYear") != -1) {
            var re = new RegExp('{{createdAtShortYear}}', 'g');
            var date = new Date();
            var fullYear = date.getFullYear(data["ctime"]) + "";
            var lastNumbersYear = fullYear.substring(2,4);
            tpl = tpl.replace(re, lastNumbersYear);
        }

        tpl = this.recursiveReplace(tpl,data);
        return tpl;
    }
});

// go.customfields.CustomFields.registerType(new go.customfields.type.FunctionField());

