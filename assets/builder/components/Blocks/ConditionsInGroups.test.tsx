import { createElement } from "react";
import { renderToStaticMarkup } from "react-dom/server";
import { describe, expect, it } from "vitest";
import FieldSet from "./FieldSet";
import Repeatable from "./Repeatable";

const owner = (type: "Select" | "ChoiceGroup") => ({
    id: "owner",
    type,
    label: "Question",
    options: [{id: "o1", label: "Oui"}],
    conditions: [{id: "r1", operator: "is", optionId: "o1", children: [{id: "c", type: "TextInput", label: "ENFANTCOND"}]}],
});

describe("champs conditionnels dans un groupe ou un répétable", () => {
    for (const type of ["Select", "ChoiceGroup"] as const) {
        it(`les conditions d'un ${type} sont visibles dans un FieldSet`, () => {
            const html = renderToStaticMarkup(createElement(FieldSet as any, {id: "g", type: "FieldSet", children: [owner(type)]}));
            expect(html).toContain("ENFANTCOND");
        });

        it(`les conditions d'un ${type} sont visibles dans un Repeatable`, () => {
            const html = renderToStaticMarkup(createElement(Repeatable as any, {id: "r", type: "Repeatable", maxItems: 2, children: [owner(type)]}));
            expect(html).toContain("ENFANTCOND");
        });
    }

    it("les conditions ne s'imbriquent pas : un choix dans une règle n'affiche pas les siennes", () => {
        const nested = {...owner("Select"), id: "nested", conditions: [{id: "r2", operator: "is", optionId: "o1", children: [{id: "d", type: "TextInput", label: "PETITFILS"}]}]};
        const outer = {...owner("Select"), conditions: [{id: "r1", operator: "is", optionId: "o1", children: [nested]}]};

        const html = renderToStaticMarkup(createElement(FieldSet as any, {id: "g", type: "FieldSet", children: [outer]}));

        expect(html).not.toContain("PETITFILS");
    });
});
