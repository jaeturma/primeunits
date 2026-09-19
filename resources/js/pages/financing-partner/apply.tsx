import { FormEvent } from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function FinancingPartnerApply() {
    const { data, setData, post, processing, errors } = useForm({
        company_name: '',
        registration_number: '',
        license_number: '',
        license_file: null as File | null,
        description: '',
        website: '',
        contact_number: '',
        contact_email: '',
        logo: null as File | null,
        banner: null as File | null,
        region: '',
        province: '',
        municipality: '',
        full_address: '',
        attachments: [] as File[],
    });

    function submit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post('/financing-partner/apply', { forceFormData: true });
    }

    return (
        <>
            <Head title="Become a Financing Partner" />
            <div className="mx-auto w-full max-w-2xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Become a Financing Partner
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Join PrimeUnits as an accredited financing provider.
                        Your application will be reviewed by our team.
                    </p>
                </div>

                <form onSubmit={submit} className="grid gap-6">
                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Company Information</h2>
                        <Field
                            label="Company Name"
                            required
                            error={errors.company_name}
                        >
                            <input
                                type="text"
                                value={data.company_name}
                                onChange={(e) =>
                                    setData('company_name', e.target.value)
                                }
                                required
                                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                            />
                        </Field>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="SEC / DTI Registration No."
                                error={errors.registration_number}
                            >
                                <input
                                    type="text"
                                    value={data.registration_number}
                                    onChange={(e) =>
                                        setData(
                                            'registration_number',
                                            e.target.value,
                                        )
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="BSP / Lending License No."
                                error={errors.license_number}
                            >
                                <input
                                    type="text"
                                    value={data.license_number}
                                    onChange={(e) =>
                                        setData(
                                            'license_number',
                                            e.target.value,
                                        )
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                        </div>
                        <Field
                            label="License / Accreditation File (PDF/Image)"
                            error={errors.license_file as string}
                        >
                            <input
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={(e) =>
                                    setData(
                                        'license_file',
                                        e.target.files?.[0] ?? null,
                                    )
                                }
                                className="text-sm file:mr-3 file:rounded-md file:border file:px-3 file:py-1.5 file:text-sm file:font-medium"
                            />
                        </Field>
                        <Field
                            label="Company Description"
                            error={errors.description}
                        >
                            <textarea
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                className="min-h-24 w-full rounded-md border bg-background px-3 py-2 text-sm"
                                placeholder="Describe your company and services..."
                            />
                        </Field>
                        <Field label="Website" error={errors.website}>
                            <input
                                type="url"
                                value={data.website}
                                onChange={(e) =>
                                    setData('website', e.target.value)
                                }
                                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                placeholder="https://..."
                            />
                        </Field>
                    </section>

                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Contact Information</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Contact Number"
                                required
                                error={errors.contact_number}
                            >
                                <input
                                    type="tel"
                                    value={data.contact_number}
                                    onChange={(e) =>
                                        setData(
                                            'contact_number',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Contact Email"
                                error={errors.contact_email}
                            >
                                <input
                                    type="email"
                                    value={data.contact_email}
                                    onChange={(e) =>
                                        setData('contact_email', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Region" error={errors.region}>
                                <input
                                    type="text"
                                    value={data.region}
                                    onChange={(e) =>
                                        setData('region', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field label="Province" error={errors.province}>
                                <input
                                    type="text"
                                    value={data.province}
                                    onChange={(e) =>
                                        setData('province', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="City / Municipality"
                                error={errors.municipality}
                            >
                                <input
                                    type="text"
                                    value={data.municipality}
                                    onChange={(e) =>
                                        setData('municipality', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                        </div>
                        <Field label="Full Address" error={errors.full_address}>
                            <textarea
                                value={data.full_address}
                                onChange={(e) =>
                                    setData('full_address', e.target.value)
                                }
                                className="min-h-16 w-full rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </Field>
                    </section>

                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Branding</h2>
                        <Field
                            label="Logo (JPG/PNG, max 2MB)"
                            error={errors.logo as string}
                        >
                            <input
                                type="file"
                                accept="image/jpg,image/jpeg,image/png"
                                onChange={(e) =>
                                    setData('logo', e.target.files?.[0] ?? null)
                                }
                                className="text-sm file:mr-3 file:rounded-md file:border file:px-3 file:py-1.5 file:text-sm file:font-medium"
                            />
                        </Field>
                        <Field
                            label="Banner (JPG/PNG, max 5MB)"
                            error={errors.banner as string}
                        >
                            <input
                                type="file"
                                accept="image/jpg,image/jpeg,image/png"
                                onChange={(e) =>
                                    setData(
                                        'banner',
                                        e.target.files?.[0] ?? null,
                                    )
                                }
                                className="text-sm file:mr-3 file:rounded-md file:border file:px-3 file:py-1.5 file:text-sm file:font-medium"
                            />
                        </Field>
                        <Field
                            label="PDF Attachments"
                            error={errors.attachments as string}
                        >
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
                            <span className="text-xs text-muted-foreground">
                                Delivery receipts, transfer receipts, and
                                supporting PDFs. Max 5MB per file.
                            </span>
                        </Field>
                    </section>

                    <button
                        type="submit"
                        disabled={processing}
                        className="h-10 rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {processing ? 'Submitting...' : 'Submit Application'}
                    </button>
                </form>
            </div>
        </>
    );
}

function Field({
    label,
    required,
    error,
    children,
}: {
    label: string;
    required?: boolean;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">
                {label}
                {required && <span className="ml-1 text-destructive">*</span>}
            </span>
            {children}
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}
