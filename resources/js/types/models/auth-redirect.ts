export type AuthFlow = 'login' | 'register';

export interface AuthRedirectPayload {
    path: string;
    flow: AuthFlow;
    storedAt: number;
}

export interface AuthRedirectStorage {
    get: (flow?: AuthFlow) => string | null;
    storeCurrent: (flow: AuthFlow) => void;
    storePath: (path: string, flow: AuthFlow) => void;
    consume: (flow?: AuthFlow) => string | null;
    clear: () => void;
}
