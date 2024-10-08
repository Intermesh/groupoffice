import ts from 'typescript';
export declare function getTsConfig(cwd: string, path: string): ts.ParsedCommandLine;
export declare function computeSearchRegExp(paths: ts.MapLike<string[]>): RegExp;
