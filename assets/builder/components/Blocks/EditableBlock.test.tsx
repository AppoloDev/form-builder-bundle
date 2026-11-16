import { createElement } from "react";
import { renderToStaticMarkup } from "react-dom/server";
import { describe, expect, it } from "vitest";
import FieldSet from "./FieldSet";

describe("barre d'outils des blocs", () => {
    it("n'apparaît que pour le bloc survolé (enfant direct), jamais via un `group-hover` hérité d'un parent", () => {
        const html = renderToStaticMarkup(createElement(FieldSet as any, {
            id: "g",
            type: "FieldSet",
            children: [{id: "c", type: "TextInput", label: "Enfant"}],
        }));

        // Un conteneur et son enfant : deux barres, chacune liée au survol de son propre bloc.
        // Masquée quand une ligne d'option ou un en-tête de condition est survolé : une seule corbeille visible.
        expect(html.split("[.fb-block:hover:not(:has([data-row-actions]:hover))&gt;&amp;]:opacity-100")).toHaveLength(3);
        expect(html).not.toContain("group-hover:opacity-100");
    });
});
