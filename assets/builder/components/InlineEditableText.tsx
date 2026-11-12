import { ChangeEvent, FocusEvent, KeyboardEvent, useEffect, useLayoutEffect, useRef, useState } from "react";
import { Input } from "./ui/input";
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
const inputOnlyClassName = "h-6 max-w-full";


export const InlineEditableText = ({value, onCommit, placeholder, className = "", multiline = false, blockId}: Props) => {
    const [draft, setDraft] = useState(value);
    const inputRef = useRef<HTMLInputElement>(null);
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

    // Le chargement de la police web change la largeur sans changer le texte.
    useLayoutEffect(() => {
        const mirror = mirrorRef.current;
        if (!mirror) return;
        const observer = new ResizeObserver(measure);
        observer.observe(mirror);
        return () => observer.disconnect();
    }, [multiline]);

    const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setDraft(e.target.value);
    };

    const handleBlur = (e: FocusEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        if (e.target.value !== value) {
            onCommit(e.target.value);
        }
    };

    const handleKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
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

            <Input
                ref={inputRef}
                value={draft}
                onChange={handleChange}
                onBlur={handleBlur}
                onKeyDown={handleKeyDown}
                placeholder={placeholder}
                style={{width}}
                className={`${baseClassName} ${inputOnlyClassName} ${className}`}
            />
        </>
    );
};
