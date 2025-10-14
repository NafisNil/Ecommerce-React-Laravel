import Navbar from '@/components/App/Navbar';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

type PageProps = {
    auth: {
        user: any;
    };
    flash: {
        success?: string;
        error?: string;
    };
};

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { auth, flash } = usePage<PageProps>().props;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <Navbar />

            {header && (
                <header className="bg-white shadow dark:bg-gray-800">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}
            {flash.success && (
                <div className="bg-green-500 text-white p-4 text-center">
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div className="bg-red-500 text-white p-4 text-center">
                    {flash.error}
                </div>
            )}
            <main>{children}</main>
        </div>
    );
}
