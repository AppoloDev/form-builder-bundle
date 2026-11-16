import { describe, expect, it } from "vitest";
import { moveBlockInTree, useFormBuilderStore } from "./block.store";

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

describe("déplacement d'un bloc dans l'arbre", () => {
    const tree = () => [
        {id: "a", type: "TextInput"},
        {id: "g", type: "FieldSet", children: [{id: "b", type: "TextInput"}]},
        {id: "s", type: "Select", options: [], conditions: [{id: "r", operator: "is", optionId: "o", children: []}]},
    ] as any;
    const ids = (blocks: any[]): any => blocks.map((b) => b.children ? [b.id, ids(b.children)] : b.id);

    it("de la racine vers un groupe, avant ou après le bloc survolé", () => {
        expect(ids(moveBlockInTree(tree(), "a", {overId: "b"}))).toEqual([["g", ["a", "b"]], "s"]);
        expect(ids(moveBlockInTree(tree(), "a", {overId: "b", after: true}))).toEqual([["g", ["b", "a"]], "s"]);
    });

    it("d'un groupe vers la racine, et dans une règle de condition vide", () => {
        expect(ids(moveBlockInTree(tree(), "b", {overId: "a"}))).toEqual(["b", "a", ["g", []], "s"]);
        const moved = moveBlockInTree(tree(), "b", {containerId: "r"}) as any[];
        expect(moved[2].conditions[0].children.map((c: any) => c.id)).toEqual(["b"]);
    });

    it("réordonne dans la même liste et refuse de déposer un groupe en lui-même", () => {
        expect(ids(moveBlockInTree(tree(), "s", {overId: "a"}))).toEqual(["s", "a", ["g", ["b"]]]);
        expect(ids(moveBlockInTree(tree(), "a", {overId: "s"}))).toEqual([["g", ["b"]], "a", "s"]);
        expect(ids(moveBlockInTree(tree(), "a", {overId: "s", after: true}))).toEqual([["g", ["b"]], "s", "a"]);
        const blocks = tree();
        expect(moveBlockInTree(blocks, "g", {containerId: "g"})).toBe(blocks);
        expect(moveBlockInTree(blocks, "g", {overId: "b"})).toBe(blocks);
    });

    it("insère un nouveau bloc juste après le bloc courant, dans sa liste (même imbriquée)", () => {
        useFormBuilderStore.setState({blocks: tree()});
        useFormBuilderStore.getState().insertBlockAfter("b", {id: "n", type: "TextInput"} as any);
        expect(ids(useFormBuilderStore.getState().blocks)).toEqual(["a", ["g", ["b", "n"]], "s"]);
    });
});
