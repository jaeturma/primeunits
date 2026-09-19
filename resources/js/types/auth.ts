export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Role = {
    id: number;
    name: string;
    label: string;
};

export type Permission = {
    id: number;
    name: string;
    label: string;
};

export type Auth = {
    user: User;
    roles: string[];
    permissions: string[];
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
