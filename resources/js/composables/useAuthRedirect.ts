import type { AuthFlow, AuthRedirectPayload, AuthRedirectStorage } from '@/types/models/auth-redirect';

const AUTH_REDIRECT_KEY = 'authRedirectPath';
const AUTH_REDIRECT_TTL_MS = 5 * 60 * 1000;

const getBrowserOrigin = (): string | null => {
    if (typeof window === 'undefined' || typeof window.location === 'undefined') {
        return null;
    }

    return window.location.origin;
};

const isBrowserStorageAvailable = (): boolean => {
    return typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';
};

const sanitizePath = (rawPath: string): string | null => {
    const origin = getBrowserOrigin();

    if (origin === null) {
        return null;
    }

    const trimmed = rawPath.trim();

    if (trimmed.length === 0 || trimmed.length > 2048) {
        return null;
    }

    try {
        const parsed = new URL(trimmed, origin);
        if (parsed.origin !== origin) {
            return null;
        }

        if (!parsed.pathname.startsWith('/')) {
            return null;
        }

        if (parsed.pathname === '/login' || parsed.pathname === '/register') {
            return '/';
        }

        if (parsed.pathname === '/dashboard') {
            return '/';
        }

        if (parsed.pathname.startsWith('/admin')) {
            return '/';
        }

        return `${parsed.pathname}${parsed.search}`;
    } catch {
        return null;
    }
};

const isExpired = (entry: AuthRedirectPayload): boolean => {
    return Date.now() - entry.storedAt > AUTH_REDIRECT_TTL_MS;
};

const readStoredPayload = (): AuthRedirectPayload | null => {
    if (!isBrowserStorageAvailable()) {
        return null;
    }

    let raw: string | null;

    try {
        raw = window.localStorage.getItem(AUTH_REDIRECT_KEY);
    } catch {
        return null;
    }
    if (raw === null) {
        return null;
    }

    try {
        const payload: unknown = JSON.parse(raw);
        if (
            typeof payload !== 'object' ||
            payload === null ||
            typeof (payload as AuthRedirectPayload).path !== 'string' ||
            (payload as AuthRedirectPayload).path.length === 0 ||
            !((payload as AuthRedirectPayload).flow === 'login' || (payload as AuthRedirectPayload).flow === 'register') ||
            typeof (payload as AuthRedirectPayload).storedAt !== 'number'
        ) {
            clearStoredPayload();
            return null;
        }

        const typedPayload = payload as AuthRedirectPayload;
        if (isExpired(typedPayload) || !typedPayload.path.startsWith('/')) {
            clearStoredPayload();
            return null;
        }

        return typedPayload;
    } catch {
        clearStoredPayload();
        return null;
    }
};

const clearStoredPayload = (): void => {
    if (!isBrowserStorageAvailable()) {
        return;
    }

    try {
        window.localStorage.removeItem(AUTH_REDIRECT_KEY);
    } catch {
    }
};

const createSafePayload = (path: string, flow: AuthFlow): AuthRedirectPayload | null => {
    const safePath = sanitizePath(path);
    if (safePath === null) {
        return null;
    }

    return {
        path: safePath,
        flow,
        storedAt: Date.now(),
    };
};

const savePayload = (payload: AuthRedirectPayload): void => {
    if (!isBrowserStorageAvailable()) {
        return;
    }

    try {
        window.localStorage.setItem(AUTH_REDIRECT_KEY, JSON.stringify(payload));
    } catch {
    }
};

export const useAuthRedirect = (): AuthRedirectStorage => {
    const storePath = (path: string, flow: AuthFlow): void => {
        const payload = createSafePayload(path, flow);

        if (payload === null) {
            return;
        }

        savePayload(payload);
    };

    const storeCurrent = (flow: AuthFlow): void => {
        if (typeof window === 'undefined') {
            return;
        }

        storePath(`${window.location.pathname}${window.location.search}`, flow);
    };

    const get = (flow?: AuthFlow): string | null => {
        const stored = readStoredPayload();
        if (stored === null) {
            return null;
        }

        if (flow !== undefined && stored.flow !== flow) {
            clearStoredPayload();
            return null;
        }

        return stored.path;
    };

    const consume = (flow?: AuthFlow): string | null => {
        const value = get(flow);
        clearStoredPayload();
        return value;
    };

    return {
        get,
        storeCurrent,
        storePath,
        consume,
        clear: clearStoredPayload,
    };
};

