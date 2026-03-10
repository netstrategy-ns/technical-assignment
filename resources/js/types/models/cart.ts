export type HoldStatusValue = 'active' | 'expired' | 'completed';

export interface CartPayload {
    items: CartItem[];
    summary: CartSummary;
}

export interface CartSummary {
    total_items: number;
    total_amount: number;
}

export interface CartItem {
    id: number;
    quantity: number;
    status: HoldStatusValue;
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

export interface CartActionOptions {
    onSuccess?: () => void;
    onError?: (errors: Record<string, string>) => void;
    onFinish?: () => void;
}
