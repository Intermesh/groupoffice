import * as esbuild from 'esbuild';
import fs from 'node:fs';
/**
 * Usage:
 *
 *  "scripts": {
 *     "start": "npm run build:clean && concurrently --kill-others \"npm run start:ts\"",
 *     "start:ts": "node ../../../../../../views/goui/esbuild-module.mjs watch script/Index.ts,script/Participant.ts",
 *
 *
 *     "build": "npm run build:clean && npm run build:ts",
 *     "build:clean": "rm -rf ./dist/*",
 *     "build:ts": "node ../../../../../../views/goui/esbuild-module.mjs build script/Index.ts,script/Participant.ts",
 *
 *     "test": "npx tsc --noEmit"
 *   }
 * @type {boolean}
 */

const watch = (process.argv.length > 2 && process.argv[2] == "watch");

// Are we building from a legacy module in www/modules/NAME ??
const legacy = process.env.INIT_CWD.indexOf('go/modules') === -1;

const entryPoints = process.argv.length > 3 ? process.argv[3].split(",") : ['script/Index.ts'];


let version, chdir = legacy ? "../../../../" : "../../../../../../";
await fs.readFile(chdir + "version.php", 'utf8', (err, data) => {
	if (err) {
		console.error(err);
		throw err;
	}

	version = data.match(/\d+\.\d+\.\d+/)
	console.log(version[0]);
});

//First I used this plugin to resolve paths: https://github.com/Awalgawe/esbuild-typescript-paths-plugin/tree/main/src
//But this seems much simpler and give more control. It must align with tsconfig.module.js though.
const moduleResolverPlugin = {

 	name: "module-resolve",

	setup(build) {
		build.onResolve({ filter: new RegExp("@intermesh\/.*")}, args => {
				const parts = args.path.split("/");


				if(parts.length === 3) {

					// import is a module. eg. @intermesh/community/calendar
					const pkg = legacy ? "go/modules/" + parts[1] : parts[1], module = parts[2];

					return {
						external: true,
						path: "../../../../../" + pkg + "/" + module + "/views/goui/dist/Index.js?v=" + version[0]
					}
				} else {
					// import is a core. eg. @intermesh/goui or @intermesh/groupoffice-core
					return {
						external: true,
						path: "../../../../../../../views/goui/dist/" + parts[1] + "/script/index.js?v=" + version[0]
					}
				}

		});
	}
}

const opts = {
	entryPoints: entryPoints,
	bundle: true,
	sourcemap: true,
	format: "esm",
	target: "esnext",
	minify: !watch,
	outdir: "dist",
	plugins: [moduleResolverPlugin],
	logLevel: "info"
}

if(watch) {
	let ctx = await esbuild.context(opts);
	await ctx.watch();
	console.log('Watching...');
} else {

	await esbuild.build(opts);
}