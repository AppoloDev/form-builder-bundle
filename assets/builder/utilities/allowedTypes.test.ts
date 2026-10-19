import { readFileSync, readdirSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { join } from "node:path";
import { describe, expect, it } from "vitest";
import { parseList, resolveAllowedTypes } from "./allowedTypes";
import { useFormBuilderStore } from "../stores/block.store";
import type { Block } from "../components/Blocks/Definition";

describe("resolveAllowedTypes", () => {
    it("propose tout (undefined) sans restriction", () => {
        expect(resolveAllowedTypes()).toBeUndefined();
        expect(resolveAllowedTypes(null, null)).toBeUndefined();
    });

    it("restreint par catégories", () => {
        expect(resolveAllowedTypes(["choice"], null)?.sort()).toEqual(["ChoiceGroup", "Select"]);
    });

    it("restreint par types", () => {
        expect(resolveAllowedTypes(null, ["Signature", "Title"])?.sort()).toEqual(["Signature", "Title"]);
    });

    it("fait l'union catégories ∪ types sans doublon", () => {
        expect(resolveAllowedTypes(["choice"], ["Signature", "Select"])?.sort()).toEqual(["ChoiceGroup", "Select", "Signature"]);
    });

    it("ignore les catégories et types inconnus", () => {
        expect(resolveAllowedTypes(["nope"], ["Nope"])).toEqual([]);
    });
});

describe("parseList", () => {
    it("lit une liste séparée par des virgules", () => {
        expect(parseList("text, choice,")).toEqual(["text", "choice"]);
        expect(parseList(undefined)).toBeUndefined();
        expect(parseList(null)).toBeUndefined();
    });
});

describe("la restriction ne limite que les ajouts", () => {
    const signature = {id: "sig", type: "Signature", label: "Signer"} as Block;

    it("un bloc d'un type non proposé reste dans la structure, modifiable et supprimable", () => {
        const allowed = resolveAllowedTypes(["text"], null);
        expect(allowed).not.toContain("Signature");

        const store = useFormBuilderStore;
        store.getState().setBlocks([signature]);
        expect(store.getState().blocks).toHaveLength(1);

        store.getState().updateBlock("sig", {label: "Autre"});
        expect(store.getState().blocks[0]).toMatchObject({type: "Signature", label: "Autre"});

        store.getState().removeBlock("sig");
        expect(store.getState().blocks).toHaveLength(0);
    });

    it("tous les AddMenu lisent la restriction dans le contexte (aucun ne passe allowTypes à la main)", () => {
        const root = fileURLToPath(new URL("..", import.meta.url));
        const files: string[] = [];
        const walk = (dir: string) => {
            for (const entry of readdirSync(dir, {withFileTypes: true})) {
                if (entry.isDirectory()) walk(join(dir, entry.name));
                else if (entry.name.endsWith(".tsx")) files.push(join(dir, entry.name));
            }
        };
        walk(root);

        const usages = files.filter((file) => /<AddMenu[\s>]/.test(readFileSync(file, "utf8")));
        expect(usages.length).toBeGreaterThanOrEqual(4); // racine, FieldSet, Repeatable, Empty
        for (const file of usages) {
            expect(/<AddMenu[^>]*allowTypes/s.test(readFileSync(file, "utf8")), file).toBe(false);
        }
    });
});
