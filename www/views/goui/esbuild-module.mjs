import * as esbuild from 'esbuild';
import { tsPathsPlugin } from './esbuild-resolve-plugin/dist/index.js';

const watch = (process.argv.length > 2 && process.argv[2] == "watch");

const opts = {
	entryPoints: ['script/Index.ts'],
	bundle: true,
	sourcemap: true,
	format: "esm",
	target: "esnext",
	minify: !watch,
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