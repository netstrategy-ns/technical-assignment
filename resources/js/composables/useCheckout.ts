import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { useFormatData } from '@/composables/useFormatData';
import type { CheckoutEventGroup, CheckoutPageErrorBag } from '@/types/models/checkout';

export const useCheckout = () => {
    const page = usePage();
    const cart = useCart();
    const { formatPrice } = useFormatData();

    const errorMessage = ref('');
    const isSubmitting = ref(false);
    const loadingText = ref('Conferma acquisto');

    const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});
    const itemsByEvent = computed<CheckoutEventGroup[]>(() => {
        const grouped = new Map<number, CheckoutEventGroup>();

        for (const item of cart.items.value) {
            let group = grouped.get(item.event.id);
            if (!group) {
                group = {
                    event: item.event,
                    eventTotal: 0,
                    lines: [],
                };
                grouped.set(item.event.id, group);
            }

            const unitPrice = parseFloat(item.ticket.price);
            const safeUnitPrice = Number.isFinite(unitPrice) ? unitPrice : 0;
            const lineSubtotal = safeUnitPrice * item.quantity;
            group.lines.push({
                holdId: item.id,
                ticketTypeName: item.ticket_type.name,
                price: safeUnitPrice,
                quantity: item.quantity,
                subtotal: lineSubtotal,
            });
            group.eventTotal += lineSubtotal;
        }

        return Array.from(grouped.values());
    });

    const totalAmountFormatted = computed(() => formatPrice(cart.totalAmount.value));

    const confirmCheckout = (): void => {
        errorMessage.value = '';
        isSubmitting.value = true;
        loadingText.value = 'Conferma in corso...';

        router.post(
            '/checkout',
            {},
            {
                preserveState: false,
                onSuccess: () => {
                    loadingText.value = 'Conferma acquisto';
                },
                onError: (errors: CheckoutPageErrorBag) => {
                    const holdError = (errors as Record<string, unknown>).holds;
                    if (Array.isArray(holdError)) {
                        errorMessage.value = holdError.join(' ');
                    } else if (typeof holdError === 'string') {
                        errorMessage.value = holdError;
                    } else {
                        errorMessage.value = 'Il checkout non è stato possibile in questo momento.';
                    }
                    loadingText.value = 'Conferma acquisto';
                    isSubmitting.value = false;
                },
                onFinish: () => {
                    isSubmitting.value = false;
                    loadingText.value = 'Conferma acquisto';
                },
            },
        );
    };

    return {
        cart,
        urls,
        errorMessage,
        isSubmitting,
        loadingText,
        itemsByEvent,
        totalAmountFormatted,
        formatPrice,
        confirmCheckout,
    };
};
