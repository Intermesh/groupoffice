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

const opts = {
	entryPoints: ['goui/script/index.ts', 'groupoffice-core/script/index.ts'],
	bundle: true,
	sourcemap: true,
	format: "esm",
	target: "es2015",
	outdir: "dist",
	plugins: [importPathPlugin],
	logLevel: "info"
}

if(process.argv.length > 2 && process.argv[2] == "watch") {
	let ctx = await esbuild.context(opts);
	await ctx.watch();
	console.log('Watching...');
} else {

	await esbuild.build(opts);
}