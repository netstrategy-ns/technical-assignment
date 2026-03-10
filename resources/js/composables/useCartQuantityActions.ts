import { ref } from 'vue';
import type { CartActionOptions } from '@/types/models/cart';

interface UseCartQuantityActionsOptions {
    remove: (holdId: number, options?: CartActionOptions) => void;
    update: (holdId: number, quantity: number, options?: CartActionOptions) => void;
    defaultErrorMessage?: string;
}

export function useCartQuantityActions({
    remove,
    update,
    defaultErrorMessage = 'Impossibile aggiornare la quantita.',
}: UseCartQuantityActionsOptions) {
    const maxReachedMessages = ref<Record<number, boolean>>({});
    const actionErrors = ref<Record<number, string>>({});
    const loadingHolds = ref<Record<number, boolean>>({});

    const maxQuantityForHold = (availableQuantity: number, maxPerUser: number | null): number => {
        if (maxPerUser != null && maxPerUser > 0) {
            return Math.min(availableQuantity, maxPerUser);
        }

        return availableQuantity;
    };

    const hasReachedUserLimit = (quantity: number, maxPerUser: number | null): boolean => {
        return maxPerUser != null && maxPerUser > 0 && quantity >= maxPerUser;
    };

    const decrementQuantity = (holdId: number, quantity: number, isExpired = false) => {
        if (loadingHolds.value[holdId] || isExpired) {
            return;
        }

        maxReachedMessages.value[holdId] = false;
        actionErrors.value[holdId] = '';

        if (quantity <= 1) {
            loadingHolds.value[holdId] = true;
            remove(holdId, {
                onFinish: () => {
                    loadingHolds.value[holdId] = false;
                },
            });
            return;
        }

        loadingHolds.value[holdId] = true;
        update(holdId, quantity - 1, {
            onError: (errors) => {
                actionErrors.value[holdId] = errors.quantity ?? defaultErrorMessage;
            },
            onFinish: () => {
                loadingHolds.value[holdId] = false;
            },
        });
    };

    const incrementQuantity = (
        holdId: number,
        quantity: number,
        availableQuantity: number,
        maxPerUser: number | null,
        isExpired = false,
    ): void => {
        if (loadingHolds.value[holdId] || isExpired) {
            return;
        }

        const maxQuantity = maxQuantityForHold(availableQuantity, maxPerUser);

        if (quantity >= maxQuantity) {
            maxReachedMessages.value[holdId] = true;
            return;
        }

        maxReachedMessages.value[holdId] = false;
        actionErrors.value[holdId] = '';
        loadingHolds.value[holdId] = true;

        update(holdId, quantity + 1, {
            onError: (errors) => {
                actionErrors.value[holdId] = errors.quantity ?? defaultErrorMessage;
            },
            onFinish: () => {
                loadingHolds.value[holdId] = false;
            },
        });
    };

    return {
        maxReachedMessages,
        actionErrors,
        loadingHolds,
        hasReachedUserLimit,
        maxQuantityForHold,
        decrementQuantity,
        incrementQuantity,
    };
}
