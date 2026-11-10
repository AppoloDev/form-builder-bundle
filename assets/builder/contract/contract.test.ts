import { readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { describe, expect, it } from "vitest";
import { buildContract, serializeContract } from "./buildContract";
import { BLOCK_CATEGORIES, addMenuSections, isBlockCategory } from "../components/Blocks/Categories";
import { blockDefinitions } from "../components/Blocks/Definition";

describe("contrat front/PHP", () => {
    it("blocks.json est à jour (lancer `pnpm run export-contract`)", () => {
        const committed = readFileSync(fileURLToPath(new URL("./blocks.json", import.meta.url)), "utf8");

        expect(committed).toBe(serializeContract(buildContract()));
    });

    it("chaque type du contrat émet un id de bloc et ses clés par défaut", () => {
        const { types } = buildContract();

        expect(Object.keys(types).length).toBeGreaterThan(0);
        for (const [type, contract] of Object.entries(types)) {
            expect(contract.keys, type).toEqual(expect.arrayContaining(Object.keys(contract.defaults).filter((k) => k !== "id" && k !== "type")));
        }
    });

    it("chaque type a une catégorie valide, exposée dans le contrat mais jamais dans les props d'un bloc", () => {
        const { types } = buildContract();

        for (const definition of Object.values(blockDefinitions)) {
            expect(isBlockCategory(definition.category), definition.type).toBe(true);
            expect(types[definition.type].category).toBe(definition.category);
            expect(Object.keys(definition.defaultProps), definition.type).not.toContain("category");
            expect(types[definition.type].keys, definition.type).not.toContain("category");
        }
    });

    it("chaque type de bloc figure dans le menu d'ajout", () => {
        const inMenu = new Set(addMenuSections.flatMap((s) => s.entries.map((e) => e.type)));
        for (const type of Object.keys(blockDefinitions)) {
            expect(inMenu.has(type as keyof typeof blockDefinitions), type).toBe(true);
        }
    });

    it("chaque catégorie est utilisée par au moins un type", () => {
        const used = new Set(Object.values(blockDefinitions).map((d) => d.category));
        for (const category of BLOCK_CATEGORIES) {
            expect(used.has(category), category).toBe(true);
        }
    });
});
