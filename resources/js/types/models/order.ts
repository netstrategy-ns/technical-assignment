export type OrderStatus = 'pending' | 'completed' | 'cancelled';

export interface OrderListItem {
    id: number;
    public_id: string | null;
    status: OrderStatus;
    total_amount: string;
    created_at: string | null;
}

export interface OrderListItemForPage {
    id: number;
    public_id: string;
    status: OrderStatus;
    total_amount: string;
    created_at: string;
}

export interface OrderEvent {
    id: number;
    slug: string;
    title: string;
}

export interface OrderTicketType {
    id: number;
    name: string;
    event: OrderEvent;
}

export interface OrderTicket {
    id: number;
    quantity: number;
    unit_price: string;
    ticket: {
        id: number;
        ticket_type: OrderTicketType;
    };
}

export interface Order {
    id: number;
    status: OrderStatus;
    public_id: string | null;
    total_amount: string;
    created_at: string;
    order_items: OrderTicket[];
}
