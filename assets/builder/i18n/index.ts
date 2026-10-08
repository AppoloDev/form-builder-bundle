import { fr, type MessageKey } from "./fr";
import { en } from "./en";

export type { MessageKey };
export type BuilderLocale = "fr" | "en";

const catalogs: Record<BuilderLocale, Record<MessageKey, string>> = { fr, en };

let current: BuilderLocale = "fr";
const listeners = new Set<() => void>();

export const resolveLocale = (value: string | undefined | null): BuilderLocale =>
    value?.toLowerCase().startsWith("fr") ? "fr" : "en";

export const getBuilderLocale = (): BuilderLocale => current;

export const setBuilderLocale = (value: string | undefined | null): void => {
    const next = resolveLocale(value);
    if (next === current) {
        return;
    }
    current = next;
    listeners.forEach((listener) => listener());
};

/** Appelé à chaque changement de langue (sert à reconstruire les définitions de blocs). */
export const onBuilderLocaleChange = (listener: () => void): void => {
    listeners.add(listener);
};

export const t = (key: MessageKey, params: Record<string, string | number> = {}): string =>
    Object.entries(params).reduce(
        (message, [name, value]) => message.replaceAll(`{${name}}`, String(value)),
        catalogs[current][key],
    );
