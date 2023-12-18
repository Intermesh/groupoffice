"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
exports.NoteDetail = void 0;
var NoteDialog_js_1 = require("./NoteDialog.js");
var goui_1 = require("@intermesh/goui");
var groupoffice_core_1 = require("@intermesh/groupoffice-core");
var NoteDetail = /** @class */ (function (_super) {
    __extends(NoteDetail, _super);
    function NoteDetail() {
        var _this = _super.call(this, "Note") || this;
        _this.scroller.items.add(_this.content = (0, goui_1.comp)({
            cls: "normalize card pad"
        }));
        _this.addCustomFields();
        _this.addComments();
        _this.addFiles();
        _this.addLinks();
        _this.addHistory();
        _this.toolbar.items.add(_this.editBtn = (0, goui_1.btn)({
            icon: "edit",
            title: (0, goui_1.t)("Edit"),
            handler: function (button, ev) {
                var dlg = new NoteDialog_js_1.NoteDialog();
                void dlg.load(_this.entity.id);
                dlg.show();
            }
        }));
        _this.on("load", function (detailPanel, entity) {
            _this.title = entity.name;
            _this.content.items.clear();
            _this.content.items.add(groupoffice_core_1.Image.replace(entity.content));
        });
        return _this;
    }
    return NoteDetail;
}(groupoffice_core_1.DetailPanel));
exports.NoteDetail = NoteDetail;
