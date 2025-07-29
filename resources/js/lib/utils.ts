import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function conditionalClass(baseClass: string, condition: boolean | string | undefined, conditionalClass: string): string {
    return condition ? `${baseClass} ${conditionalClass}` : baseClass;
}
