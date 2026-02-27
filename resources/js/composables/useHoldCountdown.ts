import { computed, onUnmounted, ref } from 'vue';

export function useHoldCountdown(expiresAt: string) {
    const now = ref(Date.now());

    const interval = setInterval(() => {
        now.value = Date.now();
    }, 1000);

    onUnmounted(() => clearInterval(interval));

    const expiresAtMs = new Date(expiresAt).getTime();

    const remainingSeconds = computed(() => {
        const diff = expiresAtMs - now.value;
        return Math.max(0, Math.floor(diff / 1000));
    });

    const isExpired = computed(() => remainingSeconds.value <= 0);

    const formatted = computed(() => {
        const total = remainingSeconds.value;
        const minutes = Math.floor(total / 60);
        const seconds = total % 60;
        return `${minutes}:${String(seconds).padStart(2, '0')}`;
    });

    return {
        remainingSeconds,
        isExpired,
        formatted,
    };
}
