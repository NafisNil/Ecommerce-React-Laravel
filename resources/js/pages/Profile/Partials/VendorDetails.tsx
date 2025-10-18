import { useForm, usePage } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

export default function VendorDetails({className = ''}: {className?: string}) {
    const [showBecomeVendor, setShowBecomeVendor] = useState(false);
    const [successMessage, setSuccessMessage] = useState('');
    const [errorMessage, setErrorMessage] = useState('');
    const user = usePage<{ auth: { user?: Record<string, any> | null } }>().props.auth?.user ?? null;
    const token = usePage<{ csrf_token: string }>().props.csrf_token;

    const {
        data, setData, post, processing, errors, recentlySuccessful
    } = useForm({
        shop_name: user?.vendor?.shop_name || '',
        shop_address: user?.vendor?.shop_address || '',
    });
    const onShopNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setData('shop_name', e.target.value.toLowerCase().replace(/\s+/g, '-'));
    }
    const becomeVendor:FormEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();
        setSuccessMessage('');
        setErrorMessage('');
        post(route('vendor.store'), {
            onSuccess: (response) => {
                setSuccessMessage('Your request to become a vendor has been submitted successfully.');
                setShowBecomeVendor(false);
            },
            onError: (errors) => {
                setErrorMessage('There was an error submitting your request. Please try again.');
            }
        });
    }

    const updateVendor:FormEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();
         setSuccessMessage('');
        setErrorMessage('');
        post(route('vendor.store'), {
            onSuccess: (response) => {
                setSuccessMessage('Your request to become a vendor has been submitted successfully.');
                setShowBecomeVendor(false);
            },
            onError: (errors) => {
                setErrorMessage('There was an error submitting your request. Please try again.');
            }
        });
    }
    return (
        <section className={className}>
            {recentlySuccessful && <div className="alert alert-success mb-4">
                {successMessage}
            </div>}

            <header>
                    <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Vendor Details
                        {user?.vendor?.status === 'pending' && (
                            <span className="ml-2 text-sm text-yellow-600">{user?.vendor?.status_label}</span>
                        )}
                    </h2>
            </header>
        </section>


    );
}