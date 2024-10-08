"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const globals_1 = require("@jest/globals");
const tools_1 = require("./tools");
(0, globals_1.describe)('Tools', () => {
    (0, globals_1.describe)(tools_1.replacePath.name, () => {
        (0, globals_1.it)('should replace paths', () => {
            const input = `
        import a from "@app/a";
        import b from "@app/a/b";
        import c from "@app/c";

        export * from "@app/a";
        `;
            const mapping = {
                '@app/a': 'src/a',
                '@app/a/b': 'src/a/b',
                '@app/c': 'src/c',
            };
            const expectedResult = `
        import a from "src/a";
        import b from "src/a/b";
        import c from "src/c";

        export * from "src/a";
        `;
            const result = (0, tools_1.replacePath)(input, mapping);
            (0, globals_1.expect)(result).toStrictEqual(expectedResult);
        });
    });
});
//# sourceMappingURL=tools.spec.js.map