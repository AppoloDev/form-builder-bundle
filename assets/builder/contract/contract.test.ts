import { readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { describe, expect, it } from "vitest";
import { buildContract, serializeContract } from "./buildContract";

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
});
