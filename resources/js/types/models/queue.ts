import type { ComputedRef, Ref } from 'vue';

export type QueueStatusValue = 'waiting' | 'enabled' | 'expired' | 'completed';

export type QueueEvent = {
    id: number;
    slug: string;
    title: string;
    description: string | null;
    location: string | null;
    image_url: string | null;
    starts_at: string | null;
    ends_at: string | null;
    sale_starts_at: string | null;
    queue_enabled: boolean;
    queue_config?: Record<string, unknown> | null;
    category: { id: number; name: string } | null;
    venueType: { id: number; name: string } | null;
    ticket_types: Array<{
        id: number;
        name: string;
        quota_quantity: number;
        available_quantity: number;
        tickets: Array<{
            id: number;
            price: string;
            max_per_user: number | null;
            available_quantity: number;
            user_hold_quantity: number;
        }>;
    }>;
};

export type QueueStatus = {
    is_queue_enabled: boolean;
    in_queue_count: number;
    status: QueueStatusValue | null;
    position: number | null;
    estimated_wait_seconds: number | null;
    entered_at: string | null;
    enabled_at: string | null;
    enabled_until: string | null;
} | null;

export type QueuePageProps<TEvent extends QueueEvent> = {
    event?: TEvent;
    queueStatus?: QueueStatus;
};

export interface UseEventQueueOptions<TEvent extends QueueEvent> {
    event: Ref<TEvent>;
    initialQueueStatus: QueueStatus;
    isAuthenticated: ComputedRef<boolean> | Ref<boolean>;
    pollIntervalMs?: number;
    autoStart?: boolean;
    onEventUpdated?: (event: TEvent) => void;
}

export interface UseEventQueueReturn {
    queueStatus: Ref<QueueStatus>;
    queueError: Ref<string>;
    queueMessage: Ref<string>;
    queueLoading: Ref<boolean>;
    isQueueEnabled: ComputedRef<boolean>;
    isWaitingInQueue: ComputedRef<boolean>;
    isEnabledInQueue: ComputedRef<boolean>;
    isQueueMember: ComputedRef<boolean>;
    joinDisabled: ComputedRef<boolean>;
    shouldShowJoinAction: ComputedRef<boolean>;
    statusLabel: ComputedRef<string>;
    formatEstimatedWait: (seconds: number | null) => string;
    refreshQueueStatus: () => Promise<void>;
    joinQueue: () => Promise<void>;
    stopQueuePolling: () => void;
    startQueuePolling: () => void;
}
