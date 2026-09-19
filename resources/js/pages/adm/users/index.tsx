import { Head, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import type { Role } from '@/types';

type UserListItem = {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
    roles: Role[];
};

export default function UsersIndex({ users }: { users: UserListItem[] }) {
    const { auth } = usePage().props;

    if (!auth.permissions.includes('manage_users')) {
        return (
            <>
                <Head title="Users" />
                <div className="p-4">
                    <div className="rounded-lg border p-5">
                        <h1 className="font-semibold">Users</h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            You do not have permission to view this page.
                        </p>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Users" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Users
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Review accounts and their assigned RBAC roles.
                    </p>
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="px-4 py-3 font-medium">Name</th>
                                <th className="px-4 py-3 font-medium">Email</th>
                                <th className="px-4 py-3 font-medium">Roles</th>
                                <th className="px-4 py-3 font-medium">
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr key={user.id} className="border-t">
                                    <td className="px-4 py-3 font-medium">
                                        {user.name}
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {user.email}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex flex-wrap gap-2">
                                            {user.roles.length > 0 ? (
                                                user.roles.map((role) => (
                                                    <Badge
                                                        key={role.id}
                                                        variant="secondary"
                                                    >
                                                        {role.label}
                                                    </Badge>
                                                ))
                                            ) : (
                                                <span className="text-muted-foreground">
                                                    None
                                                </span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {user.created_at
                                            ? new Date(
                                                  user.created_at,
                                              ).toLocaleDateString()
                                            : ''}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Users',
            href: '/adm/users',
        },
    ],
};
