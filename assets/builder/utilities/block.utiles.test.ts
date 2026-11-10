import { describe, expect, it } from "vitest";
import { createBlockFromTemplate, takePendingFocus } from "./block.utiles";
import { blockDefinitions } from "../components/Blocks/Definition";

describe("focus du bloc ajouté", () => {
    it("seul le dernier bloc créé prend le focus, une seule fois", () => {
        const first = createBlockFromTemplate(blockDefinitions.TextInput);
        const last = createBlockFromTemplate(blockDefinitions.Title);

        expect(takePendingFocus(first.id)).toBe(false);
        expect(takePendingFocus(last.id)).toBe(true);
        expect(takePendingFocus(last.id)).toBe(false);
    });
});
