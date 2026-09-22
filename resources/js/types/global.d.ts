import type { Auth } from '@/types/auth';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            marketplaceMode: 'regular' | 'silver' | 'gold';
            flash: {
                inquiry?: {
                    reference_code: string;
                    seller_name: string;
                } | null;
            };
            notificationSummary: {
                unread_count: number;
            };
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
