import { describe, expect, it } from "vitest";
import { fr } from "./fr";
import { en } from "./en";
import { getBuilderLocale, setBuilderLocale, t } from "./index";
import { blockDefinitions } from "../components/Blocks/Definition";

describe("builder i18n", () => {
    it("has the same keys in both catalogs", () => {
        expect(Object.keys(en).sort()).toEqual(Object.keys(fr).sort());
    });

    it("has the same placeholders in both catalogs", () => {
        const placeholders = (message: string) => (message.match(/\{\w+\}/g) ?? []).sort();
        for (const key of Object.keys(fr) as (keyof typeof fr)[]) {
            expect(placeholders(en[key]), key).toEqual(placeholders(fr[key]));
        }
    });

    it("interpolates parameters", () => {
        setBuilderLocale("en");
        expect(t("option.default", { n: 2 })).toBe("Option 2");
        setBuilderLocale("fr");
    });

    it("rebuilds the block definitions when the locale changes", () => {
        setBuilderLocale("en");
        expect(getBuilderLocale()).toBe("en");
        expect(blockDefinitions.TextInput.title).toBe("Short text");
        setBuilderLocale("fr_FR");
        expect(blockDefinitions.TextInput.title).toBe("Texte court");
    });

    it("falls back to English for unknown locales", () => {
        setBuilderLocale("de");
        expect(getBuilderLocale()).toBe("en");
        setBuilderLocale("fr");
    });
});
