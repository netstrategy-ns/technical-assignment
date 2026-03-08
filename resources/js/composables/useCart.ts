import { computed, ref, watch } from 'vue';

const CART_STORAGE_KEY = 'tickme_cart';

export interface CartItem {
    eventId: number;
    eventSlug: string;
    eventTitle: string;
    ticketTypeId: number;
    ticketTypeName: string;
    ticketId: number;
    price: string;
    maxPerUser: number | null;
    quantity: number;
}

function loadCartFromStorage(): CartItem[] {
    if (typeof window === 'undefined') return [];
    try {
        const raw = localStorage.getItem(CART_STORAGE_KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw) as unknown;
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function saveCartToStorage(items: CartItem[]): void {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items));
    } catch {
        return;
    }
}

const items = ref<CartItem[]>(loadCartFromStorage());

watch(
    items,
    (val) => saveCartToStorage(val),
    { deep: true },
);

const totalItems = computed(() =>
    items.value.reduce((sum, i) => sum + i.quantity, 0),
);

const totalAmount = computed(() =>
    items.value.reduce((sum, i) => sum + parseFloat(i.price) * i.quantity, 0),
);

const isEmpty = computed(() => items.value.length === 0);

function add(item: Omit<CartItem, 'quantity'> & { quantity?: number }) {
    const qty = Math.max(1, item.quantity ?? 1);
    const existing = items.value.find(
        (i) => i.eventId === item.eventId && i.ticketId === item.ticketId,
    );

    if (existing) {
        const nextQuantity = existing.maxPerUser != null
            ? Math.min(existing.quantity + qty, existing.maxPerUser)
            : existing.quantity + qty;

        items.value = items.value.map((i) =>
            i.eventId === item.eventId && i.ticketId === item.ticketId
                ? { ...i, quantity: nextQuantity }
                : i,
        );
        return;
    }

    const initialQuantity = item.maxPerUser != null
        ? Math.min(qty, item.maxPerUser)
        : qty;

    items.value = [...items.value, { ...item, quantity: initialQuantity }];
}

function remove(eventId: number, ticketId: number) {
    items.value = items.value.filter(
        (i) => !(i.eventId === eventId && i.ticketId === ticketId),
    );
}

function setQuantity(eventId: number, ticketId: number, quantity: number) {
    if (quantity <= 0) {
        remove(eventId, ticketId);
        return;
    }

    const existing = items.value.find(
        (i) => i.eventId === eventId && i.ticketId === ticketId,
    );

    if (!existing) {
        return;
    }

    const nextQuantity = existing.maxPerUser != null
        ? Math.min(quantity, existing.maxPerUser)
        : quantity;

    items.value = items.value.map((i) =>
        i.eventId === eventId && i.ticketId === ticketId
            ? { ...i, quantity: nextQuantity }
            : i,
    );
}

function clear() {
    items.value = [];
}

export function useCart() {
    return {
        items,
        totalItems,
        totalAmount,
        isEmpty,
        add,
        remove,
        setQuantity,
        clear,
    };
}
