"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.computeSearchRegExp = exports.getTsConfig = void 0;
const node_path_1 = require("node:path");
const node_fs_1 = require("node:fs");
const typescript_1 = __importDefault(require("typescript"));
function getTsConfig(cwd, path) {
    const resolvedTsConfigPath = (0, node_path_1.resolve)(cwd, path);
    const { config: json, error } = typescript_1.default.readConfigFile(resolvedTsConfigPath, (path) => (0, node_fs_1.readFileSync)(path).toString('utf-8'));
    if (error) {
        throw Object.assign(new Error(typescript_1.default.formatDiagnostic(error, {
            getCanonicalFileName: (path) => path,
            getCurrentDirectory: () => cwd,
            getNewLine: () => '\n',
        })), { json });
    }
    return typescript_1.default.parseJsonConfigFileContent(json, typescript_1.default.sys, cwd);
}
exports.getTsConfig = getTsConfig;
function computeSearchRegExp(paths) {
    return new RegExp(`(${Object.keys(paths)
        .map((p) => p.replace('*', '(.*)'))
        .join('|')})`);
}
exports.computeSearchRegExp = computeSearchRegExp;
//# sourceMappingURL=tools.js.map