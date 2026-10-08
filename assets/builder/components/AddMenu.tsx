import React, { PropsWithChildren, useMemo, useRef, useState } from "react";
import { BlockDefinition, DefinitionEditionItem, getAllBlockDefinitions } from "./Blocks/Definition";
import { TextEdition } from "./Edition/TextEdition";
import { CheckboxEdition } from "./Edition/CheckboxEdition";
import { SelectEdition } from "./Edition/SelectEdition";
import { labelToName } from "../utilities/string.utiles";
import { Button } from "./ui/button";
import { BLOCK_COMPONENTS } from "./BlockRegistry";
import { Dialog, DialogContent, DialogTrigger } from "./ui/dialog";
import { Input } from "./ui/input";

type AddMenuProps = {
    onPick: (def: BlockDefinition, overrides?: Record<string, any>) => void;
    placeholder?: string;
    allowTypes?: Array<BlockDefinition["type"]>;
} & PropsWithChildren;

export const AddMenu: React.FC<AddMenuProps> = (
    {
        onPick,
        placeholder = "Rechercher un bloc…",
        allowTypes,
        children
    }) => {
    const ALL = useMemo(() => getAllBlockDefinitions(), []);
    const items = useMemo(() => {
        if (!allowTypes || allowTypes.length === 0) return ALL;
        const allow = new Set(allowTypes);
        return ALL.filter(d => allow.has(d.type));
    }, [ALL, allowTypes]);

    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState("");
    const [selectedDef, setSelectedDef] = useState<BlockDefinition | null>(null);
    const [formState, setFormState] = useState<Record<string, any>>({});
    const inputRef = useRef<HTMLInputElement>(null);

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) return items;
        return items.filter(d =>
            d.title.toLowerCase().includes(q) ||
            d.description.toLowerCase().includes(q) ||
            d.type.toLowerCase().includes(q)
        );
    }, [query, items]);

    const handleClose = () => {
        setOpen(false);
        setQuery("");
        setSelectedDef(null);
        setFormState({});
    };

    const handleSelectDef = (def: BlockDefinition) => {
        setSelectedDef(def);
        // Initialize form state from default props
        const initial: Record<string, any> = {};
        if (def.editionSchema) {
            for (const field of def.editionSchema) {
                const defaultVal = (def.defaultProps as Record<string, any>)[field.key];
                initial[field.key] = defaultVal ?? (field.type === "checkbox" ? false : "");
            }
        }
        setFormState(initial);
    };

    const getPreviewProps = (def: BlockDefinition, state: Record<string, any>): Record<string, any> => {
        const merged: Record<string, any> = {...def.defaultProps, ...state, id: "preview", preview: true};

        if ((def.type === "ChoiceGroup" || def.type === "Select") && (!merged.options || merged.options.length === 0)) {
            merged.options = def.type === "ChoiceGroup"
                ? [
                    {id: "preview-1", label: "", value: ""},
                    {id: "preview-2", label: "", value: ""}
                ]
                : [
                    {id: "preview-1", label: "Option 1"},
                    {id: "preview-2", label: "Option 2"},
                    {id: "preview-3", label: "Option 3"},
                ];
        }

        return merged;
    };

    const handleFormChange = (key: string, value: any) => {
        setFormState(prev => {
            const next = {...prev, [key]: value};
            // Auto-derive `name` from `label`
            if (key === "label") {
                next.name = labelToName(value);
            }
            return next;
        });
    };

    const handleConfirm = () => {
        if (!selectedDef) return;

        // Build overrides: only include values that differ from defaults
        const overrides: Record<string, any> = {};
        const defaults = selectedDef.defaultProps as Record<string, any>;
        for (const [key, value] of Object.entries(formState)) {
            if (value !== defaults[key]) {
                overrides[key] = value;
            }
        }
        // If label was changed, also include the derived name
        if (overrides.label) {
            overrides.name = labelToName(overrides.label);
        }

        onPick(selectedDef, Object.keys(overrides).length > 0 ? overrides : undefined);
        handleClose();
    };

    const renderEditionField = (field: DefinitionEditionItem) => {
        const value = formState[field.key];

        if (field.type === "checkbox") {
            return (
                <CheckboxEdition
                    key={field.key}
                    label={field.label}
                    checked={Boolean(value)}
                    editItem={(v) => handleFormChange(field.key, v)}
                />
            );
        }

        if (field.type === "select") {
            return (
                <SelectEdition
                    key={field.key}
                    label={field.label}
                    value={value ?? ""}
                    options={field.options}
                    helpText={field.helpText}
                    editItem={(v) => handleFormChange(field.key, v)}
                />
            );
        }

        return (
            <TextEdition
                key={field.key}
                label={field.label}
                value={value ?? ""}
                type={field.type || "text"}
                helpText={field.helpText || ""}
                rows={field.rows}
                editItem={(v) => handleFormChange(field.key, v)}
            />
        );
    };

    return (
        <Dialog open={open} onOpenChange={(next) => {
            if (next) {
                setOpen(true);
                setTimeout(() => inputRef.current?.focus(), 0);
            } else {
                handleClose();
            }
        }}>
            <DialogTrigger nativeButton={false} render={<div onClick={(e) => e.stopPropagation()} className="inline-block cursor-pointer"/>}>
                {children}
            </DialogTrigger>

            <DialogContent
                className="sm:max-w-4xl gap-0 p-0 flex overflow-hidden"
                style={{maxHeight: "80vh"}}
            >
                {/* Left panel – Block list */}
                <div className="w-72 min-w-72 border-r border-border flex flex-col">
                    <div className="p-2 border-b border-border">
                        <Input
                            ref={inputRef}
                            type="text"
                            value={query}
                            onChange={e => setQuery(e.target.value)}
                            placeholder={placeholder}
                        />
                    </div>

                    <div className="flex-1 overflow-auto">
                        {filtered.length === 0 ? (
                            <div className="p-3 text-sm text-muted-foreground">Aucun résultat…</div>
                        ) : (
                            <ul className="p-2 space-y-1">
                                {filtered.map(def => (
                                    <li key={def.id}>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            onClick={(e) => {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                handleSelectDef(def);
                                            }}
                                            className={`w-full justify-start ${
                                                selectedDef?.id === def.id
                                                    ? "bg-accent border border-accent ring-1 ring-accent"
                                                    : ""
                                            }`}
                                            title={def.description}
                                        >
                                            {def.title}
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>

                {/* Right panel – Configuration */}
                <div className="flex-1 flex flex-col min-w-0">
                    {selectedDef ? (
                        <>
                            {/* Header */}
                            <div className="p-4 border-b border-border">
                                <h3 className="text-lg font-semibold text-foreground">{selectedDef.title}</h3>
                                <p className="text-sm text-muted-foreground mt-1">{selectedDef.description}</p>
                            </div>

                            <div className="flex-1 overflow-auto">
                                {/* Edition fields */}
                                {selectedDef.editionSchema && selectedDef.editionSchema.length > 0 && (
                                    <div className="p-4 space-y-3">
                                        {selectedDef.editionSchema.map(renderEditionField)}
                                    </div>
                                )}

                                {/* Preview */}
                                <div className="p-4 border-t border-border bg-muted">
                                    <p className="text-sm font-medium text-muted-foreground mb-2">Aperçu</p>
                                    <div className="pointer-events-none bg-background p-4 rounded-lg">
                                        {(() => {
                                            const PreviewComponent = BLOCK_COMPONENTS[selectedDef.type];
                                            return (
                                                <PreviewComponent
                                                    key={selectedDef.type}
                                                    {...getPreviewProps(selectedDef, formState)}
                                                />
                                            );
                                        })()}
                                    </div>
                                </div>
                            </div>

                            {/* Footer */}
                            <div className="p-3 border-t border-border flex justify-end gap-2">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={() => {
                                        setSelectedDef(null);
                                        setFormState({});
                                    }}
                                >
                                    Retour
                                </Button>

                                <Button
                                    onClick={handleConfirm}
                                >Ajouter</Button>
                            </div>
                        </>
                    ) : (
                        <div className="flex-1 flex items-center justify-center p-8">
                            <div className="text-center text-muted-foreground">
                                <svg className="mx-auto mb-3 text-muted-foreground" width="48" height="48" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" strokeWidth="1.5">
                                    <path d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M2 2l1.932 1.932"/>
                                </svg>
                                <p className="text-sm">Sélectionnez un bloc pour configurer ses options</p>
                            </div>
                        </div>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
};
