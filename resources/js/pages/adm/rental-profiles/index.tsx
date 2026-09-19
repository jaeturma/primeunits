import { Head, router } from '@inertiajs/react';

type Profile = { id: number; business_name: string; status: string; user: { name: string; email: string }; business_registration_url: string; valid_id_url: string };

export default function RentalProfiles({ profiles }: { profiles: { data: Profile[] } }) {
    return <><Head title="Rental Owner Applications" /><div className="p-4"><h1 className="text-2xl font-semibold">Rental Owner Applications</h1><div className="mt-6 grid gap-3">{profiles.data.map(profile => <div key={profile.id} className="flex items-center justify-between rounded-lg border p-4"><div><p className="font-medium">{profile.business_name}</p><p className="text-sm text-muted-foreground">{profile.user.name} · {profile.user.email} · {profile.status.replace('_', ' ')}</p><div className="flex gap-3 text-xs"><a className="text-primary underline" href={profile.business_registration_url} target="_blank" rel="noreferrer">Business registration</a><a className="text-primary underline" href={profile.valid_id_url} target="_blank" rel="noreferrer">Owner ID</a></div></div><div className="flex gap-2">{!['approved', 'rejected'].includes(profile.status) && <button onClick={() => router.post(`/adm/rental-profiles/${profile.id}/approve`)} className="rounded bg-primary px-3 py-2 text-sm text-primary-foreground">Advance approval</button>}<button onClick={() => { const reason = prompt('Rejection reason'); if (reason) router.post(`/adm/rental-profiles/${profile.id}/reject`, { reason }); }} className="rounded border px-3 py-2 text-sm">Reject</button></div></div>)}</div></div></>;
}

RentalProfiles.layout = { breadcrumbs: [{ title: 'Rental Owner Applications', href: '/adm/rental-profiles' }] };
