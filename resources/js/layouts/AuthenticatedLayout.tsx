import Navbar from '@/components/App/Navbar';
import Footer from '@/components/App/Footer';
import { usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';

type PageProps = {
    auth: { user: unknown };
    flash: { success?: string; error?: string };
};

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { flash } = usePage<PageProps>().props;

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
            <Footer />
        </div>
    );
}
