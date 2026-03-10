import { router, usePoll } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { QueueEvent, QueuePageProps, QueueStatus, UseEventQueueOptions, UseEventQueueReturn } from '@/types/models/queue';

const formatEstimatedWait = (seconds: number | null): string => {
    const total = Math.max(0, seconds ?? 0);
    const minutes = Math.floor(total / 60);
    const remaining = total % 60;
    return `${String(minutes).padStart(2, '0')}:${String(remaining).padStart(2, '0')}`;
};

const getFirstQueueErrorMessage = (errors: unknown, fallback: string): string => {
    if (!errors || typeof errors !== 'object') {
        return fallback;
    }

    const values = Object.values(errors as Record<string, unknown>);

    for (const value of values) {
        if (typeof value === 'string' && value.trim().length > 0) {
            return value;
        }

        if (Array.isArray(value)) {
            for (const entry of value) {
                if (typeof entry === 'string' && entry.trim().length > 0) {
                    return entry;
                }
            }
        }
    }

    return fallback;
};

export const useEventQueue = <TEvent extends QueueEvent>({
    event,
    initialQueueStatus,
    isAuthenticated,
    pollIntervalMs = 15_000,
    autoStart = true,
    onEventUpdated,
}: UseEventQueueOptions<TEvent>): UseEventQueueReturn => {
    const queueStatus = ref<QueueStatus>(initialQueueStatus);
    const queueError = ref('');
    const queueMessage = ref('');
    const queueLoading = ref(false);
    const isQueueEnabled = computed(() => Boolean(event.value.queue_enabled));
    const isUserAuthenticated = computed(() => Boolean(isAuthenticated.value));
    const canInteractWithQueue = computed(() => isUserAuthenticated.value && isQueueEnabled.value);
    const isInQueue = computed(() => queueStatus.value?.status !== null);
    const shouldPollQueue = computed(
        () => canInteractWithQueue.value && ['waiting', 'enabled'].includes(queueStatus.value?.status ?? ''),
    );
    const isWaitingInQueue = computed(() => queueStatus.value?.status === 'waiting');
    const isEnabledInQueue = computed(() => queueStatus.value?.status === 'enabled');
    const joinDisabled = computed(() => !isQueueEnabled.value || queueLoading.value || queueStatus.value?.status === 'enabled');
    const shouldShowJoinAction = computed(() => canInteractWithQueue.value && !isEnabledInQueue.value);
    const queueUsersInLine = computed(() => queueStatus.value?.in_queue_count ?? 0);

    const statusLabel = computed(() => {
        if (!canInteractWithQueue.value) {
            return '';
        }

        if (queueLoading.value) {
            return 'Aggiornamento stato coda...';
        }

        if (isEnabledInQueue.value) {
            return 'Sei stato abilitato. Puoi procedere con il carrello.';
        }

        if (isWaitingInQueue.value) {
            const pollLabel = `${Math.max(1, Math.round(pollIntervalMs / 1000))}s`;
            return `In coda: posizione ${queueStatus.value?.position ?? '-'} · attesa stimata ${formatEstimatedWait(queueStatus.value?.estimated_wait_seconds ?? null)} · aggiornamento automatico ogni ${pollLabel}.`;
        }

        if (queueStatus.value?.status === 'expired') {
            return 'Il tuo slot è scaduto. Rientra in coda per continuare.';
        }

        if (queueStatus.value?.status === 'completed') {
            return 'Checkout completato. Riaccedi in coda se necessario per questo evento.';
        }

        return `In coda ora: ${queueUsersInLine.value} utenti.`;
    });

    const queueJoinUrl = () => `/events/${event.value.id}/queue/join`;

    const applyQueuePayload = (payload: QueuePageProps<TEvent> | null): void => {
        if (payload?.queueStatus !== undefined) {
            queueStatus.value = payload.queueStatus;
        }

        if (payload?.event !== undefined) {
            onEventUpdated?.(payload.event);
        }
    };

    const { start: startQueuePolling, stop: stopQueuePolling } = usePoll(
        pollIntervalMs,
        {
            only: ['event', 'queueStatus'],
            onSuccess: (page: unknown): void => {
                const props = (page as { props?: QueuePageProps<TEvent> })?.props ?? null;
                applyQueuePayload(props);
            },
            onError: (errors: unknown): void => {
                queueError.value = getFirstQueueErrorMessage(
                    errors,
                    'Impossibile aggiornare lo stato della coda.',
                );
            },
            onFinish: (): void => {
                queueLoading.value = false;
                syncQueuePolling();
            },
        },
        {
            autoStart: false,
        },
    );

    const syncQueuePolling = (): void => {
        if (!canInteractWithQueue.value) {
            stopQueuePolling();
            return;
        }

        if (shouldPollQueue.value) {
            startQueuePolling();
            return;
        }

        stopQueuePolling();
    };

    const refreshQueueStatus = async (): Promise<void> => {
        if (!canInteractWithQueue.value) {
            return;
        }

        queueLoading.value = true;
        queueError.value = '';

        await new Promise<void>((resolve) => {
            let resolved = false;
            const finish = (): void => {
                if (resolved) {
                    return;
                }

                resolved = true;
                queueLoading.value = false;
                syncQueuePolling();
                resolve();
            };

            router.reload({
                only: ['event', 'queueStatus'],
                onSuccess: (page: unknown): void => {
                    const props = (page as { props?: QueuePageProps<TEvent> })?.props ?? null;
                    applyQueuePayload(props);
                },
                onError: (errors: unknown): void => {
                    queueError.value = getFirstQueueErrorMessage(
                        errors,
                        'Errore temporaneo nel caricamento dello stato coda.',
                    );
                },
                onFinish: finish,
            });
        });
    };

    const joinQueue = async (): Promise<void> => {
        if (!canInteractWithQueue.value || queueLoading.value) {
            return;
        }

        queueLoading.value = true;
        queueError.value = '';
        queueMessage.value = '';

        await new Promise<void>((resolve) => {
            let resolved = false;
            const finish = (): void => {
                if (resolved) {
                    return;
                }

                resolved = true;
                queueLoading.value = false;
                syncQueuePolling();
                resolve();
            };

            router.post(
                queueJoinUrl(),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (page: unknown): void => {
                        const props = (page as { props?: QueuePageProps<TEvent> })?.props ?? null;
                        applyQueuePayload(props);
                        queueMessage.value = 'Richiesta registrata.';
                    },
                    onError: (errors: unknown): void => {
                        queueError.value = getFirstQueueErrorMessage(
                            errors,
                            'Errore durante l\'iscrizione in coda.',
                        );
                    },
                    onFinish: finish,
                },
            );
        });
    };

    const refreshQueueOnVisibility = (): void => {
        if (typeof document === 'undefined' || document.hidden) {
            return;
        }

        void refreshQueueStatus();
    };

    watch(
        queueStatus,
        () => {
            syncQueuePolling();
        },
        { deep: true },
    );

    if (autoStart) {
        onMounted(() => {
            if (!canInteractWithQueue.value) {
                return;
            }

            if (isQueueEnabled.value) {
                void refreshQueueStatus();
            }

            window.addEventListener('visibilitychange', refreshQueueOnVisibility);
        });

        onBeforeUnmount(() => {
            stopQueuePolling();
            window.removeEventListener('visibilitychange', refreshQueueOnVisibility);
        });
    }

    return {
        queueStatus,
        queueError,
        queueMessage,
        queueLoading,
        isQueueEnabled,
        isWaitingInQueue,
        isEnabledInQueue,
        isQueueMember: isInQueue,
        joinDisabled,
        shouldShowJoinAction,
        statusLabel,
        formatEstimatedWait,
        refreshQueueStatus,
        joinQueue,
        stopQueuePolling,
        startQueuePolling,
    };
};
