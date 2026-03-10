import { computed } from 'vue';
import type { Ref } from 'vue';
import type { CartItem } from '@/types/models/cart';


interface CartItemByEventLine {
    holdId: number;
    ticketId: number;
    ticketTypeName: string;
    price: string;
    maxPerUser: number | null;
    quantity: number;
    subtotal: number;
    lineTotal: number;
    expiresAt: string | null;
    availableQuantity: number;
    remainingSeconds: number;
    isExpired: boolean;
}

interface CartItemsByEventResult {
    event: {
        id: number;
        slug: string;
        title: string;
    };
    eventTotal: number;
    lines: CartItemByEventLine[];
}

interface UseCartItemsByEventOptions {
    items: Ref<CartItem[]>;
    parseRemainingSeconds?: (expiresAt: string | null) => number;
}

export function useCartItemsByEvent({ items, parseRemainingSeconds }: UseCartItemsByEventOptions) {
    return computed<CartItemsByEventResult[]>(() => {
        const byEvent = new Map<number, CartItemsByEventResult>();

        for (const item of items.value) {
            const unitPrice = parseFloat(item.ticket.price);
            const safeUnitPrice = Number.isFinite(unitPrice) ? unitPrice : 0;
            const remainingSeconds = parseRemainingSeconds ? parseRemainingSeconds(item.expires_at) : 0;

            let group = byEvent.get(item.event.id);
            if (!group) {
                group = {
                    event: item.event,
                    eventTotal: 0,
                    lines: [],
                };
                byEvent.set(item.event.id, group);
            }

            const subtotal = safeUnitPrice * item.quantity;
            group.lines.push({
                holdId: item.id,
                ticketId: item.ticket.id,
                ticketTypeName: item.ticket_type.name,
                price: item.ticket.price,
                maxPerUser: item.ticket.max_per_user,
                quantity: item.quantity,
                subtotal,
                lineTotal: subtotal,
                expiresAt: item.expires_at,
                availableQuantity: item.ticket.available_quantity,
                remainingSeconds,
                isExpired: remainingSeconds <= 0,
            });
            group.eventTotal += subtotal;
        }

        return Array.from(byEvent.values());
    });
}
