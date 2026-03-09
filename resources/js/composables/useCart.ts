import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

export interface CartItem {
    id: number;
    quantity: number;
    status: string;
    expires_at: string | null;
    remaining_seconds: number;
    ticket: {
        id: number;
        price: string;
        max_per_user: number | null;
        available_quantity: number;
    };
    ticket_type: {
        id: number;
        name: string;
    };
    event: {
        id: number;
        slug: string;
        title: string;
    };
}

interface CartPayload {
    items: CartItem[];
    summary: {
        total_items: number;
        total_amount: number;
    };
}

interface CartActionOptions {
    onSuccess?: () => void;
    onError?: (errors: Record<string, string>) => void;
    onFinish?: () => void;
}

let cartAutoRefreshIntervalId: number | null = null;
let cartAutoRefreshConsumers = 0;
let cartExpirationSyncIntervalId: number | null = null;
let cartExpirationSyncConsumers = 0;
let cartExpiredRefreshPending = false;
let cartExpirationSyncResetTimeoutId: number | null = null;
let cartHoldExpiredListenerCount = 0;
export const CART_HOLD_EXPIRED_EVENT = 'tickme:cart-hold-expired';

function emitCartHoldExpiredEvent(): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(new Event(CART_HOLD_EXPIRED_EVENT));
}

function refreshCurrentPage(options: CartActionOptions = {}): void {
    router.visit(window.location.href, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onSuccess: options.onSuccess,
        onError: options.onError,
        onFinish: options.onFinish,
    });
}

function cartHasExpiredItemsFromItems(items: CartItem[]): boolean {
    const now = Date.now();

    return items.some((item) => {
        if (!item.expires_at) {
            return false;
        }

        const expiresAt = Date.parse(item.expires_at);
        if (!Number.isFinite(expiresAt)) {
            return false;
        }

        return expiresAt <= now;
    });
}

const emptyCart: CartPayload = {
    items: [],
    summary: {
        total_items: 0,
        total_amount: 0,
    },
};

export function useCart() {
    const page = usePage();

    const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
    const cart = computed(() => (page.props.cart as CartPayload | null) ?? emptyCart);
    const items = computed(() => cart.value.items ?? []);
    const totalItems = computed(() => cart.value.summary?.total_items ?? 0);
    const totalAmount = computed(() => cart.value.summary?.total_amount ?? 0);
    const isEmpty = computed(() => items.value.length === 0);

    const getItemByTicketId = (ticketId: number): CartItem | undefined =>
        items.value.find((item) => item.ticket.id === ticketId);

    const quantityForTicket = (ticketId: number): number =>
        getItemByTicketId(ticketId)?.quantity ?? 0;

    const add = (ticketId: number, quantity = 1, options: CartActionOptions = {}): void => {
        router.post(
            urls.value.cartHoldsStore ?? '/cart/hold',
            {
                ticket_id: ticketId,
                quantity,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onSuccess: options.onSuccess,
                onError: options.onError,
                onFinish: options.onFinish,
            },
        );
    };

    const remove = (holdId: number, options: CartActionOptions = {}): void => {
        router.delete(`${urls.value.cartHoldsBase ?? '/cart/hold'}/${holdId}`, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onSuccess: options.onSuccess,
            onError: options.onError,
            onFinish: options.onFinish,
        });
    };

    const update = (holdId: number, quantity: number, options: CartActionOptions = {}): void => {
        router.patch(
            `${urls.value.cartHoldsUpdateBase ?? '/cart/hold'}/${holdId}`,
            { quantity },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onSuccess: options.onSuccess,
                onError: options.onError,
                onFinish: options.onFinish,
            },
        );
    };

    const refresh = (options: CartActionOptions = {}): void => {
        refreshCurrentPage(options);
    };

    return {
        cart,
        items,
        totalItems,
        totalAmount,
        isEmpty,
        add,
        remove,
        update,
        refresh,
        getItemByTicketId,
        quantityForTicket,
    };
}

export function useCartAutoRefresh(intervalMs = 90_000) {
    onMounted(() => {
        cartAutoRefreshConsumers += 1;

        if (cartAutoRefreshIntervalId !== null) {
            return;
        }

        cartAutoRefreshIntervalId = window.setInterval(() => {
            if (document.visibilityState === 'hidden') {
                return;
            }

            refreshCurrentPage();
        }, intervalMs);
    });

    onUnmounted(() => {
        cartAutoRefreshConsumers = Math.max(0, cartAutoRefreshConsumers - 1);

        if (cartAutoRefreshConsumers > 0 || cartAutoRefreshIntervalId === null) {
            return;
        }

        window.clearInterval(cartAutoRefreshIntervalId);
        cartAutoRefreshIntervalId = null;
    });
}

export function useCartExpirationAutoRefresh(checkIntervalMs = 1_000) {
    const page = usePage();
    const cart = computed(() => (page.props.cart as CartPayload | null) ?? emptyCart);

    onMounted(() => {
        cartExpirationSyncConsumers += 1;

        if (cartExpirationSyncIntervalId !== null) {
            return;
        }

        cartExpirationSyncIntervalId = window.setInterval(() => {
            if (document.visibilityState === 'hidden') {
                return;
            }

            const hasExpiredItems = cartHasExpiredItemsFromItems(cart.value.items);
            if (!hasExpiredItems) {
                cartExpiredRefreshPending = false;

                return;
            }

            if (!cartExpiredRefreshPending) {
                cartExpiredRefreshPending = true;
                emitCartHoldExpiredEvent();
                if (cartExpirationSyncResetTimeoutId !== null) {
                    window.clearTimeout(cartExpirationSyncResetTimeoutId);
                }
                cartExpirationSyncResetTimeoutId = window.setTimeout(() => {
                    cartExpiredRefreshPending = false;
                    cartExpirationSyncResetTimeoutId = null;
                }, checkIntervalMs);
            }
        }, checkIntervalMs);
    });

    onUnmounted(() => {
        cartExpirationSyncConsumers = Math.max(0, cartExpirationSyncConsumers - 1);

        if (cartExpirationSyncConsumers > 0 || cartExpirationSyncIntervalId === null) {
            return;
        }

        window.clearInterval(cartExpirationSyncIntervalId);
        cartExpirationSyncIntervalId = null;
        cartExpiredRefreshPending = false;
    });
}

export function useCartHoldExpiredEvent(handler: () => void): void {
    if (typeof window === 'undefined') {
        return;
    }

    const listener = (): void => {
        handler();
    };

    onMounted(() => {
        cartHoldExpiredListenerCount += 1;
        window.addEventListener(CART_HOLD_EXPIRED_EVENT, listener);
    });

    onUnmounted(() => {
        cartHoldExpiredListenerCount = Math.max(0, cartHoldExpiredListenerCount - 1);
        window.removeEventListener(CART_HOLD_EXPIRED_EVENT, listener);

        if (cartHoldExpiredListenerCount > 0 || cartExpirationSyncConsumers > 0) {
            return;
        }

        if (cartExpirationSyncResetTimeoutId !== null) {
            window.clearTimeout(cartExpirationSyncResetTimeoutId);
            cartExpirationSyncResetTimeoutId = null;
        }
    });
}
