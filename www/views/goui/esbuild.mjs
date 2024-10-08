import * as esbuild from 'esbuild';

// mark GOUI lib as external and map the path to the main lib
let importPathPlugin = {
	name: 'import-path',
	setup(build) {
		build.onResolve({ filter: /@intermesh\/goui/ }, args => {
			return { path: "../../goui/script/index.js", external: true }
		})
	},
}

const watch = (process.argv.length > 2 && process.argv[2] == "watch");

const opts = {
	entryPoints: ['goui/script/index.ts', 'groupoffice-core/script/index.ts'],
	bundle: true,
	sourcemap: true,
	format: "esm",
	target: "esnext",
	minify: !watch,
	outdir: "dist",
	plugins: [importPathPlugin],
	logLevel: "info"
}

if(watch) {
	let ctx = await esbuild.context(opts);
	await ctx.watch();
	console.log('Watching...');
} else {

	await esbuild.build(opts);
}