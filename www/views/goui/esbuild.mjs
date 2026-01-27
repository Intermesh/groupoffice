import * as esbuild from 'esbuild';
import fs from 'node:fs';

let version;
const data = fs.readFileSync("../../version.php", 'utf8');
version = data.match(/\d+\.\d+\.\d+/)
console.log(version[0]);

// mark GOUI lib as external and map the path to the main lib
let importPathPlugin = {
	name: 'import-path',
	setup(build) {
		build.onResolve({ filter: /@intermesh\/goui/ }, args => {
			return { path: "../../goui/script/index.js?v=" + version, external: true }
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