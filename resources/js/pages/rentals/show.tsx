import { FormEvent, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    Bus,
    CalendarDays,
    Car,
    CheckCircle,
    MapPin,
    Truck,
    User,
    UserX,
    Users,
} from 'lucide-react';

type RentalPackage = {
    id: number;
    name: string;
    duration_type: string;
    duration_value: number;
    price: string;
    inclusions: string | null;
};

type RentalImage = { id: number; url: string; is_primary: boolean };

type RentalUnit = {
    id: number;
    slug: string;
    name: string;
    rental_type: string;
    rental_type_label: string;
    brand: string | null;
    model: string | null;
    year_model: number | null;
    with_driver: boolean;
    price_per_day: string;
    price_per_hour: string | null;
    capacity: number | null;
    description: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    provider: { name: string };
    packages: RentalPackage[];
    availability: Record<string, boolean>;
    images: RentalImage[];
    attachments: Array<{ id: number; name: string; url: string; size: number }>;
};

const durationLabel: Record<string, string> = {
    hourly: 'hr',
    daily: 'day',
    weekly: 'week',
    monthly: 'month',
};

const typeIcons: Record<string, typeof Car> = {
    bus_rental: Bus,
    shuttle: Bus,
    truck_rental: Truck,
    equipment_rental: Truck,
};

export default function RentalShow({ rental }: { rental: RentalUnit }) {
    const [activeImage, setActiveImage] = useState(0);
    const { auth, flash } = usePage().props as any;
    const primary = rental.images[activeImage] ?? rental.images[0];
    const Icon = typeIcons[rental.rental_type] ?? Car;

    const { data, setData, post, processing, errors } = useForm({
        rental_unit_id: rental.id.toString(),
        rental_package_id: '',
        start_date: '',
        end_date: '',
        pickup_address: '',
        message: '',
    });

    function submitBooking(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(`/rentals/${rental.slug}/book`, { preserveScroll: true });
    }

    const location = [rental.municipality, rental.province, rental.region]
        .filter(Boolean)
        .join(', ');

    return (
        <>
            <Head title={rental.name} />
            <div className="mx-auto w-full max-w-6xl gap-6 p-4 lg:grid lg:grid-cols-[1fr_360px] lg:p-6">
                {/* Left: Images + details */}
                <div className="space-y-4">
                    {/* Main image */}
                    <div className="aspect-video overflow-hidden rounded-lg border bg-muted">
                        {primary ? (
                            <img
                                src={primary.url}
                                alt={rental.name}
                                className="h-full w-full object-cover"
                            />
                        ) : (
                            <div className="flex h-full items-center justify-center text-muted-foreground/30">
                                <Icon className="size-16" />
                            </div>
                        )}
                    </div>

                    {/* Thumbnails */}
                    {rental.images.length > 1 && (
                        <div className="grid grid-cols-5 gap-2">
                            {rental.images.map((img, i) => (
                                <button
                                    key={img.id}
                                    type="button"
                                    onClick={() => setActiveImage(i)}
                                    className={`overflow-hidden rounded-md border-2 transition-colors ${
                                        i === activeImage
                                            ? 'border-emerald-600'
                                            : 'border-transparent'
                                    }`}
                                >
                                    <img
                                        src={img.url}
                                        alt=""
                                        className="aspect-video w-full object-cover"
                                    />
                                </button>
                            ))}
                        </div>
                    )}

                    {/* Description */}
                    {rental.description && (
                        <section className="rounded-lg border p-5">
                            <h2 className="font-semibold">About this unit</h2>
                            <p className="mt-3 text-sm leading-6 whitespace-pre-line text-muted-foreground">
                                {rental.description}
                            </p>
                        </section>
                    )}

                    {/* Packages */}
                    {rental.packages.length > 0 && (
                        <section className="rounded-lg border p-5">
                            <h2 className="font-semibold">Rental Packages</h2>
                            <div className="mt-4 grid gap-3">
                                {rental.packages.map((pkg) => (
                                    <div
                                        key={pkg.id}
                                        className="rounded-md border p-4"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="space-y-1">
                                                <p className="font-medium">
                                                    {pkg.name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {pkg.duration_value}{' '}
                                                    {durationLabel[
                                                        pkg.duration_type
                                                    ] ?? pkg.duration_type}
                                                    {pkg.duration_value > 1
                                                        ? 's'
                                                        : ''}
                                                </p>
                                                {pkg.inclusions && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {pkg.inclusions}
                                                    </p>
                                                )}
                                            </div>
                                            <p className="shrink-0 font-semibold text-emerald-700">
                                                PHP{' '}
                                                {Number(
                                                    pkg.price,
                                                ).toLocaleString()}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    )}

                    {rental.attachments.length > 0 && (
                        <section className="rounded-lg border p-5">
                            <h2 className="font-semibold">Documents</h2>
                            <div className="mt-3 grid gap-2">
                                {rental.attachments.map((attachment) => (
                                    <a
                                        key={attachment.id}
                                        href={attachment.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="truncate rounded-md border px-3 py-2 text-sm hover:bg-muted"
                                    >
                                        {attachment.name}
                                    </a>
                                ))}
                            </div>
                        </section>
                    )}
                </div>

                {/* Right: Booking sidebar */}
                <aside className="mt-4 space-y-4 lg:mt-0">
                    {/* Success flash */}
                    {flash?.booking_code && (
                        <div className="flex gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <CheckCircle className="mt-0.5 size-5 shrink-0 text-emerald-600" />
                            <div>
                                <p className="text-sm font-semibold text-emerald-800">
                                    Booking inquiry sent!
                                </p>
                                <p className="mt-0.5 text-xs text-emerald-700">
                                    Reference code:{' '}
                                    <span className="font-mono font-bold">
                                        {flash.booking_code}
                                    </span>
                                </p>
                                <p className="mt-1 text-xs text-emerald-700">
                                    The rental provider will contact you to
                                    confirm.
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Unit summary card */}
                    <div className="rounded-lg border bg-card p-5">
                        {/* Type + driver badge row */}
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                {rental.rental_type_label}
                            </span>
                            <span
                                className={`flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                    rental.with_driver
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-zinc-100 text-zinc-700'
                                }`}
                            >
                                {rental.with_driver ? (
                                    <>
                                        <User className="size-3" /> With Driver
                                        / Operator
                                    </>
                                ) : (
                                    <>
                                        <UserX className="size-3" /> Self-Drive
                                    </>
                                )}
                            </span>
                        </div>

                        <h1 className="mt-3 text-xl leading-snug font-semibold">
                            {rental.name}
                        </h1>

                        {(rental.brand || rental.model) && (
                            <p className="mt-1 text-sm text-muted-foreground">
                                {[rental.brand, rental.model, rental.year_model]
                                    .filter(Boolean)
                                    .join(' · ')}
                            </p>
                        )}

                        {/* Pricing */}
                        <div className="mt-4">
                            <p className="text-2xl font-bold text-emerald-700">
                                PHP{' '}
                                {Number(rental.price_per_day).toLocaleString()}
                                <span className="text-sm font-normal text-muted-foreground">
                                    {' '}
                                    / day
                                </span>
                            </p>
                            {rental.price_per_hour && (
                                <p className="mt-0.5 text-sm text-muted-foreground">
                                    PHP{' '}
                                    {Number(
                                        rental.price_per_hour,
                                    ).toLocaleString()}{' '}
                                    / hour
                                </p>
                            )}
                        </div>

                        {/* Meta */}
                        <dl className="mt-4 space-y-2 text-sm">
                            {rental.capacity != null && rental.capacity > 0 && (
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <Users className="size-4 shrink-0" />
                                    <span>{rental.capacity} pax capacity</span>
                                </div>
                            )}
                            {location && (
                                <div className="flex items-center gap-2 text-muted-foreground">
                                    <MapPin className="size-4 shrink-0" />
                                    <span>{location}</span>
                                </div>
                            )}
                            <div className="flex items-center gap-2 text-muted-foreground">
                                <CalendarDays className="size-4 shrink-0" />
                                <span>Provider: {rental.provider.name}</span>
                            </div>
                        </dl>
                    </div>

                    {/* Booking form / login gate */}
                    <div className="rounded-lg border bg-card p-5">
                        <h2 className="font-semibold">
                            Send a Booking Inquiry
                        </h2>

                        {auth.user ? (
                            <form
                                onSubmit={submitBooking}
                                className="mt-4 space-y-4"
                            >
                                {rental.packages.length > 0 && (
                                    <div className="space-y-1.5">
                                        <label className="text-xs font-medium">
                                            Package (optional)
                                        </label>
                                        <select
                                            value={data.rental_package_id}
                                            onChange={(e) =>
                                                setData(
                                                    'rental_package_id',
                                                    e.target.value,
                                                )
                                            }
                                            className="h-9 w-full rounded-md border bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                        >
                                            <option value="">
                                                No specific package
                                            </option>
                                            {rental.packages.map((p) => (
                                                <option key={p.id} value={p.id}>
                                                    {p.name} — PHP{' '}
                                                    {Number(
                                                        p.price,
                                                    ).toLocaleString()}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}

                                <div className="grid grid-cols-2 gap-3">
                                    <div className="space-y-1.5">
                                        <label className="text-xs font-medium">
                                            Start Date{' '}
                                            <span className="text-destructive">
                                                *
                                            </span>
                                        </label>
                                        <input
                                            type="date"
                                            value={data.start_date}
                                            min={
                                                new Date()
                                                    .toISOString()
                                                    .split('T')[0]
                                            }
                                            required
                                            onChange={(e) =>
                                                setData(
                                                    'start_date',
                                                    e.target.value,
                                                )
                                            }
                                            className="h-9 w-full rounded-md border bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                        />
                                        {errors.start_date && (
                                            <p className="text-xs text-destructive">
                                                {errors.start_date}
                                            </p>
                                        )}
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-xs font-medium">
                                            End Date
                                        </label>
                                        <input
                                            type="date"
                                            value={data.end_date}
                                            min={data.start_date}
                                            onChange={(e) =>
                                                setData(
                                                    'end_date',
                                                    e.target.value,
                                                )
                                            }
                                            className="h-9 w-full rounded-md border bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-1.5">
                                    <label className="text-xs font-medium">
                                        Pickup / Delivery Address
                                    </label>
                                    <input
                                        type="text"
                                        value={data.pickup_address}
                                        onChange={(e) =>
                                            setData(
                                                'pickup_address',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Street, barangay, city"
                                        className="h-9 w-full rounded-md border bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <label className="text-xs font-medium">
                                        Message
                                    </label>
                                    <textarea
                                        value={data.message}
                                        onChange={(e) =>
                                            setData('message', e.target.value)
                                        }
                                        placeholder="Purpose of rental, special requirements, preferred time..."
                                        className="min-h-24 w-full rounded-md border bg-background px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="h-10 w-full rounded-md bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    {processing
                                        ? 'Sending…'
                                        : 'Send Booking Inquiry'}
                                </button>
                            </form>
                        ) : (
                            <div className="mt-4 rounded-md border border-dashed p-5 text-center">
                                <CalendarDays className="mx-auto size-8 text-muted-foreground/40" />
                                <p className="mt-3 text-sm font-medium">
                                    Login required to book
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Guests can browse — create an account to
                                    send inquiries and book rentals.
                                </p>
                                <div className="mt-4 flex gap-2">
                                    <Link
                                        href="/login"
                                        className="flex-1 rounded-md bg-emerald-600 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-700"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="flex-1 rounded-md border py-2 text-center text-sm font-semibold hover:bg-accent"
                                    >
                                        Register
                                    </Link>
                                </div>
                            </div>
                        )}
                    </div>
                </aside>
            </div>
        </>
    );
}
