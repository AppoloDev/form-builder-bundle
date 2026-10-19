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
        expect(html.match(/\[\.fb-block:hover&gt;&amp;\]:opacity-100|\[\.fb-block:hover>&\]:opacity-100/g)).toHaveLength(2);
        expect(html).not.toContain("group-hover:opacity-100");
    });
});
