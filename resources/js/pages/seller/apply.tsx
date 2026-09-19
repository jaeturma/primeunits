import { FormEvent, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

type SellerProfile = {
    seller_type: string;
    business_name: string | null;
    owner_name: string | null;
    contact_number: string;
    email: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    barangay: string | null;
    full_address: string | null;
    permit_number: string | null;
    accreditation: string | null;
    representative_name: string | null;
    representative_contact: string | null;
    status: string;
    status_label: string;
    rejected_reason: string | null;
    can_edit: boolean;
};

type SellerTypeOption = {
    value: string;
    label: string;
};

type SellerFormData = {
    seller_type: string;
    business_name: string;
    owner_name: string;
    contact_number: string;
    email: string;
    region: string;
    province: string;
    municipality: string;
    barangay: string;
    full_address: string;
    permit_number: string;
    permit_file: File | null;
    accreditation: string;
    accreditation_file: File | null;
    representative_name: string;
    representative_contact: string;
    representative_id_file: File | null;
    valid_id_file: File | null;
    selfie_file: File | null;
    attachments: File[];
};

const fileFields = [
    'permit_file',
    'accreditation_file',
    'representative_id_file',
    'valid_id_file',
    'selfie_file',
] as const;

export default function SellerApply({
    profile,
    sellerTypes,
}: {
    profile: SellerProfile | null;
    sellerTypes: SellerTypeOption[];
}) {
    const [previews, setPreviews] = useState<Record<string, string>>({});
    const { data, setData, transform, post, processing, errors } =
        useForm<SellerFormData>({
            seller_type: profile?.seller_type ?? 'individual',
            business_name: profile?.business_name ?? '',
            owner_name: profile?.owner_name ?? '',
            contact_number: profile?.contact_number ?? '',
            email: profile?.email ?? '',
            region: profile?.region ?? '',
            province: profile?.province ?? '',
            municipality: profile?.municipality ?? '',
            barangay: profile?.barangay ?? '',
            full_address: profile?.full_address ?? '',
            permit_number: profile?.permit_number ?? '',
            permit_file: null,
            accreditation: profile?.accreditation ?? '',
            accreditation_file: null,
            representative_name: profile?.representative_name ?? '',
            representative_contact: profile?.representative_contact ?? '',
            representative_id_file: null,
            valid_id_file: null,
            selfie_file: null,
            attachments: [],
        });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (profile?.can_edit) {
            transform((current) => ({ ...current, _method: 'put' }));
            post('/seller/profile', {
                forceFormData: true,
            });

            return;
        }

        post('/seller/apply', { forceFormData: true });
    }

    function handleFile(field: (typeof fileFields)[number], file: File | null) {
        setData(field, file);

        if (file && file.type.startsWith('image/')) {
            setPreviews((current) => ({
                ...current,
                [field]: URL.createObjectURL(file),
            }));
        }
    }

    return (
        <>
            <Head title="Seller Application" />

            <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Seller Application
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Submit your seller details for admin verification.
                    </p>
                </div>

                {profile?.rejected_reason && (
                    <div className="rounded-lg border border-destructive/40 p-4 text-sm">
                        <p className="font-medium">{profile.status_label}</p>
                        <p className="mt-1 text-muted-foreground">
                            {profile.rejected_reason}
                        </p>
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Basic Info</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <Field
                                label="Seller type"
                                error={errors.seller_type}
                            >
                                <select
                                    value={data.seller_type}
                                    onChange={(event) =>
                                        setData(
                                            'seller_type',
                                            event.target.value,
                                        )
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    {sellerTypes.map((type) => (
                                        <option
                                            key={type.value}
                                            value={type.value}
                                        >
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <TextField
                                label="Contact number"
                                value={data.contact_number}
                                error={errors.contact_number}
                                required
                                onChange={(value) =>
                                    setData('contact_number', value)
                                }
                            />
                            <TextField
                                label="Business name"
                                value={data.business_name}
                                error={errors.business_name}
                                onChange={(value) =>
                                    setData('business_name', value)
                                }
                            />
                            <TextField
                                label="Owner name"
                                value={data.owner_name}
                                error={errors.owner_name}
                                onChange={(value) =>
                                    setData('owner_name', value)
                                }
                            />
                            <TextField
                                label="Email"
                                type="email"
                                value={data.email}
                                error={errors.email}
                                onChange={(value) => setData('email', value)}
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Address</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <TextField
                                label="Region"
                                value={data.region}
                                error={errors.region}
                                onChange={(value) => setData('region', value)}
                            />
                            <TextField
                                label="Province"
                                value={data.province}
                                error={errors.province}
                                onChange={(value) => setData('province', value)}
                            />
                            <TextField
                                label="Municipality"
                                value={data.municipality}
                                error={errors.municipality}
                                onChange={(value) =>
                                    setData('municipality', value)
                                }
                            />
                            <TextField
                                label="Barangay"
                                value={data.barangay}
                                error={errors.barangay}
                                onChange={(value) => setData('barangay', value)}
                            />
                            <TextAreaField
                                label="Full address"
                                value={data.full_address}
                                error={errors.full_address}
                                onChange={(value) =>
                                    setData('full_address', value)
                                }
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Business Info</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <TextField
                                label="Permit number"
                                value={data.permit_number}
                                error={errors.permit_number}
                                onChange={(value) =>
                                    setData('permit_number', value)
                                }
                            />
                            <TextField
                                label="Accreditation"
                                value={data.accreditation}
                                error={errors.accreditation}
                                onChange={(value) =>
                                    setData('accreditation', value)
                                }
                            />
                            <FileField
                                label="Upload permit file"
                                error={errors.permit_file}
                                preview={previews.permit_file}
                                onChange={(file) =>
                                    handleFile('permit_file', file)
                                }
                            />
                            <FileField
                                label="Upload accreditation file"
                                error={errors.accreditation_file}
                                preview={previews.accreditation_file}
                                onChange={(file) =>
                                    handleFile('accreditation_file', file)
                                }
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Representative</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <TextField
                                label="Name"
                                value={data.representative_name}
                                error={errors.representative_name}
                                onChange={(value) =>
                                    setData('representative_name', value)
                                }
                            />
                            <TextField
                                label="Contact"
                                value={data.representative_contact}
                                error={errors.representative_contact}
                                onChange={(value) =>
                                    setData('representative_contact', value)
                                }
                            />
                            <FileField
                                label="Upload ID"
                                error={errors.representative_id_file}
                                preview={previews.representative_id_file}
                                onChange={(file) =>
                                    handleFile('representative_id_file', file)
                                }
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Verification</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <FileField
                                label="Upload valid ID"
                                error={errors.valid_id_file}
                                preview={previews.valid_id_file}
                                onChange={(file) =>
                                    handleFile('valid_id_file', file)
                                }
                            />
                            <FileField
                                label="Upload selfie"
                                accept="image/*"
                                error={errors.selfie_file}
                                preview={previews.selfie_file}
                                onChange={(file) =>
                                    handleFile('selfie_file', file)
                                }
                            />
                        </div>
                        <div className="mt-5 grid gap-2 text-sm">
                            <span className="font-medium">PDF attachments</span>
                            <input
                                type="file"
                                multiple
                                accept="application/pdf,.pdf"
                                onChange={(event) =>
                                    setData(
                                        'attachments',
                                        Array.from(event.target.files ?? []),
                                    )
                                }
                                className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                            />
                            <p className="text-xs text-muted-foreground">
                                Delivery receipts, property transfer receipts,
                                and other supporting PDFs. Max 5MB per file.
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
                        className="inline-flex h-10 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {processing ? 'Submitting...' : 'Submit application'}
                    </button>
                </form>
            </div>
        </>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">{label}</span>
            {children}
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
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
    onChange: (value: string) => void;
}) {
    return (
        <Field label={label} error={error}>
            <input
                type={type}
                value={value}
                required={required}
                onChange={(event) => onChange(event.target.value)}
                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
            />
        </Field>
    );
}

function TextAreaField({
    label,
    value,
    error,
    onChange,
}: {
    label: string;
    value: string;
    error?: string;
    onChange: (value: string) => void;
}) {
    return (
        <Field label={label} error={error}>
            <textarea
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="min-h-24 w-full rounded-md border bg-background px-3 py-2 text-sm"
            />
        </Field>
    );
}

function FileField({
    label,
    error,
    preview,
    accept = 'image/*,application/pdf',
    onChange,
}: {
    label: string;
    error?: string;
    preview?: string;
    accept?: string;
    onChange: (file: File | null) => void;
}) {
    return (
        <Field label={label} error={error}>
            <input
                type="file"
                accept={accept}
                onChange={(event) =>
                    onChange(event.target.files?.item(0) ?? null)
                }
                className="w-full rounded-md border bg-background px-3 py-2 text-sm"
            />
            {preview && (
                <img
                    src={preview}
                    alt=""
                    className="mt-2 h-24 w-24 rounded-md border object-cover"
                />
            )}
        </Field>
    );
}

SellerApply.layout = {
    breadcrumbs: [
        {
            title: 'Seller Application',
            href: '/seller/apply',
        },
    ],
};
