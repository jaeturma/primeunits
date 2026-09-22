import { Link, usePage } from '@inertiajs/react';
import {
    Bell,
    BookOpen,
    FolderGit2,
    Handshake,
    LayoutGrid,
    ListChecks,
    MapPin,
    Megaphone,
    Store,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth, notificationSummary } = usePage().props;
    const canUseAdmin = auth.roles.some((role) =>
        [
            'superadmin',
            'admin',
            'manager',
            'coordinator',
            'insurance_manager',
        ].includes(role),
    );
    const isSeller = auth.roles.includes('seller');
    const isBuyer = auth.roles.includes('buyer');
    const isDealer = auth.roles.includes('dealer');
    const isRentalProvider = auth.roles.includes('rental_provider');
    const can = (permission: string) => auth.permissions.includes(permission);

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: notificationSummary.unread_count
                ? `Notifications (${notificationSummary.unread_count})`
                : 'Notifications',
            href: '/notifications',
            icon: Bell,
        },
        ...(canUseAdmin
            ? [
                  {
                      title: 'Admin Dashboard',
                      href: '/adm/dashboard',
                      icon: LayoutGrid,
                  },
                  ...(can('verify_sellers')
                      ? [
                            {
                                title: 'Seller Applications',
                                href: '/adm/sellers',
                                icon: Store,
                            },
                        ]
                      : []),
                  ...(can('approve_listings')
                      ? [
                            {
                                title: 'Listing Review',
                                href: '/adm/listings',
                                icon: ListChecks,
                            },
                        ]
                      : []),
                  ...(can('view_reports')
                      ? [
                            {
                                title: 'Transactions',
                                href: '/adm/transactions',
                                icon: Handshake,
                            },
                        ]
                      : []),
                  ...(can('manage_roles')
                      ? [
                            {
                                title: 'Plans',
                                href: '/adm/plans',
                                icon: Store,
                            },
                        ]
                      : []),
                  ...(can('view_reports')
                      ? [
                            {
                                title: 'Payments',
                                href: '/adm/payments',
                                icon: Handshake,
                            },
                            {
                                title: 'Promotions',
                                href: '/adm/promotions',
                                icon: Megaphone,
                            },
                        ]
                      : []),
                  ...(can('manage_users')
                      ? [
                            {
                                title: 'Users',
                                href: '/adm/users',
                                icon: Users,
                            },
                        ]
                      : []),
                  ...(can('manage_landing')
                      ? [
                            {
                                title: 'Landing Page',
                                href: '/adm/landing',
                                icon: LayoutGrid,
                            },
                            {
                                title: 'Landing Ads',
                                href: '/adm/landing-ads',
                                icon: Store,
                            },
                        ]
                      : []),
                  ...(can('manage_locations')
                      ? [
                            {
                                title: 'Locations',
                                href: '/adm/locations',
                                icon: MapPin,
                            },
                        ]
                      : []),
                  ...(can('manage_catalog')
                      ? [
                            {
                                title: 'Categories',
                                href: '/adm/categories',
                                icon: ListChecks,
                            },
                            {
                                title: 'Brands',
                                href: '/adm/brands',
                                icon: Store,
                            },
                        ]
                      : []),
                  ...(can('manage_dealers')
                      ? [
                            {
                                title: 'Dealer Applications',
                                href: '/adm/dealers',
                                icon: Store,
                            },
                        ]
                      : []),
                  ...(can('manage_rentals')
                      ? [
                            {
                                title: 'Rental Unit Review',
                                href: '/adm/rentals',
                                icon: ListChecks,
                            },
                            {
                                title: 'Rental Owner Applications',
                                href: '/adm/rental-profiles',
                                icon: Store,
                            },
                        ]
                      : []),
              ]
            : []),
        ...(isSeller
            ? [
                  {
                      title: 'Seller Status',
                      href: '/seller/status',
                      icon: Store,
                  },
                  {
                      title: 'My Listings',
                      href: '/seller/listings',
                      icon: ListChecks,
                  },
                  {
                      title: 'Seller Leads',
                      href: '/seller/leads',
                      icon: Handshake,
                  },
                  {
                      title: 'Monetization',
                      href: '/seller/monetization',
                      icon: Store,
                  },
              ]
            : []),
        ...(isBuyer
            ? [
                  {
                      title: 'My Inquiries',
                      href: '/buyer/leads',
                      icon: Handshake,
                  },
              ]
            : []),
        ...(isDealer
            ? [
                  {
                      title: 'Dealer Status',
                      href: '/dealer/status',
                      icon: Store,
                  },
                  {
                      title: 'Dealer Units',
                      href: '/dealer/units',
                      icon: ListChecks,
                  },
              ]
            : []),
        ...(isRentalProvider
            ? [
                  {
                      title: 'Business Verification',
                      href: '/rental-provider/profile',
                      icon: Store,
                  },
                  {
                      title: 'Rental Units',
                      href: '/rental-provider/units',
                      icon: Store,
                  },
                  {
                      title: 'Rental Bookings',
                      href: '/rental-provider/bookings',
                      icon: Handshake,
                  },
              ]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
