import React, { PropsWithChildren, useCallback, useEffect, useMemo, useRef, useState } from "react";
import { BlockDefinition } from "./Blocks/Definition";
import { useAddableDefinitions } from "./AllowedTypesContext";
import { Button } from "./ui/button";
import { Dialog, DialogClose, DialogContent, DialogTrigger } from "./ui/dialog";
import { Input } from "./ui/input";
import { addMenuSections } from "./Blocks/Categories";
import { t } from "../i18n";

/** Hauteur du fondu en haut et en bas de la liste quand elle défile. */
const FADE = "2rem";

/** Fondu (masque) en haut si la liste a défilé, en bas s'il reste du contenu sous la zone visible. */
const fadeMask = (top: boolean, bottom: boolean) =>
    `linear-gradient(to bottom, ${top ? "transparent" : "black"}, black ${FADE}, black calc(100% - ${FADE}), ${bottom ? "transparent" : "black"})`;

type AddMenuProps = {
    onPick: (def: BlockDefinition) => void;
    allowTypes?: Array<BlockDefinition["type"]>;
} & PropsWithChildren;

export const AddMenu: React.FC<AddMenuProps> = (
    {
        onPick,
        allowTypes,
        children
    }) => {
    const items = useAddableDefinitions(allowTypes);

    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState("");
    // Focus initial sur la recherche : on peut taper directement à l'ouverture.
    const searchRef = useRef<HTMLInputElement>(null);
    const pickedRef = useRef(false);

    const groups = useMemo(() => {
        const q = query.trim().toLowerCase();

        return addMenuSections
            .map(section => ({
                titleKey: section.titleKey,
                defs: section.entries.flatMap((entry): BlockDefinition[] => {
                    const def = items.find(d => d.type === entry.type);
                    if (!def) return [];
                    const item = {
                        ...def,
                        title: entry.titleKey ? t(entry.titleKey) : def.title,
                        icon: entry.icon ?? def.icon,
                        defaultProps: {...def.defaultProps, ...entry.defaultProps},
                    } as BlockDefinition;
                    const matches = !q || [item.title, item.description, item.type].some(s => s.toLowerCase().includes(q));

                    return matches ? [item] : [];
                }),
            }))
            .filter(group => group.defs.length > 0);
    }, [query, items]);

    const listRef = useRef<HTMLDivElement | null>(null);
    const [fade, setFade] = useState({top: false, bottom: false});
    const updateFade = useCallback(() => {
        const el = listRef.current;
        if (!el) return;
        setFade({top: el.scrollTop > 0, bottom: el.scrollTop + el.clientHeight < el.scrollHeight - 1});
    }, []);
    // Ref fonction : la liste est montée dans le portail à l'ouverture du menu.
    const setListRef = useCallback((el: HTMLDivElement | null) => {
        listRef.current = el;
        updateFade();
    }, [updateFade]);
    useEffect(updateFade, [groups, updateFade]);

    const handleClose = () => {
        setOpen(false);
        setQuery("");
    };

    return (
        <Dialog open={open} onOpenChange={(next) => {
            if (next) {
                pickedRef.current = false;
                setOpen(true);
            } else {
                handleClose();
            }
        }}>
            <DialogTrigger nativeButton={false} render={<div onClick={(e) => e.stopPropagation()} className="outline-none"/>}>
                {children}
            </DialogTrigger>

            <DialogContent
                showCloseButton={false}
                className="sm:max-w-sm gap-0 p-0 flex flex-col overflow-hidden"
                style={{maxHeight: "min(85vh, 48rem)"}}
                initialFocus={searchRef}
                finalFocus={() => !pickedRef.current}
            >
                <div className="p-2">
                    <Input
                        ref={searchRef}
                        type="text"
                        value={query}
                        onChange={e => setQuery(e.target.value)}
                        placeholder={t("builder.searchBlock")}
                    />
                </div>

                {/* Barre de défilement fine sans piste (ni fond ni bordure) : propriétés standard (Chrome, Firefox),
                    pseudo-éléments -webkit- pour Safari. */}
                <div
                    ref={setListRef}
                    onScroll={updateFade}
                    className="flex-1 overflow-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-muted-foreground/30"
                    style={{
                        maskImage: fadeMask(fade.top, fade.bottom),
                        WebkitMaskImage: fadeMask(fade.top, fade.bottom),
                        scrollbarWidth: "thin",
                        scrollbarColor: "color-mix(in oklab, var(--muted-foreground) 30%, transparent) transparent",
                    }}
                >
                    {groups.length === 0 ? (
                        <div className="p-3 text-sm text-muted-foreground">{t("common.noResult")}</div>
                    ) : (
                        <div className="divide-y divide-border px-3">
                            {groups.map(({titleKey, defs}) => (
                                <section key={titleKey} aria-label={t(titleKey)} className="py-3">
                                    {groups.length > 1 && (
                                        <h4 className="px-2 pb-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                            {t(titleKey)}
                                        </h4>
                                    )}
                                    <ul>
                                        {defs.map(def => (
                                            <li key={def.title}>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        pickedRef.current = true;
                                                        onPick(def);
                                                        handleClose();
                                                    }}
                                                    className="h-9 w-full justify-start gap-3 font-normal"
                                                    title={def.description}
                                                >
                                                    {def.icon && <def.icon className="size-5 text-muted-foreground" aria-hidden="true"/>}
                                                    {def.title}
                                                </Button>
                                            </li>
                                        ))}
                                    </ul>
                                </section>
                            ))}
                        </div>
                    )}
                </div>

                <div className="border-t border-border p-2">
                    <DialogClose render={<Button type="button" variant="ghost" className="h-9 w-full justify-start font-normal"/>}>
                        {t("common.close")}
                    </DialogClose>
                </div>
            </DialogContent>
        </Dialog>
    );
};
