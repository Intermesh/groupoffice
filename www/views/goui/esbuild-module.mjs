import * as esbuild from 'esbuild';
import { tsPathsPlugin } from './esbuild-resolve-plugin/dist/index.js';

const watch = (process.argv.length > 2 && process.argv[2] == "watch");

const opts = {
	entryPoints: ['src/Index.ts'],
	bundle: true,
	sourcemap: true,
	format: "esm",
	target: "esnext",
	minify: false,
	outdir: "dist",
	plugins: [tsPathsPlugin()],
	logLevel: "info"
}

if(watch) {
	let ctx = await esbuild.context(opts);
	await ctx.watch();
	console.log('Watching...');
} else {

	await esbuild.build(opts);
}