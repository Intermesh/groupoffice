
(function() {
    function checkArray(part) {
        var matches = part.match(/\[([0-9]*)\]/);
        if(!matches) {
            return -1;
        }

        return matches[1];
    }

    function traverse(obj, part, value) {
        var arrayIndex = checkArray(part);
        if(arrayIndex != -1) {
            part = part.replace(/\[[0-9]*\]/, "");

            if (!obj[part] || !Ext.isArray(obj[part])) {
                if(Ext.isDefined(value)) {
                    obj[part] = [];
                } else
                {
                    return null;
                }
            }

            if(arrayIndex === "") {
                arrayIndex = obj[part].length;
            }
            if(!obj[part][arrayIndex]) {
                if(Ext.isDefined(value)) {
                    obj[part][arrayIndex] = value;
                } else
                {
                    return null;
                }
            }
            obj = obj[part][arrayIndex];
        } else {

            if (!obj[part]) {
                if(!Ext.isDefined(value)) {
                    return null;
                }
                obj[part] = value;
            }
            if(Ext.isDefined(value)) {
                obj[part] = value;
            }
            obj = obj[part];
        }

        return obj;
    }


    go.util.Object = {
        /**
         * Set's value on the object to the give path.
         *
         * eg.
         *
         * var o = {};
         *
         * o.applyPath("foo.bar", "test");
         * o.applyPath("foo.anArray[]", "test");
         *
         * will result in:
         *
         * {"foo": {"bar": "test", "anArray": ["test"]}
         *
         * @param path eg foo.bar
         * @param value
         * @return Deepest child
         */
        applyPath : function(obj, path, value) {

            var parts = path.split(".");
            var last = parts.pop();
            var ret;

            parts.forEach(function(part) {
                ret = obj = traverse(obj, part);
            });

            traverse(obj, last, value);

            return ret;
        },

        fetchPath : function(obj, path) {
            var parts = path.split(".");
            var last = parts.pop();

            for(var i = 0, l = parts.length; i < l; i++) {
                if(!traverse(obj, parts[i])) {
                    return null;
                }
            }

            return traverse(obj, last, value);
        }
    };

})();