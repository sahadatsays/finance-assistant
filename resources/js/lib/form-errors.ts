import { toast } from 'sonner';

export function firstValidationError(
    errors: Record<string, string | string[]>,
): string | null {
    const first = Object.values(errors)[0];

    if (Array.isArray(first)) {
        return first[0] ?? null;
    }

    return first ?? null;
}

export function showValidationErrorToast(
    errors: Record<string, string | string[]>,
): void {
    const message = firstValidationError(errors);

    if (message) {
        toast.error(message);
    }
}
