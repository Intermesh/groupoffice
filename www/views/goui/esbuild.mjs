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

await esbuild.build({
  entryPoints: ['goui/script/index.ts', 'groupoffice-core/script/index.ts'],
  bundle: true,
	sourcemap: true,
	format: "esm",
	target: "esnext",
	outdir: "dist",
	plugins: [importPathPlugin],
})