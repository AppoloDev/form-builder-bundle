import { ChangeEvent, FocusEvent, KeyboardEvent, useEffect, useLayoutEffect, useRef, useState } from "react";
import { Input } from "./ui/input";
import { Textarea } from "./ui/textarea";

type Props = {
    value: string;
    onCommit: (value: string) => void;
    placeholder?: string;
    className?: string;
    multiline?: boolean;
};

const baseClassName = "border-transparent bg-transparent px-1 -mx-1 hover:border-input focus-visible:border-ring";
const inputOnlyClassName = "h-6 max-w-full";

const MIN_WIDTH = 96;
const CARET_SPACE = 8;

export const InlineEditableText = ({value, onCommit, placeholder, className = "", multiline = false}: Props) => {
    const [draft, setDraft] = useState(value);
    const mirrorRef = useRef<HTMLSpanElement>(null);
    const [width, setWidth] = useState(MIN_WIDTH);

    useEffect(() => {
        setDraft(value);
    }, [value]);

    useLayoutEffect(() => {
        if (multiline || !mirrorRef.current) return;
        const measured = mirrorRef.current.getBoundingClientRect().width;
        const floor = draft ? 0 : MIN_WIDTH;
        setWidth(Math.max(floor, Math.ceil(measured) + CARET_SPACE));
    }, [draft, placeholder, multiline, className]);

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
                className={`invisible absolute -z-10 whitespace-pre border px-1 ${className}`}
            >
                {draft || placeholder}
            </span>

            <Input
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
