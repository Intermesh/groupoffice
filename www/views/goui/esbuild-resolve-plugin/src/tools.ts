import { resolve } from 'node:path';
import { readFileSync } from 'node:fs';
import ts from 'typescript';

export function getTsConfig(cwd: string, path: string) {
  const resolvedTsConfigPath = resolve(cwd, path);

  const { config: json, error } = ts.readConfigFile(
    resolvedTsConfigPath,
    (path) => readFileSync(path).toString('utf-8')
  );

  if (error) {
    throw Object.assign(
      new Error(
        ts.formatDiagnostic(error, {
          getCanonicalFileName: (path) => path,
          getCurrentDirectory: () => cwd,
          getNewLine: () => '\n',
        })
      ),
      { json }
    );
  }

  return ts.parseJsonConfigFileContent(json, ts.sys, cwd);
}

export function computeSearchRegExp(paths: ts.MapLike<string[]>) {
  return new RegExp(
    `(${Object.keys(paths)
      .map((p) => p.replace('*', '(.*)'))
      .join('|')})`
  );
}
