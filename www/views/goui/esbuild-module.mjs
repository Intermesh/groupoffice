import * as esbuild from 'esbuild';

const watch = (process.argv.length > 2 && process.argv[2] == "watch");

//First I used this plugin to resolve paths: https://github.com/Awalgawe/esbuild-typescript-paths-plugin/tree/main/src
//But this seems much simpler and give more control. It must align with tsconfig.module.js though.
const moduleResolverPlugin = {

 	name: "module-resolve",

	setup(build) {
		build.onResolve({ filter: new RegExp("@intermesh\/.*")}, args => {

				const parts = args.path.split("/");

				if(parts.length === 3) {

					// import is a module. eg. @intermesh/community/calendar
					const pkg = parts[1], module = parts[2];

					return {
						external: true,
						path: "../../../../../" + pkg + "/" + module + "/views/goui/dist/Index.js"
					}
				} else {
					// import is a core. eg. @intermesh/goui or @intermesh/groupoffice-core
					return {
						external: true,
						path: "../../../../../../../views/goui/dist/" + parts[1] + "/script/index.js"
					}
				}

		});
	}
}

const opts = {
	entryPoints: ['script/Index.ts'],
	bundle: true,
	sourcemap: watch,
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