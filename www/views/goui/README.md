This package builds "goui" and "groupoffice-core" into the "dist" folder.
It makes sure all modules can be loaded in the browser.

GOUI modules must extend the tsconfig.module.json file so @intermesh/* resolves to this package dist folder.


run npm start for development. It will also start watching goui and groupoffice-core
