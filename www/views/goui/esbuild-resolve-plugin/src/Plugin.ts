import type {OnResolveArgs, OnResolveResult, Plugin, PluginBuild,} from 'esbuild';
import {dirname, relative} from 'node:path';
import ts from 'typescript';
import {computeSearchRegExp, getTsConfig,} from './tools';

export default class TsPathsPlugin implements Plugin {
  readonly name = 'ts-paths';

  tsConfig?: ts.ParsedCommandLine;
  program?: ts.Program;
  searchRegExp?: RegExp;

  constructor() {
    this.setup = this.setup.bind(this);
    this.onResolve = this.onResolve.bind(this);
  }

  async setup(build: PluginBuild) {
    this.tsConfig = await getTsConfig(
      build.initialOptions.absWorkingDir ?? process.cwd(),
      build.initialOptions.tsconfig ?? './tsconfig.json'
    );

    if (!this.tsConfig?.options.paths) {
      throw new Error('tsconfig.json does not contain any path mappings');
    }

    this.program = ts.createProgram({
      rootNames: this.tsConfig.fileNames,
      options: this.tsConfig.options,
      projectReferences: this.tsConfig.projectReferences,
      configFileParsingDiagnostics: this.tsConfig.errors,
    });

    this.searchRegExp = computeSearchRegExp(this.tsConfig.options.paths);

		build.onResolve({ filter: this.searchRegExp }, this.onResolve);
  }

  onResolve(args: OnResolveArgs): OnResolveResult | null {
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

  resolveAlias(sourceFile: ts.SourceFile, imported: string, absolute = false) {
    if (!this.tsConfig?.options.paths) {
      throw new Error('tsconfig.json does not contain any path mappings');
    }

    if (!this.program) {
      throw new Error('Program should be defined at this point');
    }


    const { resolvedModule } = ts.resolveModuleName(
      imported,
      sourceFile.fileName,
      this.tsConfig.options,
      ts.sys
    );

    if (!resolvedModule) {
      throw new Error(
        `Unable to resolve "${imported}" from "${sourceFile.fileName}" with ${this.tsConfig.options.configFilePath}`
      );
    }

    const { resolvedFileName } = resolvedModule;

    if (absolute) {
      return resolvedFileName;
    }

    let relativePath = relative(
      dirname(sourceFile.fileName),
      resolvedFileName
    );

		if(relativePath.substring(relativePath.length - 4) == 'd.ts') {
			relativePath = relativePath.substring(0, relativePath.length - 4) + "js";
		}

    return relativePath.startsWith('.') ? relativePath : `./${relativePath}`;
  }
}
