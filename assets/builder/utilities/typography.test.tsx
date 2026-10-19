import { createElement } from "react";
import { renderToStaticMarkup } from "react-dom/server";
import { describe, expect, it } from "vitest";
import { HEADING_LEVELS, PARAGRAPH_CLASSES, PARAGRAPH_TEXTAREA_CLASSES, TITLE_CLASSES, TITLE_INPUT_CLASSES } from "./typography";
import Title from "../components/Blocks/Title";
import Paragraph from "../components/Blocks/Paragraph";
import { blockDefinitions } from "../components/Blocks/Definition";

const sizeOf = (classes: string) => classes.split(" ").find((c) => /^text-(xs|sm|base|lg|xl|\dxl)$/.test(c));

describe("typographie des titres et paragraphes", () => {
    it("chaque niveau h1 à h6 a des classes avec une taille, font-bold et text-foreground", () => {
        expect(Object.keys(TITLE_CLASSES)).toEqual([...HEADING_LEVELS]);
        for (const level of HEADING_LEVELS) {
            expect(sizeOf(TITLE_CLASSES[level]), level).toBeDefined();
            expect(TITLE_CLASSES[level].split(" "), level).toEqual(expect.arrayContaining(["font-bold", "text-foreground"]));
        }
    });

    it("l'échelle est celle du formulaire de réponse", () => {
        expect(HEADING_LEVELS.map((level) => sizeOf(TITLE_CLASSES[level]))).toEqual(
            ["text-3xl", "text-2xl", "text-xl", "text-lg", "text-base", "text-sm"],
        );
    });

    it("les classes d'édition sont celles du formulaire, avec la taille répétée sous md:", () => {
        for (const level of HEADING_LEVELS) {
            const size = sizeOf(TITLE_CLASSES[level]);
            expect(TITLE_INPUT_CLASSES[level].split(" ").sort(), level)
                .toEqual([...TITLE_CLASSES[level].split(" "), `md:${size}`].sort());
        }
    });

    it("le paragraphe est en text-base text-muted-foreground, taille répétée sous md: en édition", () => {
        expect(PARAGRAPH_CLASSES).toBe("text-base text-muted-foreground");
        expect(PARAGRAPH_TEXTAREA_CLASSES.split(" ").sort()).toEqual([...PARAGRAPH_CLASSES.split(" "), "md:text-base"].sort());
    });

    it("tous les niveaux proposés par le builder ont des classes", () => {
        const schema = blockDefinitions.Title.editionSchema?.find((item) => item.key === "heading");
        expect(schema?.type).toBe("select");
        const values = schema && schema.type === "select" ? schema.options.map((option) => option.value) : [];
        expect(values).toEqual([...HEADING_LEVELS]);
    });

    it("Title applique ces classes en édition et en aperçu", () => {
        for (const level of HEADING_LEVELS) {
            const edit = renderToStaticMarkup(createElement(Title, {id: "t", type: "Title", text: "Mon titre", heading: level}));
            for (const cls of TITLE_INPUT_CLASSES[level].split(" ")) {
                expect(edit, `${level} ${cls}`).toContain(cls);
            }

            const preview = renderToStaticMarkup(createElement(Title, {id: "t", type: "Title", text: "Mon titre", heading: level, preview: true}));
            expect(preview).toContain(`<${level} class="${TITLE_CLASSES[level]}">Mon titre</${level}>`);
        }
    });

    it("Paragraph applique ces classes en édition et en aperçu", () => {
        const edit = renderToStaticMarkup(createElement(Paragraph, {id: "p", type: "Paragraph", text: "Texte"}));
        for (const cls of PARAGRAPH_TEXTAREA_CLASSES.split(" ")) {
            expect(edit, cls).toContain(cls);
        }

        const preview = renderToStaticMarkup(createElement(Paragraph, {id: "p", type: "Paragraph", text: "Texte", preview: true}));
        expect(preview).toContain(`<p class="${PARAGRAPH_CLASSES}">Texte</p>`);
    });
});
