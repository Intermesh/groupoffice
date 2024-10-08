"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = {
    // resolves from test to snapshot path
    resolveSnapshotPath: (testPath, snapshotExtension) => {
        console.log({ testPath, snapshotExtension });
        return testPath.replace('/dist/', '/src/') + snapshotExtension;
    },
    // resolves from snapshot to test path
    resolveTestPath: (snapshotFilePath, snapshotExtension) => snapshotFilePath
        .replace('/src/', '/dist/')
        .slice(0, -snapshotExtension.length),
    // Example test path, used for preflight consistency check of the implementation above
    testPathForConsistencyCheck: 'some/dist/example.test.js',
};
//# sourceMappingURL=snapshotResolver.js.map