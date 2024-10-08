"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const node_path_1 = require("node:path");
const typescript_1 = __importDefault(require("typescript"));
const tools_1 = require("./tools");
class TsPathsPlugin {
    constructor() {
        this.name = 'ts-paths';
        this.setup = this.setup.bind(this);
        this.onResolve = this.onResolve.bind(this);
    }
    async setup(build) {
        this.tsConfig = await (0, tools_1.getTsConfig)(build.initialOptions.absWorkingDir ?? process.cwd(), build.initialOptions.tsconfig ?? './tsconfig.json');
        if (!this.tsConfig?.options.paths) {
            throw new Error('tsconfig.json does not contain any path mappings');
        }
        this.program = typescript_1.default.createProgram({
            rootNames: this.tsConfig.fileNames,
            options: this.tsConfig.options,
            projectReferences: this.tsConfig.projectReferences,
            configFileParsingDiagnostics: this.tsConfig.errors,
        });
        this.searchRegExp = (0, tools_1.computeSearchRegExp)(this.tsConfig.options.paths);
        build.onResolve({ filter: this.searchRegExp }, this.onResolve);
    }
    onResolve(args) {
        const sourceFile = this.program?.getSourceFile(args.importer);
        if (!sourceFile) {
            throw new Error(`Unable to find source file "${args.importer}"`);
        }
        const resolvedPath = this.resolveAlias(sourceFile, args.path, false);
        if (!resolvedPath) {
            return null;
        }
        return {
            path: resolvedPath,
            external: true
        };
    }
    resolveAlias(sourceFile, imported, absolute = false) {
        if (!this.tsConfig?.options.paths) {
            throw new Error('tsconfig.json does not contain any path mappings');
        }
        if (!this.program) {
            throw new Error('Program should be defined at this point');
        }
        const { resolvedModule } = typescript_1.default.resolveModuleName(imported, sourceFile.fileName, this.tsConfig.options, typescript_1.default.sys);
        if (!resolvedModule) {
            throw new Error(`Unable to resolve "${imported}" from "${sourceFile.fileName}" with ${this.tsConfig.options.configFilePath}`);
        }
        const { resolvedFileName } = resolvedModule;
        if (absolute) {
            return resolvedFileName;
        }
        let relativePath = (0, node_path_1.relative)((0, node_path_1.dirname)(sourceFile.fileName), resolvedFileName);
        relativePath = relativePath.substring(0, relativePath.length - 4) + "js";
        return relativePath.startsWith('.') ? relativePath : `./${relativePath}`;
    }
}
exports.default = TsPathsPlugin;
//# sourceMappingURL=Plugin.js.map