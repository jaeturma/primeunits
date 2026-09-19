import { FormEvent } from 'react';
import { Head, useForm } from '@inertiajs/react';

type Payment = {
    id: number;
    amount: string;
    method: string;
    reference_number: string | null;
    status: string;
    proof_url: string | null;
    payable_type: string;
};

type Method = { value: string; label: string };

export default function PaymentShow({
    payment,
    methods,
}: {
    payment: Payment;
    methods: Method[];
}) {
    const { data, setData, post, processing, errors } = useForm<{
        method: string;
        reference_number: string;
        proof_file: File | null;
    }>({
        method: payment.method,
        reference_number: payment.reference_number ?? '',
        proof_file: null,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post(`/payments/${payment.id}`, {
            forceFormData: true,
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Payment" />
            <div className="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Payment
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {payment.payable_type} · PHP{' '}
                        {Number(payment.amount).toLocaleString()} ·{' '}
                        {payment.status}
                    </p>
                </div>
                <form
                    onSubmit={submit}
                    className="grid gap-4 rounded-lg border p-5"
                >
                    <label className="grid gap-2 text-sm">
                        <span className="font-medium">Method</span>
                        <select
                            value={data.method}
                            onChange={(event) =>
                                setData('method', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        >
                            {methods.map((method) => (
                                <option key={method.value} value={method.value}>
                                    {method.label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="grid gap-2 text-sm">
                        <span className="font-medium">Reference number</span>
                        <input
                            value={data.reference_number}
                            onChange={(event) =>
                                setData('reference_number', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </label>
                    <label className="grid gap-2 text-sm">
                        <span className="font-medium">Proof</span>
                        <input
                            type="file"
                            accept="image/*,application/pdf"
                            onChange={(event) =>
                                setData(
                                    'proof_file',
                                    event.target.files?.item(0) ?? null,
                                )
                            }
                            className="rounded-md border bg-background px-3 py-2"
                        />
                        {errors.proof_file && (
                            <span className="text-xs text-destructive">
                                {errors.proof_file}
                            </span>
                        )}
                    </label>
                    {payment.proof_url && (
                        <a
                            href={payment.proof_url}
                            target="_blank"
                            rel="noreferrer"
                            className="text-sm underline"
                        >
                            View current proof
                        </a>
                    )}
                    <button
                        type="submit"
                        disabled={processing || payment.status !== 'pending'}
                        className="h-10 w-fit rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        Save payment
                    </button>
                </form>
            </div>
        </>
    );
}
