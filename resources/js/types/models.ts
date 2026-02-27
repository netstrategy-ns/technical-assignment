export type Category = {
    id: number;
    name: string;
    slug: string;
};

export type Event = {
    id: number;
    category_id: number;
    title: string;
    slug: string;
    description: string;
    venue: string;
    city: string;
    image: string | null;
    starts_at: string;
    ends_at: string | null;
    sale_starts_at: string;
    is_featured: boolean;
    queue_enabled: boolean;
    queue_concurrency_limit: number;
    created_at: string;
    updated_at: string;
    category?: Category;
};

export type TicketType = {
    id: number;
    event_id: number;
    name: string;
    price: string;
    total_quantity: number;
    per_user_limit: number;
    sort_order: number;
};

export type TicketTypeAvailability = {
    ticket_type_id: number;
    name: string;
    price: string;
    total_quantity: number;
    per_user_limit: number;
    sold: number;
    held: number;
    available: number;
    user_held: number;
};

export type Hold = {
    id: number;
    user_id: number;
    ticket_type_id: number;
    event_id: number;
    quantity: number;
    expires_at: string;
    status: 'active' | 'expired' | 'converted';
    created_at: string;
    updated_at: string;
    ticket_type?: TicketType;
};

export type OrderItem = {
    id: number;
    order_id: number;
    ticket_type_id: number;
    quantity: number;
    unit_price: string;
    created_at: string;
    updated_at: string;
    ticket_type?: TicketType;
};

export type Order = {
    id: number;
    user_id: number;
    event_id: number;
    idempotency_key: string;
    total_amount: string;
    status: 'confirmed' | 'cancelled';
    created_at: string;
    updated_at: string;
    event?: Event;
    items?: OrderItem[];
};

export type QueueStatus = {
    id: number;
    status: 'waiting' | 'active' | 'expired' | 'completed';
    position: number;
    ahead: number;
    token: string;
    expires_at: string | null;
};

export type EventFilters = {
    search: string;
    category_id: string | number;
    city: string;
    date_from: string;
    date_to: string;
    featured: boolean;
    sort: 'nearest' | 'newest' | 'featured';
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
    first_page_url: string | null;
    last_page_url: string | null;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
};

export type Flash = {
    success?: string | null;
    error?: string | null;
    info?: string | null;
};
