import { writeFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { buildContract, serializeContract } from "./buildContract";

// pnpm run export-contract
const target = fileURLToPath(new URL("./blocks.json", import.meta.url));
writeFileSync(target, serializeContract(buildContract()));
console.log(`Contrat écrit dans ${target}`);
