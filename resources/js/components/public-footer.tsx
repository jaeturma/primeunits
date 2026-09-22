import { Link } from '@inertiajs/react';
import { Truck } from 'lucide-react';
import { login } from '@/routes';

const vehicleTypes = [
    'Sedan',
    'SUV',
    'Pickup',
    'Van',
    'Truck',
    'Motorcycle',
    'Tricycle',
    'E-Bike',
];

const equipmentTypes = [
    'Excavator',
    'Wheel Loader',
    'Forklift',
    'Tractor',
    'Harvester',
    'Dump Truck',
    'Backhoe',
    'Bulldozer',
];

const regions = [
    'Metro Manila',
    'CALABARZON',
    'Central Luzon',
    'Central Visayas',
    'Davao Region',
    'Northern Mindanao',
    'Western Visayas',
    'Bicol Region',
];

export function PublicFooter() {
    return (
        <footer className="border-t border-zinc-800 bg-zinc-950 text-white">
            <div className="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr_1fr_1fr] lg:px-6">
                <div>
                    <Link
                        href="/"
                        className="flex items-center gap-3 font-semibold"
                    >
                        <span className="flex size-10 items-center justify-center rounded-md bg-emerald-500 text-zinc-950">
                            <Truck className="size-5" />
                        </span>
                        <span className="text-xl">PrimeUnits</span>
                    </Link>
                    <p className="mt-4 max-w-sm text-sm leading-6 text-white/65">
                        Marketplace tools for Philippine buyers and verified
                        sellers of vehicles, motorcycles, farm machines, and
                        work-ready equipment.
                    </p>
                </div>

                <FooterColumn title="Vehicles" items={vehicleTypes} />
                <FooterColumn title="Equipment" items={equipmentTypes} />
                <FooterColumn title="Locations" items={regions} />
                <div>
                    <h2 className="text-sm font-semibold">Company</h2>
                    <div className="mt-3 grid gap-2">
                        {[
                            ['About Us', '/about-us'],
                            ['Contact Us', '/contact-us'],
                            ['Terms and Conditions', '/terms-and-conditions'],
                            ['Privacy Policy', '/privacy-policy'],
                            ['Copyrights', '/copyrights'],
                        ].map(([label, href]) => (
                            <Link
                                key={label}
                                href={href}
                                className="text-sm text-white/62 hover:text-white"
                            >
                                {label}
                            </Link>
                        ))}
                    </div>
                </div>
            </div>

            <div className="border-t border-white/10">
                <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 text-xs text-white/55 sm:flex-row sm:items-center sm:justify-between lg:px-6">
                    <p>(c) 2026 PrimeUnits. All rights reserved.</p>
                    <div className="flex flex-wrap gap-4">
                        <Link href="/#results" className="hover:text-white">
                            Results
                        </Link>
                        <Link href="/seller/apply" className="hover:text-white">
                            Sell a unit
                        </Link>
                        <Link href={login()} className="hover:text-white">
                            Account
                        </Link>
                        <Link
                            href="/privacy-policy"
                            className="hover:text-white"
                        >
                            Privacy Policy
                        </Link>
                        <Link href="/copyrights" className="hover:text-white">
                            Copyrights
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    );
}

function FooterColumn({ title, items }: { title: string; items: string[] }) {
    return (
        <div>
            <h2 className="text-sm font-semibold">{title}</h2>
            <div className="mt-3 grid gap-2">
                {items.map((item) => (
                    <Link
                        key={item}
                        href={`/?classification=${encodeURIComponent(item)}#results`}
                        className="text-sm text-white/62 hover:text-white"
                    >
                        {item}
                    </Link>
                ))}
            </div>
        </div>
    );
}
