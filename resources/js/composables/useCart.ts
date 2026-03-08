import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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

    return {
        cart,
        items,
        totalItems,
        totalAmount,
        isEmpty,
        add,
        remove,
        update,
        getItemByTicketId,
        quantityForTicket,
    };
}
