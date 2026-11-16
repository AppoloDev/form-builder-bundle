import { PropsWithChildren } from "react";
import { Tooltip as TooltipRoot, TooltipContent, TooltipProvider, TooltipTrigger } from "./ui/tooltip";

type Props = { content: string } & PropsWithChildren;

export const Tooltip = ({ children, content }: Props) => {
    return (
        <TooltipProvider>
            <TooltipRoot>
                <TooltipTrigger render={<span className="inline-block" />}>
                    {children}
                </TooltipTrigger>
                <TooltipContent className="whitespace-pre-line">{content}</TooltipContent>
            </TooltipRoot>
        </TooltipProvider>
    );
}
