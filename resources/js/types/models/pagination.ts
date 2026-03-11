export type PerPageOption = 12 | 24 | 32 | 48;

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationNavPayload {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}
