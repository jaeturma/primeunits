import { FormEvent } from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function DealerApply() {
    const { data, setData, post, processing, errors } = useForm({
        business_name: '',
        contact_number: '',
        email: '',
        website: '',
        description: '',
        region: '',
        province: '',
        municipality: '',
        barangay: '',
        full_address: '',
        accreditation_number: '',
        logo: null as File | null,
        banner: null as File | null,
        accreditation_file: null as File | null,
        attachments: [] as File[],
    });

    function submit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post('/dealer/apply', { forceFormData: true });
    }

    return (
        <>
            <Head title="Apply as Dealer" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Apply as Dealer
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Submit your dealership information for verification. Our
                        team will review your application within 2-3 business
                        days.
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Dealership Info</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <TextField
                                label="Business Name"
                                value={data.business_name}
                                error={errors.business_name}
                                required
                                onChange={(v) => setData('business_name', v)}
                            />
                            <TextField
                                label="Contact Number"
                                value={data.contact_number}
                                error={errors.contact_number}
                                required
                                onChange={(v) => setData('contact_number', v)}
                            />
                            <TextField
                                label="Email"
                                value={data.email}
                                error={errors.email}
                                type="email"
                                onChange={(v) => setData('email', v)}
                            />
                            <TextField
                                label="Website"
                                value={data.website}
                                error={errors.website}
                                type="url"
                                onChange={(v) => setData('website', v)}
                            />
                            <TextField
                                label="Accreditation Number"
                                value={data.accreditation_number}
                                error={errors.accreditation_number}
                                onChange={(v) =>
                                    setData('accreditation_number', v)
                                }
                            />
                        </div>
                        <div className="mt-4">
                            <label className="grid gap-2 text-sm">
                                <span className="font-medium">Description</span>
                                <textarea
                                    value={data.description}
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                    className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                    placeholder="Tell buyers about your dealership..."
                                />
                                {errors.description && (
                                    <span className="text-xs text-destructive">
                                        {errors.description}
                                    </span>
                                )}
                            </label>
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Location</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <TextField
                                label="Region"
                                value={data.region}
                                error={errors.region}
                                onChange={(v) => setData('region', v)}
                            />
                            <TextField
                                label="Province"
                                value={data.province}
                                error={errors.province}
                                onChange={(v) => setData('province', v)}
                            />
                            <TextField
                                label="City/Municipality"
                                value={data.municipality}
                                error={errors.municipality}
                                onChange={(v) => setData('municipality', v)}
                            />
                            <TextField
                                label="Barangay"
                                value={data.barangay}
                                error={errors.barangay}
                                onChange={(v) => setData('barangay', v)}
                            />
                        </div>
                        <div className="mt-4">
                            <label className="grid gap-2 text-sm">
                                <span className="font-medium">
                                    Full Address
                                </span>
                                <textarea
                                    value={data.full_address}
                                    onChange={(e) =>
                                        setData('full_address', e.target.value)
                                    }
                                    className="min-h-20 rounded-md border bg-background px-3 py-2 text-sm"
                                />
                                {errors.full_address && (
                                    <span className="text-xs text-destructive">
                                        {errors.full_address}
                                    </span>
                                )}
                            </label>
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Branding & Documents</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <FileField
                                label="Dealer Logo (JPG/PNG, max 2MB)"
                                error={errors.logo}
                                onChange={(f) => setData('logo', f)}
                                accept="image/jpeg,image/png"
                            />
                            <FileField
                                label="Banner Image (JPG/PNG, max 5MB)"
                                error={errors.banner}
                                onChange={(f) => setData('banner', f)}
                                accept="image/jpeg,image/png"
                            />
                            <FileField
                                label="Accreditation Document (JPG/PDF, max 10MB)"
                                error={errors.accreditation_file}
                                onChange={(f) =>
                                    setData('accreditation_file', f)
                                }
                                accept="image/jpeg,image/png,application/pdf"
                            />
                        </div>
                        <div className="mt-5 grid gap-2 text-sm">
                            <span className="font-medium">PDF attachments</span>
                            <input
                                type="file"
                                multiple
                                accept="application/pdf,.pdf"
                                onChange={(e) =>
                                    setData(
                                        'attachments',
                                        Array.from(e.target.files ?? []),
                                    )
                                }
                                className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                            />
                            <p className="text-xs text-muted-foreground">
                                Delivery receipts, property transfer receipts,
                                and supporting PDFs. Max 5MB per file.
                            </p>
                            {errors.attachments && (
                                <span className="text-xs text-destructive">
                                    {errors.attachments}
                                </span>
                            )}
                        </div>
                    </section>

                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex h-10 items-center rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {processing ? 'Submitting...' : 'Submit Application'}
                    </button>
                </form>
            </div>
        </>
    );
}

function TextField({
    label,
    value,
    error,
    type = 'text',
    required = false,
    onChange,
}: {
    label: string;
    value: string;
    error?: string;
    type?: string;
    required?: boolean;
    onChange: (v: string) => void;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">
                {label}
                {required && <span className="text-destructive"> *</span>}
            </span>
            <input
                type={type}
                value={value}
                required={required}
                onChange={(e) => onChange(e.target.value)}
                className="h-10 rounded-md border bg-background px-3 text-sm"
            />
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

function FileField({
    label,
    error,
    accept,
    onChange,
}: {
    label: string;
    error?: string;
    accept: string;
    onChange: (f: File | null) => void;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">{label}</span>
            <input
                type="file"
                accept={accept}
                onChange={(e) => onChange(e.target.files?.[0] ?? null)}
                className="rounded-md border bg-background px-3 py-2 text-sm"
            />
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

DealerApply.layout = {
    breadcrumbs: [{ title: 'Apply as Dealer', href: '/dealer/apply' }],
};
