import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    Car,
    ClipboardList,
    CircleHelp,
    FileText,
    Headphones,
    Landmark,
    LifeBuoy,
    ShieldCheck,
    Truck,
    WalletCards,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

const insuranceTypes = [
    'Car Insurance',
    'Truck Insurance',
    'CTPL Insurance',
    'Motorcycle Insurance',
    'Three-Wheeler Insurance',
    'Farm and Heavy Equipment Cover',
];

const benefits = [
    [
        'Low premiums',
        'Compare practical coverage options built around unit value, use, and location.',
        'text-blue-600',
        'bg-blue-50',
    ],
    [
        'Fast policy assistance',
        'Get help preparing details and documents before talking to an insurance partner.',
        'text-emerald-600',
        'bg-emerald-50',
    ],
    [
        'Roadside and claims support',
        'Plan for emergency assistance, repair coordination, and claim filing.',
        'text-amber-600',
        'bg-amber-50',
    ],
    [
        'Acts of Nature options',
        'Add flood, typhoon, and natural event protection where available.',
        'text-violet-600',
        'bg-violet-50',
    ],
];

const steps = [
    [
        'Provide unit details',
        'Select brand, model, year, category, condition, and declared use.',
    ],
    [
        'Share contact and location',
        'Add city, province, mobile number, and preferred coverage type.',
    ],
    [
        'Compare plan options',
        'Review CTPL, comprehensive, add-ons, and payment terms.',
    ],
];

const coverage = [
    [
        'CTPL',
        'Basic compulsory third-party liability required for registration.',
    ],
    [
        'Comprehensive',
        'Covers own damage, theft, fire, third-party exposure, and selected add-ons.',
    ],
    [
        'Commercial vehicle cover',
        'For trucks, three-wheelers, delivery vans, and business-use units.',
    ],
    [
        'Equipment protection',
        'For selected farm and heavy equipment risks, subject to partner underwriting.',
    ],
];

const faqs = [
    [
        'Is insurance mandatory in the Philippines?',
        'Yes. Registered vehicles need at least CTPL. Many owners add comprehensive coverage for stronger financial protection.',
    ],
    [
        'What documents are usually needed?',
        'OR/CR, valid ID, driver details, vehicle details, and sometimes inspection photos or proof of address.',
    ],
    [
        'Can trucks and motorcycles be insured?',
        'Yes. Coverage depends on use, unit age, condition, and insurer rules.',
    ],
    [
        'How are premiums calculated?',
        'Premiums usually consider make, model, age, market value, location, usage, and selected add-ons.',
    ],
    [
        'Can I choose the insurance provider?',
        'Yes. You can compare quotes and choose a provider based on price, coverage, claims support, and repair network.',
    ],
    [
        'Can insurance be transferred to another unit?',
        'Some policies can be amended or transferred, but this depends on insurer rules and the new unit details.',
    ],
];

const insuranceBenefits: Array<{
    title: string;
    icon: LucideIcon;
    color: string;
    background: string;
}> = [
    {
        title: '24/7 claim guidance',
        icon: LifeBuoy,
        color: 'text-emerald-700',
        background: 'bg-emerald-50',
    },
    {
        title: 'Repair coordination',
        icon: BadgeCheck,
        color: 'text-blue-700',
        background: 'bg-blue-50',
    },
    {
        title: 'Legal and third-party support',
        icon: Landmark,
        color: 'text-violet-700',
        background: 'bg-violet-50',
    },
    {
        title: 'Flexible payment options',
        icon: WalletCards,
        color: 'text-amber-700',
        background: 'bg-amber-50',
    },
];

type InsuranceCompany = {
    id: number;
    name: string;
    description: string | null;
    coverage_types: string[];
    ctpl_price: string | null;
    premium_starting_price: string | null;
    contact_number: string;
    contact_email: string | null;
    website: string | null;
    province: string | null;
    municipality: string | null;
};

type Props = {
    companies: InsuranceCompany[];
};

export default function Insurance({ companies }: Props) {
    return (
        <>
            <Head title="Insurance | PrimeUnits Philippines">
                <meta
                    name="description"
                    content="Compare practical insurance options for cars, trucks, motorcycles, three-wheelers, farm equipment, and heavy equipment in the Philippines."
                />
            </Head>

            <main className="min-h-screen bg-[#f4f5f2] text-zinc-950">
                <header className="border-b border-zinc-200 bg-white">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 lg:px-6">
                        <Link
                            href="/"
                            className="flex items-center gap-3 font-semibold"
                        >
                            <span className="flex size-10 items-center justify-center rounded-md bg-emerald-600 text-white">
                                <Truck className="size-5" />
                            </span>
                            <span className="text-xl">PrimeUnits</span>
                        </Link>
                        <nav className="flex items-center gap-2 text-sm">
                            <Link
                                href="/#results"
                                className="rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100"
                            >
                                Results
                            </Link>
                            <Link
                                href="/insurance"
                                className="rounded-md bg-emerald-600 px-4 py-2 font-semibold text-white"
                            >
                                Insurance
                            </Link>
                        </nav>
                    </div>
                </header>

                <section className="bg-white">
                    <div className="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 py-10 lg:grid-cols-[1fr_380px] lg:px-6">
                        <div className="flex flex-col justify-center">
                            <p className="flex items-center gap-2 text-sm font-semibold text-emerald-700">
                                <ShieldCheck className="size-4" />
                                Insurance support for Philippine buyers
                            </p>
                            <h1 className="mt-4 max-w-3xl text-4xl leading-tight font-semibold tracking-normal md:text-5xl">
                                Get insurance-ready before you buy, renew, or
                                operate your unit.
                            </h1>
                            <p className="mt-4 max-w-2xl text-base leading-7 text-zinc-600">
                                PrimeUnits helps buyers prepare the right
                                details for car, truck, motorcycle,
                                three-wheeler, farm, and heavy equipment
                                insurance conversations.
                            </p>
                            <div className="mt-6 flex flex-wrap gap-2">
                                {insuranceTypes.map((type) => (
                                    <Link
                                        key={type}
                                        href="#quote"
                                        className="rounded-md border border-zinc-200 px-3 py-2 text-sm font-medium hover:border-emerald-600 hover:text-emerald-700"
                                    >
                                        {type}
                                    </Link>
                                ))}
                            </div>
                        </div>

                        <form
                            id="quote"
                            className="grid gap-3 rounded-md border border-zinc-200 bg-[#f8faf7] p-5 shadow-sm"
                        >
                            <h2 className="text-xl font-semibold">
                                Request insurance help
                            </h2>
                            <input
                                className="h-10 rounded-md border bg-white px-3 text-sm"
                                placeholder="Full name"
                            />
                            <input
                                className="h-10 rounded-md border bg-white px-3 text-sm"
                                placeholder="Mobile number"
                            />
                            <select className="h-10 rounded-md border bg-white px-3 text-sm">
                                {insuranceTypes.map((type) => (
                                    <option key={type}>{type}</option>
                                ))}
                            </select>
                            <input
                                className="h-10 rounded-md border bg-white px-3 text-sm"
                                placeholder="Brand / model / year"
                            />
                            <input
                                className="h-10 rounded-md border bg-white px-3 text-sm"
                                placeholder="City or province"
                            />
                            <button
                                type="button"
                                className="inline-flex h-10 items-center justify-center rounded-md bg-emerald-600 px-4 text-sm font-semibold text-white"
                            >
                                Get quote assistance
                                <ArrowRight className="ml-2 size-4" />
                            </button>
                        </form>
                    </div>
                </section>

                {companies.length > 0 && (
                    <Section title="Insurance Company Partners">
                        <div className="grid gap-3 md:grid-cols-2">
                            {companies.map((company) => (
                                <article
                                    key={company.id}
                                    className="rounded-md border border-zinc-200 bg-white p-5 shadow-sm"
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 className="font-semibold">
                                                {company.name}
                                            </h3>
                                            <p className="mt-1 text-sm text-zinc-500">
                                                {[
                                                    company.municipality,
                                                    company.province,
                                                ]
                                                    .filter(Boolean)
                                                    .join(', ')}
                                            </p>
                                        </div>
                                        <span className="flex size-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-700">
                                            <ShieldCheck className="size-5" />
                                        </span>
                                    </div>
                                    {company.description && (
                                        <p className="mt-3 text-sm leading-6 text-zinc-600">
                                            {company.description}
                                        </p>
                                    )}
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        {company.coverage_types.map(
                                            (coverageType) => (
                                                <span
                                                    key={coverageType}
                                                    className="rounded-md bg-zinc-100 px-2.5 py-1.5 text-xs font-medium text-zinc-700"
                                                >
                                                    {coverageType}
                                                </span>
                                            ),
                                        )}
                                    </div>
                                    <div className="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                                        <div className="rounded-md bg-[#f8faf7] p-3">
                                            <p className="text-xs font-medium text-zinc-500">
                                                CTPL from
                                            </p>
                                            <p className="mt-1 font-semibold text-emerald-700">
                                                {company.ctpl_price
                                                    ? `PHP ${Number(company.ctpl_price).toLocaleString()}`
                                                    : 'Request quote'}
                                            </p>
                                        </div>
                                        <div className="rounded-md bg-[#f8faf7] p-3">
                                            <p className="text-xs font-medium text-zinc-500">
                                                Premium from
                                            </p>
                                            <p className="mt-1 font-semibold text-emerald-700">
                                                {company.premium_starting_price
                                                    ? `PHP ${Number(company.premium_starting_price).toLocaleString()}`
                                                    : 'Request quote'}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="mt-4 flex flex-wrap gap-2 text-sm">
                                        <a
                                            href={`tel:${company.contact_number}`}
                                            className="rounded-md border border-zinc-200 px-3 py-2 font-medium hover:border-emerald-600 hover:text-emerald-700"
                                        >
                                            {company.contact_number}
                                        </a>
                                        {company.contact_email && (
                                            <a
                                                href={`mailto:${company.contact_email}`}
                                                className="rounded-md border border-zinc-200 px-3 py-2 font-medium hover:border-emerald-600 hover:text-emerald-700"
                                            >
                                                Email
                                            </a>
                                        )}
                                    </div>
                                </article>
                            ))}
                        </div>
                    </Section>
                )}

                <Section title="Why Choose PrimeUnits Insurance Support">
                    <div className="grid gap-3 md:grid-cols-4">
                        {benefits.map(([title, body, color, background]) => (
                            <InfoCard
                                key={title}
                                color={color}
                                iconBackground={background}
                                icon={<BadgeCheck className="size-5" />}
                                title={title}
                                body={body}
                            />
                        ))}
                    </div>
                </Section>

                <Section title="How It Works">
                    <div className="grid gap-3 md:grid-cols-3">
                        {steps.map(([title, body], index) => (
                            <InfoCard
                                key={title}
                                color={
                                    [
                                        'text-blue-600',
                                        'text-emerald-600',
                                        'text-amber-600',
                                    ][index]
                                }
                                iconBackground={
                                    [
                                        'bg-blue-50',
                                        'bg-emerald-50',
                                        'bg-amber-50',
                                    ][index]
                                }
                                icon={<ClipboardList className="size-5" />}
                                title={`${index + 1}. ${title}`}
                                body={body}
                            />
                        ))}
                    </div>
                </Section>

                <Section title="Coverage Options">
                    <div className="grid gap-3 md:grid-cols-2">
                        {coverage.map(([title, body]) => (
                            <InfoCard
                                key={title}
                                icon={<FileText className="size-5" />}
                                title={title}
                                body={body}
                                color="text-cyan-600"
                                iconBackground="bg-cyan-50"
                            />
                        ))}
                    </div>
                </Section>

                <Section title="Insurance Benefits">
                    <div className="grid gap-3 md:grid-cols-4">
                        {insuranceBenefits.map(
                            ({ title, icon: Icon, color, background }) => (
                                <div
                                    key={title}
                                    className="flex items-center gap-2 rounded-md border border-zinc-200 bg-white p-4 text-sm font-medium"
                                >
                                    <span
                                        className={`flex size-9 items-center justify-center rounded-md ${background} ${color}`}
                                    >
                                        <Icon className="size-5" />
                                    </span>
                                    {title}
                                </div>
                            ),
                        )}
                    </div>
                </Section>

                <Section title="Car Insurance FAQs">
                    <div className="grid gap-3 md:grid-cols-2">
                        {faqs.map(([question, answer]) => (
                            <article
                                key={question}
                                className="rounded-md border border-zinc-200 bg-white p-5 shadow-sm"
                            >
                                <h3 className="flex items-start gap-3 font-semibold">
                                    <span className="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                                        <CircleHelp className="size-4" />
                                    </span>
                                    {question}
                                </h3>
                                <p className="mt-2 text-sm leading-6 text-zinc-600">
                                    {answer}
                                </p>
                            </article>
                        ))}
                    </div>
                </Section>

                <section className="bg-zinc-950 text-white">
                    <div className="mx-auto grid max-w-7xl gap-5 px-4 py-8 md:grid-cols-3 lg:px-6">
                        <InfoCard
                            dark
                            icon={<Car className="size-5" />}
                            title="Vehicle owners"
                            body="Prepare your car, truck, motorcycle, or three-wheeler for CTPL and comprehensive cover."
                        />
                        <InfoCard
                            dark
                            icon={<Headphones className="size-5" />}
                            title="Dealer partners"
                            body="Offer insurance assistance alongside listings, repair referrals, and buyer support."
                        />
                        <InfoCard
                            dark
                            icon={<Truck className="size-5" />}
                            title="Equipment buyers"
                            body="Ask about protection pathways for business-use, farm, and heavy equipment units."
                        />
                    </div>
                </section>

                <Footer />
            </main>
        </>
    );
}

function Section({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="mx-auto max-w-7xl px-4 py-8 lg:px-6">
            <h2 className="mb-4 text-2xl font-semibold tracking-normal">
                {title}
            </h2>
            {children}
        </section>
    );
}

function InfoCard({
    icon,
    title,
    body,
    dark = false,
    color = 'text-emerald-700',
    iconBackground = 'bg-emerald-50',
}: {
    icon: React.ReactNode;
    title: string;
    body: string;
    dark?: boolean;
    color?: string;
    iconBackground?: string;
}) {
    return (
        <article
            className={`rounded-md border p-5 ${dark ? 'border-white/10 bg-white/5 text-white' : 'border-zinc-200 bg-white'}`}
        >
            <div
                className={`flex size-10 items-center justify-center rounded-md ${dark ? 'bg-white/10 text-emerald-300' : `${iconBackground} ${color}`}`}
            >
                {icon}
            </div>
            <h3 className="mt-4 font-semibold">{title}</h3>
            <p
                className={`mt-2 text-sm leading-6 ${dark ? 'text-white/68' : 'text-zinc-600'}`}
            >
                {body}
            </p>
        </article>
    );
}

function Footer() {
    return (
        <footer className="border-t border-zinc-800 bg-zinc-950 text-white">
            <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-6 text-sm text-white/62 md:flex-row md:items-center md:justify-between lg:px-6">
                <p>© 2026 PrimeUnits. All rights reserved.</p>
                <div className="flex flex-wrap gap-4">
                    <Link href="/about-us" className="hover:text-white">
                        About Us
                    </Link>
                    <Link href="/contact-us" className="hover:text-white">
                        Contact Us
                    </Link>
                    <Link
                        href="/terms-and-conditions"
                        className="hover:text-white"
                    >
                        Terms and Conditions
                    </Link>
                    <Link href="/privacy-policy" className="hover:text-white">
                        Privacy Policy
                    </Link>
                    <Link href="/copyrights" className="hover:text-white">
                        Copyrights
                    </Link>
                </div>
            </div>
        </footer>
    );
}
