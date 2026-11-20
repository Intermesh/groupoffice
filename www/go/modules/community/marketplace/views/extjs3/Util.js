/* global go, Ext */

Ext.ns('go.modules.community.marketplace');

/**
 * go.Jmap.request() whose callback is also called when the HTTP request itself
 * fails. With a `callback`, go.Jmap only invokes it for a JMAP response (and
 * returns no promise); a timeout, a PHP fatal or a dropped connection is lost,
 * which would leave masks up and stall "download all"/"update all" chains
 * forever. So the request is sent through the promise API instead and both
 * outcomes are routed to the callback, with the same arguments as before.
 *
 * @param {Object} options go.Jmap.request options, usually with a `callback`
 * @return {Promise|undefined}
 */
go.modules.community.marketplace.request = function (options) {
    var callback = options.callback,
        scope = options.scope || window;
    if (!callback) {
        return go.Jmap.request(options);
    }
    var promiseOptions = Ext.apply({}, options);
    delete promiseOptions.callback;
    delete promiseOptions.scope;
    go.Jmap.request(promiseOptions).then(function (response) {
        callback.call(scope, options, true, response);
    }, function (error) {
        callback.call(scope, options, false, error || {});
    });
};

/**
 * True for an absolute https URL. Anything the browser navigates to or loads
 * from a remote marketplace server must pass this: a javascript: or data: URL
 * would run in Group-Office's origin.
 *
 * @param {String} url
 * @return {Boolean}
 */
go.modules.community.marketplace.isHttpsUrl = function (url) {
    return typeof url === 'string' && /^https:\/\/[^\s"'<>]+$/i.test(url);
};

/**
 * An `ext:qtip="…"` attribute for untrusted text. Ext.QuickTip renders its text
 * as HTML after the browser has decoded the attribute, so the text is encoded
 * twice: once for the attribute, once for the tooltip body.
 *
 * @param {String} text
 * @return {String}
 */
go.modules.community.marketplace.qtipAttr = function (text) {
    var enc = Ext.util.Format.htmlEncode;
    return 'ext:qtip="' + enc(enc(text || '')).replace(/"/g, '&quot;') + '"';
};
