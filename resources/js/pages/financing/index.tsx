import { Head, Link, router } from '@inertiajs/react';
import { PublicFooter } from '@/components/public-footer';
import { PublicHeader } from '@/components/public-header';
import { Banknote, Calculator, Car, FileText, ShieldCheck } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';

type Partner = {
    id: number;
    slug: string;
    company_name: string;
    contact_number: string;
    region: string | null;
    province: string | null;
    municipality: string | null;
    logo_url: string | null;
    products_count: number;
};

type PaginatedPartners = {
    data: Partner[];
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function FinancingIndex({
    partners,
    filters,
}: {
    partners: PaginatedPartners;
    filters: { q: string; region: string; type: string };
}) {
    const [q, setQ] = useState(filters.q);
    const [loanAmount, setLoanAmount] = useState(850000);
    const [interestRate, setInterestRate] = useState(1.25);
    const [loanMonths, setLoanMonths] = useState(48);

    function search(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        router.get(
            '/financing',
            { q, type: filters.type },
            { preserveState: true },
        );
    }

    const monthlyPayment = useMemo(() => {
        const monthlyRate = interestRate / 100;
        const factor = Math.pow(1 + monthlyRate, loanMonths);

        if (monthlyRate === 0) {
            return loanAmount / loanMonths;
        }

        return (loanAmount * monthlyRate * factor) / (factor - 1);
    }, [interestRate, loanAmount, loanMonths]);

    const totalAmount = monthlyPayment * loanMonths;
    const interestAmount = totalAmount - loanAmount;
    const selectedType =
        financingTypes.find((type) => type.value === filters.type) ??
        financingTypes[1];

    return (
        <>
            <Head title="PrimeUnits Financing" />
            <PublicHeader />
            <main className="bg-[#f4f5f2] text-zinc-950">
                <section className="border-b border-zinc-200 bg-white">
                    <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 lg:grid-cols-[1fr_420px] lg:px-6">
                        <div className="flex flex-col justify-center">
                            <p className="text-sm font-semibold text-emerald-700">
                                Auto loans made easier
                            </p>
                            <h1 className="mt-3 text-4xl font-semibold tracking-normal md:text-5xl">
                                Financing for brand new, used, and Sangla OR/CR
                                vehicle needs.
                            </h1>
                            <p className="mt-4 max-w-2xl text-base leading-7 text-zinc-600">
                                Compare verified financing partners, estimate
                                monthly payments, and prepare the right
                                documents before you apply.
                            </p>
                            <div className="mt-6 flex flex-wrap gap-2">
                                {financingTypes.map((type) => (
                                    <Link
                                        key={type.value}
                                        href={`/financing?type=${type.value}`}
                                        className={`rounded-md border px-4 py-2 text-sm font-semibold ${
                                            selectedType.value === type.value
                                                ? 'border-emerald-600 bg-emerald-600 text-white'
                                                : 'border-zinc-300 bg-white text-zinc-700 hover:border-emerald-600'
                                        }`}
                                    >
                                        {type.label}
                                    </Link>
                                ))}
                            </div>
                        </div>
                        <div className="rounded-md border border-zinc-200 bg-zinc-950 p-6 text-white shadow-sm">
                            <div className="rounded-md bg-white/10 p-4">
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-emerald-200">
                                        {selectedType.label}
                                    </p>
                                    <span className="flex size-11 items-center justify-center rounded-md bg-emerald-500 text-white">
                                        <Car className="size-6" />
                                    </span>
                                </div>
                                <h2 className="mt-4 text-2xl font-semibold">
                                    {selectedType.title}
                                </h2>
                                <p className="mt-3 text-sm leading-6 text-white/70">
                                    {selectedType.body}
                                </p>
                            </div>
                            <div className="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                                <div className="rounded-md bg-white/10 p-3">
                                    <p className="text-lg font-semibold">10+</p>
                                    <p className="text-white/60">Partners</p>
                                </div>
                                <div className="rounded-md bg-white/10 p-3">
                                    <p className="text-lg font-semibold">
                                        12-60
                                    </p>
                                    <p className="text-white/60">Months</p>
                                </div>
                                <div className="rounded-md bg-white/10 p-3">
                                    <p className="text-lg font-semibold">
                                        Fast
                                    </p>
                                    <p className="text-white/60">Review</p>
                                </div>
                            </div>
                            <Link
                                href="/buyer/financing-applications/apply"
                                className="mt-4 inline-flex h-10 w-full items-center justify-center rounded-md bg-emerald-500 px-4 text-sm font-semibold text-white hover:bg-emerald-400"
                            >
                                Apply now
                            </Link>
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-4 py-8 lg:px-6">
                    <div className="grid gap-4 md:grid-cols-3">
                        {benefits.map((benefit) => (
                            <article
                                key={benefit.title}
                                className="rounded-md border border-zinc-200 bg-white p-5 shadow-sm"
                            >
                                <benefit.icon className="size-6 text-emerald-700" />
                                <h2 className="mt-4 font-semibold">
                                    {benefit.title}
                                </h2>
                                <p className="mt-2 text-sm leading-6 text-zinc-600">
                                    {benefit.body}
                                </p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[1fr_420px] lg:px-6">
                    <div>
                        <h2 className="text-xl font-semibold">
                            Why choose PrimeUnits financing?
                        </h2>
                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            {whyChoose.map((item) => (
                                <article
                                    key={item.title}
                                    className="rounded-md border border-zinc-200 bg-white p-5"
                                >
                                    <h3 className="font-semibold">
                                        {item.title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-6 text-zinc-600">
                                        {item.body}
                                    </p>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-md border border-zinc-200 bg-white p-5 shadow-sm">
                        <div className="flex items-center gap-2">
                            <Calculator className="size-5 text-emerald-700" />
                            <h2 className="font-semibold">
                                Calculate your monthly payment
                            </h2>
                        </div>
                        <FinanceInput
                            label="Loan amount"
                            value={loanAmount}
                            onChange={setLoanAmount}
                            prefix="PHP"
                        />
                        <FinanceInput
                            label="Monthly interest"
                            value={interestRate}
                            onChange={setInterestRate}
                            suffix="%"
                            step={0.05}
                        />
                        <FinanceInput
                            label="Loan term"
                            value={loanMonths}
                            onChange={setLoanMonths}
                            suffix="months"
                            step={1}
                        />
                        <div className="mt-5 rounded-md bg-zinc-950 p-4 text-white">
                            <p className="text-sm text-white/65">
                                Estimated monthly payment
                            </p>
                            <p className="mt-1 text-2xl font-semibold">
                                PHP{' '}
                                {Math.round(monthlyPayment).toLocaleString()}
                            </p>
                            <div className="mt-4 grid grid-cols-2 gap-3 text-xs text-white/65">
                                <span>
                                    Interest: PHP{' '}
                                    {Math.round(
                                        interestAmount,
                                    ).toLocaleString()}
                                </span>
                                <span>
                                    Total: PHP{' '}
                                    {Math.round(totalAmount).toLocaleString()}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="border-y border-zinc-200 bg-white">
                    <div className="mx-auto max-w-7xl px-4 py-8 lg:px-6">
                        <h2 className="text-xl font-semibold">
                            How application works
                        </h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-5">
                            {steps.map((step, index) => (
                                <article
                                    key={step.title}
                                    className="rounded-md border border-zinc-200 p-4"
                                >
                                    <span className="flex size-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                                        {index + 1}
                                    </span>
                                    <h3 className="mt-4 text-sm font-semibold">
                                        {step.title}
                                    </h3>
                                    <p className="mt-2 text-xs leading-5 text-zinc-600">
                                        {step.body}
                                    </p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-2 lg:px-6">
                    <div className="rounded-md border border-zinc-200 bg-white p-5">
                        <h2 className="text-xl font-semibold">
                            Required documents
                        </h2>
                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            {documentGroups.map((group) => (
                                <div key={group.title}>
                                    <h3 className="text-sm font-semibold">
                                        {group.title}
                                    </h3>
                                    <ul className="mt-2 space-y-1 text-sm text-zinc-600">
                                        {group.items.map((item) => (
                                            <li key={item}>• {item}</li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="rounded-md border border-zinc-200 bg-white p-5">
                        <h2 className="text-xl font-semibold">
                            Have questions?
                        </h2>
                        <div className="mt-4 space-y-4">
                            {faqs.map((faq) => (
                                <div key={faq.question}>
                                    <h3 className="text-sm font-semibold">
                                        {faq.question}
                                    </h3>
                                    <p className="mt-1 text-sm leading-6 text-zinc-600">
                                        {faq.answer}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="border-t border-zinc-200 bg-white">
                    <div className="mx-auto max-w-7xl px-4 py-8 lg:px-6">
                        <div className="mb-6">
                            <h2 className="text-xl font-semibold">
                                Browse financing partners
                            </h2>
                            <p className="mt-1 text-sm text-zinc-600">
                                Find accredited financing companies, banks, and
                                credit providers for your vehicle purchase.
                            </p>
                        </div>

                        <form onSubmit={search} className="mb-6 flex gap-3">
                            <input
                                type="text"
                                value={q}
                                onChange={(e) => setQ(e.target.value)}
                                placeholder="Search financing partners..."
                                className="h-10 flex-1 rounded-md border border-zinc-300 bg-white px-3 text-sm"
                            />
                            <button
                                type="submit"
                                className="h-10 rounded-md bg-emerald-600 px-4 text-sm font-semibold text-white"
                            >
                                Search
                            </button>
                        </form>

                        <p className="mb-4 text-sm text-zinc-600">
                            {partners.total} partner
                            {partners.total !== 1 ? 's' : ''} available
                        </p>

                        {partners.data.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-12 text-center">
                                <p className="text-sm text-muted-foreground">
                                    No financing partners found.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    {partners.data.map((partner) => (
                                        <Link
                                            key={partner.id}
                                            href={`/financing/${partner.slug}`}
                                            className="group flex gap-4 rounded-lg border bg-card p-4 transition-shadow hover:shadow-md"
                                        >
                                            <div className="h-14 w-14 shrink-0 overflow-hidden rounded-full border bg-muted">
                                                {partner.logo_url ? (
                                                    <img
                                                        src={partner.logo_url}
                                                        alt={
                                                            partner.company_name
                                                        }
                                                        className="h-full w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="flex h-full w-full items-center justify-center text-base font-bold text-muted-foreground">
                                                        {partner.company_name.charAt(
                                                            0,
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <h3 className="leading-tight font-medium group-hover:text-primary">
                                                    {partner.company_name}
                                                </h3>
                                                {partner.municipality && (
                                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                                        {[
                                                            partner.municipality,
                                                            partner.region,
                                                        ]
                                                            .filter(Boolean)
                                                            .join(', ')}
                                                    </p>
                                                )}
                                                <p className="mt-2 text-xs font-medium text-primary">
                                                    {partner.products_count}{' '}
                                                    product
                                                    {partner.products_count !==
                                                    1
                                                        ? 's'
                                                        : ''}{' '}
                                                    available
                                                </p>
                                            </div>
                                        </Link>
                                    ))}
                                </div>

                                {partners.links &&
                                    partners.links.length > 3 && (
                                        <div className="mt-8 flex justify-center gap-2">
                                            {partners.links.map((link, i) =>
                                                link.url ? (
                                                    <Link
                                                        key={i}
                                                        href={link.url}
                                                        className={`rounded-md border px-3 py-1.5 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                                                        dangerouslySetInnerHTML={{
                                                            __html: link.label,
                                                        }}
                                                    />
                                                ) : (
                                                    <span
                                                        key={i}
                                                        className="rounded-md border px-3 py-1.5 text-sm text-muted-foreground"
                                                        dangerouslySetInnerHTML={{
                                                            __html: link.label,
                                                        }}
                                                    />
                                                ),
                                            )}
                                        </div>
                                    )}
                            </>
                        )}
                    </div>
                </section>
            </main>
            <PublicFooter />
        </>
    );
}

function FinanceInput({
    label,
    value,
    onChange,
    prefix,
    suffix,
    step = 10000,
}: {
    label: string;
    value: number;
    onChange: (value: number) => void;
    prefix?: string;
    suffix?: string;
    step?: number;
}) {
    return (
        <label className="mt-4 block text-sm">
            <span className="font-medium">{label}</span>
            <div className="mt-2 flex items-center rounded-md border border-zinc-300 bg-white">
                {prefix && (
                    <span className="px-3 text-xs text-zinc-500">{prefix}</span>
                )}
                <input
                    type="number"
                    value={value}
                    step={step}
                    min={0}
                    onChange={(event) => onChange(Number(event.target.value))}
                    className="h-10 min-w-0 flex-1 bg-transparent px-3 text-sm outline-none"
                />
                {suffix && (
                    <span className="px-3 text-xs text-zinc-500">{suffix}</span>
                )}
            </div>
        </label>
    );
}

const financingTypes = [
    {
        value: 'brand-new',
        label: 'Brand New',
        title: 'Plan a brand-new vehicle purchase with guided loan options.',
        body: 'Compare terms for new units, estimate monthly amortization, and prepare requirements before visiting a dealer.',
    },
    {
        value: 'used-cars',
        label: 'Used Cars',
        title: 'Used vehicle financing for buyer-ready listings.',
        body: 'Get matched with partner lenders for pre-owned cars, trucks, motorcycles, and equipment listed on PrimeUnits.',
    },
    {
        value: 'sangla-or-cr',
        label: 'Sangla OR/CR',
        title: 'Short-term funding using vehicle OR/CR evaluation.',
        body: 'Connect with partners that can assess your vehicle documents, ownership status, and repayment capacity.',
    },
];

const benefits = [
    {
        title: 'Trusted partner choices',
        body: 'Work with verified banks, credit providers, and financing companies.',
        icon: ShieldCheck,
    },
    {
        title: 'Flexible payment plans',
        body: 'Compare loan amounts, payment terms, and monthly estimates before applying.',
        icon: Banknote,
    },
    {
        title: 'Quick application flow',
        body: 'Prepare borrower, vehicle, and income documents from one guided page.',
        icon: FileText,
    },
];

const whyChoose = [
    {
        title: 'Assisted loan processing',
        body: 'Our workflow keeps the buyer, seller, and financing partner aligned from inquiry to approval.',
    },
    {
        title: 'More options to compare',
        body: 'Multiple product types help buyers find a fit for brand-new, used, or OR/CR-backed needs.',
    },
    {
        title: 'Clear document checklist',
        body: 'Borrowers can prepare IDs, income proof, billing proof, and vehicle details before submission.',
    },
    {
        title: 'Marketplace-ready context',
        body: 'Financing can start from real PrimeUnits listings, making unit details easier to verify.',
    },
];

const steps = [
    {
        title: 'Choose a unit',
        body: 'Start from a marketplace listing or identify the unit you want to finance.',
    },
    {
        title: 'Estimate payments',
        body: 'Use the calculator to check monthly payment comfort before applying.',
    },
    {
        title: 'Submit documents',
        body: 'Provide borrower details, income proof, billing proof, and vehicle information.',
    },
    {
        title: 'Partner review',
        body: 'A financing partner reviews eligibility, terms, and required follow-up items.',
    },
    {
        title: 'Release and records',
        body: 'Finalize documents, payment terms, and vehicle release or OR/CR processing.',
    },
];

const documentGroups = [
    {
        title: 'Borrower',
        items: [
            'Two government-issued IDs',
            'Proof of billing',
            'TIN and SSS/GSIS details',
        ],
    },
    {
        title: 'Income proof',
        items: [
            'COE, payslips, or ITR',
            'Business permits or bank statements',
            'OFW contract or remittance proof',
        ],
    },
    {
        title: 'Vehicle details',
        items: [
            'Year, brand, and model',
            'Selling price and down payment',
            'OR/CR or sales documents when applicable',
        ],
    },
    {
        title: 'Co-borrower',
        items: [
            'Immediate family relationship',
            'Valid IDs and proof of billing',
            'SPA when signing abroad',
        ],
    },
];

const faqs = [
    {
        question: 'Who can apply?',
        answer: 'Applicants are commonly Filipino residents from 21 to 65 years old with verifiable income or a qualified co-borrower.',
    },
    {
        question: 'How much down payment is needed?',
        answer: 'Many lenders require 20% to 30%, but exact terms depend on the unit, lender, and borrower profile.',
    },
    {
        question: 'What terms are available?',
        answer: 'Used vehicle terms often range from 12 to 60 months, while brand-new units may qualify for longer terms depending on partner policy.',
    },
];
