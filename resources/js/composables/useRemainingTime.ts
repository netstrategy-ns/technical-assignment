import { onMounted, onUnmounted, ref } from 'vue';

// Gestisce un ticker globale e utility per secondi/rimanenti
export function useRemainingTime(tickIntervalMs = 1_000, autoStart = true) {
    const now = ref(Date.now());
    let tickerId: number | null = null;

    // Avvia il ticker periodico dell'orario corrente
    const startTicker = (): void => {
        if (tickerId !== null || typeof window === 'undefined') {
            return;
        }

        tickerId = window.setInterval(() => {
            now.value = Date.now();
        }, tickIntervalMs);
    };

    // Ferma il ticker e pulisce l'intervallo
    const stopTicker = (): void => {
        if (tickerId === null || typeof window === 'undefined') {
            return;
        }

        window.clearInterval(tickerId);
        tickerId = null;
    };

    // Converte data di scadenza in secondi rimanenti (min 0)
    const parseRemainingSeconds = (expiresAt: string | null): number => {
        if (!expiresAt) {
            return 0;
        }

        const expiresAtMs = Date.parse(expiresAt);
        if (!Number.isFinite(expiresAtMs)) {
            return 0;
        }

        return Math.max(0, Math.floor((expiresAtMs - now.value) / 1000));
    };

    // Format mm:ss dei secondi rimanenti
    const formatRemainingTime = (totalSeconds: number): string => {
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    onMounted(() => {
        if (!autoStart) {
            return;
        }

        startTicker();
    });

    onUnmounted(() => {
        stopTicker();
    });

    return {
        parseRemainingSeconds,
        formatRemainingTime,
        startTicker,
        stopTicker,
    };
}
