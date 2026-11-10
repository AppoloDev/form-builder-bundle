import { describe, expect, it } from "vitest";
import { textVariantOf, transformTextBlock } from "./TextVariants";

describe("Transformer en (titres et texte explicatif)", () => {
    it("transforme un titre en texte en gardant l'id et le texte, sans clé `heading`", () => {
        expect(transformTextBlock({id: "a", type: "Title", text: "Bonjour", heading: "h2"}, "paragraph"))
            .toEqual({id: "a", type: "Paragraph", text: "Bonjour"});
    });

    it("transforme un texte en titre du niveau choisi", () => {
        expect(transformTextBlock({id: "a", type: "Paragraph", text: "Bonjour"}, "h3"))
            .toEqual({id: "a", type: "Title", text: "Bonjour", heading: "h3"});
    });

    it("change le niveau d'un titre", () => {
        expect(transformTextBlock({id: "a", type: "Title", text: "T", heading: "h1"}, "h2"))
            .toEqual({id: "a", type: "Title", text: "T", heading: "h2"});
    });

    it("donne la variante courante, aucune pour un ancien titre de niveau 4 à 6", () => {
        expect(textVariantOf({type: "Paragraph"})).toBe("paragraph");
        expect(textVariantOf({type: "Title", heading: "h2"})).toBe("h2");
        expect(textVariantOf({type: "Title", heading: "h5"})).toBeUndefined();
    });
});
