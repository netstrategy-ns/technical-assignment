import type { QueueStatus } from '@/types/models';
import { onUnmounted, ref } from 'vue';

export function useQueuePolling(eventSlug: string, initialStatus: QueueStatus | null) {
    const status = ref<QueueStatus | null>(initialStatus);
    const isPolling = ref(false);
    let interval: ReturnType<typeof setInterval> | null = null;

    async function poll() {
        try {
            const response = await fetch(`/events/${eventSlug}/queue/status`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                status.value = await response.json();
            }
        } catch {
            // Silently fail on network errors; will retry on next poll
        }
    }

    function startPolling(intervalMs = 5000) {
        if (isPolling.value) return;
        isPolling.value = true;
        interval = setInterval(poll, intervalMs);
    }

    function stopPolling() {
        if (interval) {
            clearInterval(interval);
            interval = null;
        }
        isPolling.value = false;
    }

    onUnmounted(() => stopPolling());

    return {
        status,
        isPolling,
        poll,
        startPolling,
        stopPolling,
    };
}
