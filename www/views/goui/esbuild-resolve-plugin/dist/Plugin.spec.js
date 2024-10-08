"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const esbuild_1 = require("esbuild");
const node_path_1 = require("node:path");
const Plugin_1 = __importDefault(require("./Plugin"));
const globals_1 = require("@jest/globals");
(0, globals_1.describe)('Plugin', () => {
    (0, globals_1.it)('should work', async () => {
        const plugin = new Plugin_1.default();
        // @ts-expect-error Mocking esbuild
        const esbuild = {
            onLoad: () => { },
            onResolve: () => { },
            initialOptions: {
                absWorkingDir: (0, node_path_1.resolve)(process.cwd(), 'assets'),
                tsconfig: './tsconfig.json',
            },
        };
        await plugin.setup(esbuild);
        const result = await plugin.onLoad({
            namespace: 'file',
            suffix: 'js',
            path: (0, node_path_1.resolve)(process.cwd(), 'assets/src/test-imports.ts'),
            pluginData: {},
        });
        (0, globals_1.expect)(result?.contents).toMatchSnapshot('yolo');
    });
    (0, globals_1.it)('build bundle', async () => {
        const { name, setup } = new Plugin_1.default();
        const result = await (0, esbuild_1.build)({
            absWorkingDir: (0, node_path_1.resolve)(process.cwd(), 'assets'),
            tsconfig: './tsconfig.json',
            entryPoints: [(0, node_path_1.resolve)(process.cwd(), 'assets/src/a')],
            plugins: [{ name, setup }],
            target: 'es2022',
            format: 'esm',
            logLevel: 'info',
            outdir: 'toto',
            bundle: true,
            write: false,
        });
        (0, globals_1.expect)(result.errors).toStrictEqual([]);
    });
    (0, globals_1.it)('build without bundling', async () => {
        const { name, setup } = new Plugin_1.default();
        const result = await (0, esbuild_1.build)({
            absWorkingDir: (0, node_path_1.resolve)(process.cwd(), 'assets'),
            tsconfig: './tsconfig.json',
            entryPoints: [(0, node_path_1.resolve)(process.cwd(), 'assets/src/a/**/*')],
            plugins: [{ name, setup }],
            target: 'es2022',
            format: 'esm',
            logLevel: 'info',
            outdir: 'toto',
            bundle: false,
            write: false,
        });
        (0, globals_1.expect)(result.errors).toStrictEqual([]);
    });
});
//# sourceMappingURL=Plugin.spec.js.map