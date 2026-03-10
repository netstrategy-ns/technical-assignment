import type { UseFormatDataOptions } from '@/types/models/format';

export const useFormatData = (options: UseFormatDataOptions = {}) => {
    const locale = options.locale ?? 'it-IT';
    const currency = options.currency ?? 'EUR';

    const formatPrice = (value: number | string): string => {
        const numericValue = typeof value === 'number' ? value : Number(value);

        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency,
        }).format(numericValue);
    };

    const statusLabel = (status: string): string => {
        switch (status) {
            case 'completed':
                return 'Completato';
            case 'pending':
                return 'In attesa';
            case 'cancelled':
                return 'Annullato';
            default:
                return status;
        }
    };

    const formatDate = (value: string | null): string => {
        if (value === null) {
            return '-';
        }

        return new Date(value).toLocaleString(locale, {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return {
        formatPrice,
        statusLabel,
        formatDate,
    };
};
