# @awalgawe/esbuild-typescript-paths-plugin

A plugin for [esbuild](https://esbuild.github.io/) that resolves TypeScript path aliases during the build process.

## Installation

You can install the plugin using npm or yarn:

```bash
npm install --save-dev @awalgawe/esbuild-typescript-paths-plugin
```

or

```bash
yarn add --dev @awalgawe/esbuild-typescript-paths-plugin
```

## Usage

To use the TypeScript Paths Plugin, you need to import it and add it to your esbuild plugins list. Here's an example of how you can set it up:

```javascript
import { build } from 'esbuild';
import { tsPathsPlugin } from '@awalgawe/esbuild-typescript-paths-plugin';

build({
  entryPoints: ['src/Main.ts'],
  bundle: true,
  outfile: 'dist/bundle.js',
  plugins: [tsPathsPlugin()],
  tsconfig: './path/to/your/tsconfig.json', // Optional: Path to your TypeScript configuration file.
  absWorkingDir: __dirname, // Optional: Absolute working directory for resolving paths.
}).catch(() => process.exit(1));
```

or without bundling:

```javascript
import { build } from 'esbuild';
import { tsPathsPlugin } from '@awalgawe/esbuild-typescript-paths-plugin';

build({
  entryPoints: ['src/**/*.ts'],
  outdir: 'dist',
  plugins: [tsPathsPlugin()],
  tsconfig: './path/to/your/tsconfig.json', // Optional: Path to your TypeScript configuration file.
  absWorkingDir: __dirname, // Optional: Absolute working directory for resolving paths.
}).catch(() => process.exit(1));
```

Make sure you have a valid `tsconfig.json` file with the required path mappings in your project root.

```json
{
  "compilerOptions": {
    // ...
    "baseUrl": ".",
    "paths": {
      "#app/*": ["src/*"]
    }
  }
}
```

## How It Works

The plugin works by intercepting the import statements in your TypeScript files and resolving the path aliases defined in your `tsconfig.json` file. It uses the TypeScript compiler API to parse and resolve the import paths. If an alias is found, it replaces the import path with the actual resolved path during the build process.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

This plugin is inspired by the need to handle TypeScript path aliases in projects built with esbuild.

## Contributing

Contributions are welcome! If you find a bug or want to add a new feature, feel free to open an issue or submit a pull request.

## Alternatives

- [@esbuild-plugins/tsconfig-path](https://www.npmjs.com/package/@esbuild-plugins/tsconfig-paths)
- [esbuild-plugin-alias](https://www.npmjs.com/package/esbuild-plugin-alias)
- [esbuild-plugin-alias-path](https://www.npmjs.com/package/esbuild-plugin-alias-path)
- [esbuild-plugin-path-alias](https://www.npmjs.com/package/esbuild-plugin-path-alias)
- [esbuild-plugin-tsconfig-paths](https://www.npmjs.com/package/esbuild-plugin-tsconfig-paths)
