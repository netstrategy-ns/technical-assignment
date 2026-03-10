export type CheckoutPageErrorBag = Record<string, unknown>;

export interface CheckoutLine {
    holdId: number;
    ticketTypeName: string;
    quantity: number;
    price: number;
    subtotal: number;
}

export interface CheckoutEventGroup {
    event: {
        id: number;
        slug: string;
        title: string;
    };
    lines: CheckoutLine[];
    eventTotal: number;
}
