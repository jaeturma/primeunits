import { createInertiaApp, router } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name === 'insurance':
                return null;
            case name.startsWith('financing/'):
                return null;
            case name.startsWith('listings/'):
                return null;
            case name.startsWith('rentals/'):
                return null;
            case name.startsWith('dealers/'):
                return null;
            case name === 'sellers/show':
                return null;
            case name === 'seo/landing':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();

// Keeps the tier-theme attribute in sync with the server-authorized
// marketplace mode on every SPA navigation (e.g. after switching mode in
// settings). The mode itself is never trusted from the client — this only
// mirrors what HandleMarketplaceMode already resolved server-side onto
// <html data-marketplace-mode>, which app.blade.php also sets on first
// paint to avoid a flash of the wrong theme.
router.on('navigate', (event) => {
    const mode = (event.detail.page.props as { marketplaceMode?: string })
        .marketplaceMode;

    if (mode && typeof document !== 'undefined') {
        document.documentElement.dataset.marketplaceMode = mode;
    }
});
