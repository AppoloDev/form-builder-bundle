import { describe, expect, it } from "vitest";
import { useFormBuilderStore } from "./block.store";

describe("chargement des blocs", () => {
    it("un groupe ou un répétable sans `children` en reçoit un vide", () => {
        useFormBuilderStore.getState().setBlocks([
            {id: "g", type: "FieldSet"},
            {id: "r", type: "Repeatable", children: [{id: "f", type: "FieldSet"}]},
        ] as any);

        const [fieldSet, repeatable] = useFormBuilderStore.getState().blocks as any[];
        expect(fieldSet.children).toEqual([]);
        expect(repeatable.children[0].children).toEqual([]);
    });
});
