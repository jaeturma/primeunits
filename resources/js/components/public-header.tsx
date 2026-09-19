import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, Truck } from 'lucide-react';
import { dashboard, login } from '@/routes';

const financingMenuItems = [
    { title: 'Brand New', href: '/financing?type=brand-new' },
    { title: 'Used Cars', href: '/financing?type=used-cars' },
    { title: 'Sangla OR/CR', href: '/financing?type=sangla-or-cr' },
];

export function PublicHeader() {
    const { auth } = usePage().props;

    return (
        <header className="border-b border-zinc-200 bg-white">
            <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 lg:px-6">
                <Link
                    href="/"
                    className="flex items-center gap-3 font-semibold"
                >
                    <span className="flex size-10 items-center justify-center rounded-md bg-emerald-600 text-white">
                        <Truck className="size-5" />
                    </span>
                    <span className="text-xl">PrimeUnits</span>
                </Link>

                <nav className="flex items-center gap-1 text-sm">
                    <Link
                        href="/seller/apply"
                        className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
                    >
                        Sell
                    </Link>
                    <Link
                        href="/rentals"
                        className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
                    >
                        Rent
                    </Link>
                    <Link
                        href="/insurance"
                        className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
                    >
                        Insurance
                    </Link>
                    <div className="group relative hidden md:block">
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
                        className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
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
            </div>
        </header>
    );
}
