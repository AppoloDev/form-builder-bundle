import { ChangeEvent, FocusEvent, KeyboardEvent, useEffect, useLayoutEffect, useRef, useState } from "react";
import { Textarea } from "./ui/textarea";
import { cn } from "cn";
import { takePendingFocus } from "../utilities/block.utiles";
import { BlockId } from "./Blocks/Definition";

type Props = {
    value: string;
    onCommit: (value: string) => void;
    placeholder?: string;
    className?: string;
    multiline?: boolean;
    /** Bloc porteur du texte : le champ prend le focus si ce bloc vient d'être ajouté. */
    blockId?: BlockId;
};

const baseClassName = "border-transparent bg-transparent px-1 -mx-1 hover:border-input focus-visible:border-ring";
// Une seule « ligne » logique, mais le texte passe à la ligne au lieu d'être coupé quand il dépasse le bloc.
const singleLineClassName = "max-w-full resize-none overflow-hidden";


export const InlineEditableText = ({value, onCommit, placeholder, className = "", multiline = false, blockId}: Props) => {
    const [draft, setDraft] = useState(value);
    const inputRef = useRef<HTMLTextAreaElement>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    useEffect(() => {
        if (blockId && takePendingFocus(blockId)) {
            (inputRef.current ?? textareaRef.current)?.focus();
        }
    }, [blockId]);

    useEffect(() => {
        setDraft(value);
    }, [value]);

    // L'input prend la largeur du texte (ou du placeholder) recopié dans un span invisible.
    const mirrorRef = useRef<HTMLSpanElement>(null);
    const [width, setWidth] = useState<number>();
    const measure = () => {
        if (mirrorRef.current) setWidth(Math.ceil(mirrorRef.current.getBoundingClientRect().width));
    };

    // À chaque frappe, mesure synchrone avant l'affichage : l'input ne paraît jamais plus étroit que son texte.
    useLayoutEffect(measure, [draft, placeholder, className]);

    // Hauteur ajustée au texte replié (field-sizing n'est pas pris en charge partout).
    useLayoutEffect(() => {
        const el = inputRef.current;
        if (!el) return;
        el.style.height = "auto";
        el.style.height = `${el.scrollHeight}px`;
    }, [draft, width]);

    // Le chargement de la police web change la largeur sans changer le texte.
    useLayoutEffect(() => {
        const mirror = mirrorRef.current;
        if (!mirror) return;
        const observer = new ResizeObserver(measure);
        observer.observe(mirror);
        return () => observer.disconnect();
    }, [multiline]);

    const handleChange = (e: ChangeEvent<HTMLTextAreaElement>) => {
        // Hors multiline, un saut de ligne collé devient une espace : le texte reste sur une ligne logique.
        setDraft(multiline ? e.target.value : e.target.value.replace(/\r?\n/g, " "));
    };

    const handleBlur = (e: FocusEvent<HTMLTextAreaElement>) => {
        if (e.target.value !== value) {
            onCommit(e.target.value);
        }
    };

    const handleKeyDown = (e: KeyboardEvent<HTMLTextAreaElement>) => {
        if (e.key === "Enter") {
            e.preventDefault();
            e.currentTarget.blur();
        }
    };

    if (multiline) {
        return (
            <Textarea
                ref={textareaRef}
                value={draft}
                onChange={handleChange}
                onBlur={handleBlur}
                placeholder={placeholder}
                className={`resize-none ${baseClassName} ${className}`}
            />
        );
    }

    return (
        <>
            <span
                ref={mirrorRef}
                aria-hidden="true"
                className={cn("invisible absolute -z-10 whitespace-pre border px-1", className)}
            >
                {draft || placeholder}
            </span>

            <Textarea
                ref={inputRef}
                rows={1}
                value={draft}
                onChange={handleChange}
                onBlur={handleBlur}
                onKeyDown={handleKeyDown}
                placeholder={placeholder}
                style={{width}}
                className={`${baseClassName} ${singleLineClassName} ${className}`}
            />
        </>
    );
};
