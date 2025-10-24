import { useForm, usePage } from "@inertiajs/react";
import { Link } from "lucide-react";
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
                        {user?.vendor?.status === 'approved' && (
                            <span className="ml-2 text-sm text-green-600">{user?.vendor?.status_label}</span>
                        )}
                        {user?.vendor?.status === 'rejected' && (
                            <span className="ml-2 text-sm text-red-600">{user?.vendor?.status_label}</span>
                        )}
                    </h2>
            </header>
            <div className="mt-4">
                {!user?.vendor && (
                    <div className="alert alert-warning">
                        You are not a vendor yet. Please apply to become a vendor.
                        <button className="btn btn-primary ml-4" onClick={() => setShowBecomeVendor(true)}>Apply Now</button>
                    </div>
                )}
                {user?.vendor && (
                    <div className="alert alert-info">
                        You are already a vendor. Your shop name is: <strong>{user.vendor.shop_name}</strong>
                        <button className="btn btn-secondary ml-4" onClick={() => setShowBecomeVendor(true)}>Update Details</button>
                    </div>
                )}
            </div>

            {showBecomeVendor && (
                <form onSubmit={user?.vendor ? updateVendor : becomeVendor} className="mt-4 space-y-4">
                    {errorMessage && <div className="alert alert-error">{errorMessage}</div>}
                    {successMessage && <div className="alert alert-success">{successMessage}</div>}
                    <div className="form-control">
                        <label className="label">
                            <span className="label-text">Shop Name</span>
                        </label>
                        <input
                            type="text"
                            name="shop_name"
                            value={data.shop_name}
                            onChange={onShopNameChange}
                            className={`input input-bordered ${errors.shop_name ? 'input-error' : ''}`}
                            required
                        />
                        {errors.shop_name && <span className="text-error text-sm">{errors.shop_name}</span>}
                    </div>
                    <div className="form-control">
                        <label className="label">
                            <span className="label-text">Shop Address</span>
                        </label>
                        <input
                            type="text"
                            name="shop_address"
                            value={data.shop_address}
                            onChange={(e) => setData('shop_address', e.target.value)}
                            className={`input input-bordered ${errors.shop_address ? 'input-error' : ''}`}
                            required
                        />
                        {errors.shop_address && <span className="text-error text-sm">{errors.shop_address}</span>}
                    </div>
                    <input type="hidden" name="_token" value={token} />
                    <button type="submit" className="btn btn-primary w-full" disabled={processing}>
                        {user?.vendor ? 'Update Vendor Details' : 'Apply to Become a Vendor'}
                    </button>
                </form>
            )}
            {/* <header className="mt-6">
                <div className="text-sm text-gray-500 dark:text-gray-400">
                    Please ensure your shop details are accurate and complete. This information will be used to set up your vendor account.
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    If you have any questions or need assistance, please contact our support team.
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Thank you for your interest in becoming a vendor on our platform!
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.index')} className="text-blue-600 hover:underline">View Vendor Dashboard</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.products.index')} className="text-blue-600 hover:underline">View Your Products</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.orders.index')} className="text-blue-600 hover:underline">View Your Orders</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.settings.index')} className="text-blue-600 hover:underline">Vendor Settings</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.support.index')} className="text-blue-600 hover:underline">Vendor Support</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.terms.index')} className="text-blue-600 hover:underline">Vendor Terms and Conditions</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.privacy.index')} className="text-blue-600 hover:underline">Vendor Privacy Policy</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.faq.index')} className="text-blue-600 hover:underline">Vendor FAQ</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.contact.index')} className="text-blue-600 hover:underline">Contact Vendor Support</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.resources.index')} className="text-blue-600 hover:underline">Vendor Resources</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.community.index')} className="text-blue-600 hover:underline">Vendor Community</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.feedback.index')} className="text-blue-600 hover:underline">Vendor Feedback</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.analytics.index')} className="text-blue-600 hover:underline">Vendor Analytics</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.notifications.index')} className="text-blue-600 hover:underline">Vendor Notifications</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.billing.index')} className="text-blue-600 hover:underline">Vendor Billing</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.support-tickets.index')} className="text-blue-600 hover:underline">Vendor Support Tickets</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.referrals.index')} className="text-blue-600 hover:underline">Vendor Referrals</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.promotions.index')} className="text-blue-600 hover:underline">Vendor Promotions</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.events.index')} className="text-blue-600 hover:underline">Vendor Events</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.partners.index')} className="text-blue-600 hover:underline">Vendor Partners</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.integrations.index')} className="text-blue-600 hover:underline">Vendor Integrations</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.api.index')} className="text-blue-600 hover:underline">Vendor API</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.documentation.index')} className="text-blue-600 hover:underline">Vendor Documentation</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.blog.index')} className="text-blue-600 hover:underline">Vendor Blog</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.newsletter.index')} className="text-blue-600 hover:underline">Vendor Newsletter</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.social-media.index')} className="text-blue-600 hover:underline">Vendor Social Media</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.webinars.index')} className="text-blue-600 hover:underline">Vendor Webinars</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.training.index')} className="text-blue-600 hover:underline">Vendor Training</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.certification.index')} className="text-blue-600 hover:underline">Vendor Certification</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.affiliates.index')} className="text-blue-600 hover:underline">Vendor Affiliates</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.community-forums.index')} className="text-blue-600 hover:underline">Vendor Community Forums</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.user-guides.index')} className="text-blue-600 hover:underline">Vendor User Guides</Link>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <Link href={route('vendor.case-studies.index')} className="text-blue-600 hover:underline">Vendor Case Studies</Link>
                </div>      
              </div>  
            </header> */}
        </section>


    );
}