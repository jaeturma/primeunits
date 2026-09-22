import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, Menu, Truck } from 'lucide-react';
import { useState } from 'react';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { dashboard, login } from '@/routes';

const financingMenuItems = [
    { title: 'Brand New', href: '/financing?type=brand-new' },
    { title: 'Used Cars', href: '/financing?type=used-cars' },
    { title: 'Sangla OR/CR', href: '/financing?type=sangla-or-cr' },
];

const navLinks = [
    { title: 'Sell', href: '/seller/apply' },
    { title: 'Rent', href: '/rentals' },
    { title: 'Insurance', href: '/insurance' },
    { title: 'Financing', href: '/financing' },
    { title: 'Ask', href: '/contact-us' },
];

const modeLabels: Record<string, string> = {
    regular: 'Regular',
    silver: 'Silver',
    gold: 'Gold',
};

export function PublicHeader() {
    const { auth, marketplaceMode } = usePage().props;
    const [mobileOpen, setMobileOpen] = useState(false);

    const showModeBadge =
        auth.user && marketplaceMode && marketplaceMode !== 'regular';

    return (
        <header className="border-b border-zinc-200 bg-white">
            <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 lg:px-6">
                <Link
                    href="/"
                    className="flex items-center gap-3 font-semibold"
                >
                    <span className="flex size-10 items-center justify-center rounded-md bg-brand-primary text-white">
                        <Truck className="size-5" />
                    </span>
                    <span className="text-xl">PrimeUnits</span>
                    {showModeBadge && (
                        <span className="rounded-full bg-brand-badge-background px-2 py-0.5 text-xs font-semibold text-brand-badge-foreground capitalize">
                            {modeLabels[marketplaceMode] ?? marketplaceMode}
                        </span>
                    )}
                </Link>

                <nav className="hidden items-center gap-1 text-sm md:flex">
                    <Link
                        href="/seller/apply"
                        className="rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100"
                    >
                        Sell
                    </Link>
                    <Link
                        href="/rentals"
                        className="rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100"
                    >
                        Rent
                    </Link>
                    <Link
                        href="/insurance"
                        className="rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100"
                    >
                        Insurance
                    </Link>
                    <div className="group relative">
                        <Link
                            href="/financing"
                            className="inline-flex items-center rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100"
                        >
                            Financing
                            <ChevronDown className="ml-1 size-4" />
                        </Link>
                        <div className="invisible absolute top-full right-0 z-20 w-44 rounded-md border border-zinc-200 bg-white p-1 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
                            {financingMenuItems.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className="block rounded px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100"
                                >
                                    {item.title}
                                </Link>
                            ))}
                        </div>
                    </div>
                    <Link
                        href="/contact-us"
                        className="rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100"
                    >
                        Ask
                    </Link>
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="rounded-md bg-zinc-950 px-4 py-2 font-semibold text-white"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <Link
                            href={login()}
                            className="rounded-md bg-zinc-950 px-4 py-2 font-semibold text-white hover:bg-zinc-800"
                        >
                            Login
                        </Link>
                    )}
                </nav>

                <div className="flex items-center gap-2 md:hidden">
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="flex h-11 items-center rounded-md bg-zinc-950 px-3 text-sm font-semibold text-white"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <Link
                            href={login()}
                            className="flex h-11 items-center rounded-md bg-zinc-950 px-3 text-sm font-semibold text-white"
                        >
                            Login
                        </Link>
                    )}
                    <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                        <button
                            type="button"
                            onClick={() => setMobileOpen(true)}
                            className="flex size-11 items-center justify-center rounded-md border border-zinc-300 text-zinc-700"
                            aria-label="Open menu"
                        >
                            <Menu className="size-5" />
                        </button>
                        <SheetContent
                            side="right"
                            className="w-full sm:max-w-xs"
                        >
                            <SheetHeader>
                                <SheetTitle>Menu</SheetTitle>
                            </SheetHeader>
                            <nav className="flex flex-col gap-1 px-4 pb-4">
                                {navLinks.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        onClick={() => setMobileOpen(false)}
                                        className="flex min-h-11 items-center rounded-md px-3 text-base font-medium text-zinc-800 hover:bg-zinc-100"
                                    >
                                        {item.title}
                                    </Link>
                                ))}
                                {showModeBadge && (
                                    <Link
                                        href="/settings/membership"
                                        onClick={() => setMobileOpen(false)}
                                        className="mt-2 flex min-h-11 items-center gap-2 rounded-md border border-brand-border bg-brand-surface-muted px-3 text-base font-medium text-brand-accent-strong"
                                    >
                                        <span className="rounded-full bg-brand-badge-background px-2 py-0.5 text-xs font-semibold text-brand-badge-foreground capitalize">
                                            {modeLabels[marketplaceMode] ??
                                                marketplaceMode}
                                        </span>
                                        Marketplace mode
                                    </Link>
                                )}
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
