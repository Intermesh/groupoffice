import type { OnResolveArgs, OnResolveResult, Plugin, PluginBuild } from 'esbuild';
import ts from 'typescript';
export default class TsPathsPlugin implements Plugin {
    readonly name = "ts-paths";
    tsConfig?: ts.ParsedCommandLine;
    program?: ts.Program;
    searchRegExp?: RegExp;
    constructor();
    setup(build: PluginBuild): Promise<void>;
    onResolve(args: OnResolveArgs): OnResolveResult | null;
    resolveAlias(sourceFile: ts.SourceFile, imported: string, absolute?: boolean): string;
}
