import React from 'react';
import type { GroupedCartItems, PageProps, CartItem as CartItemType, User } from '@/types';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Link, Head, router } from '@inertiajs/react';
import CurrencyFormatter from '@/components/Core/CurrencyFormatter';
import { buildVariationQuery } from '@/helper';
import PrimaryButton from '@/components/PrimaryButton';
import { CreditCardIcon } from '@heroicons/react/24/outline';
// no runtime import needed for CartItem type

function Index({
    csrf_token,
    cart_items,
    cart_total_price,
    cart_total_quantity,
}: PageProps<{ cart_items: Record<number, GroupedCartItems> | CartItemType[] }>) {
    // Normalize cart_items to an array of GroupedCartItems
    const grouped: GroupedCartItems[] = React.useMemo(() => {
        if (!cart_items) return [];
        if (Array.isArray(cart_items)) {
            // Group flat items by user id (vendor)
            type FlatItem = CartItemType & { user?: User | null };
            const groups: Record<string, { user: User | null; items: CartItemType[]; total_price: number; total_quantity: number; }> = {};
            (cart_items as FlatItem[]).forEach((item) => {
                const userId = String(item.user?.id ?? 'guest');
                if (!groups[userId]) {
                    groups[userId] = {
                        user: (item.user as User | null) ?? null,
                        items: [],
                        total_price: 0,
                        total_quantity: 0,
                    };
                }
                groups[userId].items.push(item);
                groups[userId].total_price += (item.price ?? 0) * (item.quantity ?? 0);
                groups[userId].total_quantity += (item.quantity ?? 0);
            });
            return Object.values(groups);
        }
        // It is a record keyed by vendor id already
        return Object.values((cart_items as Record<number, GroupedCartItems>) || {}).filter(g => Array.isArray(g.items));
    }, [cart_items]);

    return (
        <Authenticated>
            <Head title="Cart" />
            <div className="container mx-auto p-4 flex flex-col md:flex-row gap-4">
                <div className="flex-1 card bg-white dark:bg-gray-800 shadow-md p-4 order-2 lg:order-1">
                    <div className="card-body">
                        <h2 className="text-2xl font-semibold mb-4">Shopping Cart</h2>
                        <div className="my-4">
                            {grouped.length === 0 && (
                               <div className="text-center text-gray-500">
                                      Your cart is empty.
                               </div>
                            )}

                            {grouped.map((cartItem, groupIdx) => (
                                <div key={(cartItem.items[0]?.id ?? groupIdx) + '-group'} className="border-b border-gray-200 dark:border-gray-700 py-4">
                                    <div className="flex items-center justify-between pb-4 bottom-b border-gray-200 dark:border-gray-700 mb-4">
                                        <Link href='/' className='underline'>
                                        {(cartItem.user?.name) || 'Guest User'} ( {cartItem.total_quantity} items ) - <CurrencyFormatter amount={cartItem.total_price} />
                                        </Link>
                                        <div>
                                            <form action={route('cart.checkout')} method='get'>
                                                <input type="hidden" name='vendor_id' value={cartItem.user?.id ?? ''} />
                                                <button className='btn btn-sm btn-ghost'>
                                                    <CreditCardIcon className='h-5 w-5' />
                                                    Pay Only for this Vendor
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    {cartItem.items.map((item: CartItemType) => {
                                        const optionEntries = (Object.entries(item.option_ids || {}) as Array<[string, number | null]>).filter((entry): entry is [string, number] => entry[1] != null);
                                        const optionMap = Object.fromEntries(optionEntries);
                                        const onUpdateQuantity = (q: number) => {
                                            router.put(route('cart.update', item.product_id), { quantity: q, option_ids: optionMap }, {
                                                preserveState: true,
                                                preserveScroll: true,
                                            });
                                        };
                                        const onRemove = () => {
                                            router.delete(route('cart.destroy', item.product_id), {
                                                data: { option_ids: optionMap },
                                                preserveState: true,
                                                preserveScroll: true,
                                            });
                                        };
                                        return (
                                            <div key={item.id} className="flex items-center justify-between gap-3 py-2">
                                                <Link href={`${route('products.show', item.slug)}${buildVariationQuery(item.option_ids)}`} className="flex items-center gap-3">
                                                    <img src={item.image || '/favicon.svg'} alt={item.title} className="w-14 h-14 object-cover rounded" />
                                                    <span className="font-medium">{item.title}</span>
                                                </Link>
                                                <div className="ml-auto flex items-center gap-3 text-sm">
                                                    <div className="flex items-center gap-2">
                                                        <select
                                                            name="quantity"
                                                            defaultValue={item.quantity}
                                                            className="select select-sm select-bordered"
                                                            onChange={(e) => onUpdateQuantity(parseInt(e.target.value, 10) || 1)}
                                                        >
                                                            {Array.from({ length: 10 }).map((_, i) => (
                                                                <option key={i+1} value={i+1}>{i+1}</option>
                                                            ))}
                                                        </select>
                                                        <button className="btn btn-sm" onClick={() => onUpdateQuantity(item.quantity)} type="button">Update</button>
                                                    </div>
                                                    <span className="text-neutral-400">×</span>
                                                    <span className="font-medium"><CurrencyFormatter amount={item.price} /></span>
                                                    <span className="text-neutral-400">=</span>
                                                    <span className="font-semibold"><CurrencyFormatter amount={(item.price ?? 0) * (item.quantity ?? 0)} /></span>
                                                    <button className="btn btn-sm btn-ghost text-error ml-3" type="button" onClick={onRemove}>Remove</button>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="flex-1 card bg-white dark:bg-gray-800 shadow-md p-4  lg:min-w-[300px] order-1 lg:order-2">
                    <div className="card-body">
                            Subtotal ({cart_total_quantity} items): <span className="font-bold"><CurrencyFormatter amount={cart_total_price} /></span>
                            <form action={route('cart.checkout')} method='get'>
                                <PrimaryButton className='rounded-full'>
                                    <CreditCardIcon className='h-5 w-5'/>
                                    Proceed to Checkout
                                </PrimaryButton>
                            </form>
                            <form action={route('cart.clear')} method='post' className='mt-4'>
                                <input type="hidden" name="_method" value="DELETE" />
                                <input type="hidden" name="_token" value={csrf_token} />
                                <button className='btn btn-outline btn-error w-full' type='submit'>Clear Cart</button>
                            </form>
                    </div>
                </div>
            </div>
          </Authenticated>  
    );
}

export default Index;