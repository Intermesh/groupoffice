"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.tsPathsPlugin = void 0;
const Plugin_1 = __importDefault(require("./Plugin"));
function tsPathsPlugin() {
    const { name, setup } = new Plugin_1.default();
    return { name, setup };
}
exports.tsPathsPlugin = tsPathsPlugin;
//# sourceMappingURL=index.js.map