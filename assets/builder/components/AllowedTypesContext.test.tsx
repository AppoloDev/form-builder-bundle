import { createElement } from "react";
import { renderToStaticMarkup } from "react-dom/server";
import { describe, expect, it } from "vitest";
import { AllowedTypesContext, useAddableDefinitions } from "./AllowedTypesContext";
import { ContainerContext } from "./Blocks/ContainerContext";
import { resolveAllowedTypes } from "../utilities/allowedTypes";

const Probe = ({allowTypes}: { allowTypes?: Parameters<typeof useAddableDefinitions>[0] }) =>
    createElement("p", null, useAddableDefinitions(allowTypes).map((d) => d.type).join(","));

const render = (value: ReturnType<typeof resolveAllowedTypes>, child = createElement(Probe)) =>
    renderToStaticMarkup(
        createElement(AllowedTypesContext.Provider, {value},
            // Comme un AddMenu de FieldSet ou de Repeatable : plusieurs conteneurs imbriqués sous la racine.
            createElement(ContainerContext.Provider, {value: true},
                createElement(ContainerContext.Provider, {value: true}, child))),
    );

describe("useAddableDefinitions", () => {
    it("propose tous les types sans restriction", () => {
        expect(render(undefined).split(",").length).toBeGreaterThanOrEqual(17);
    });

    it("applique la restriction dans les conteneurs imbriqués", () => {
        expect(render(resolveAllowedTypes(["choice"], ["Signature"]))).toBe("<p>ChoiceGroup,Select,Signature</p>");
    });

    it("ne propose rien si la restriction ne retient aucun type (et non tout)", () => {
        expect(render(resolveAllowedTypes(["nope"], null))).toBe("<p></p>");
    });

    it("la prop explicite l'emporte sur le contexte", () => {
        expect(render(resolveAllowedTypes(["choice"], null), createElement(Probe, {allowTypes: ["Title"]}))).toBe("<p>Title</p>");
    });
});
