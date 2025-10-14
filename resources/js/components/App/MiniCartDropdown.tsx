import { usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import React from 'react';
import { buildVariationQuery } from '@/helper';

function MiniCartDropdown() {
const { cart_items, cart_total_quantity, cart_total_price } = usePage().props;
return (
<div className="dropdown dropdown-end">
    <div tabIndex={0} role="button" className="btn btn-ghost btn-circle">
        <div className="indicator">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span className="badge badge-sm indicator-item">{cart_total_quantity}</span>
        </div>
    </div>
    <div tabIndex={0} className="card card-compact dropdown-content bg-base-100 z-1 mt-3 w-64 shadow">
        <div className="card-body">
            <span className="text-lg font-bold">{cart_total_quantity} Items</span>
            <div className="my-4 max-h-[200px] overflow-y-auto">
                {cart_items.length === 0 && <div className="text-center text-gray-500">Your cart is empty</div>}
                {cart_items.map(item => (
                    <div key={item.id} className="flex items-center justify-between py-2 border-b">
                        <Link href={`${route('products.show', item.slug)}${buildVariationQuery(item.option_ids)}`} className="font-semibold">
                            <img src={item.image} alt={item.title} className="w-12 h-12 object-cover mr-2" />
                        </Link>
                        <span>{item.title}</span>
                        <span>{item.quantity}</span> &nbsp; x &nbsp;
                        <span>${item.price }</span>
                    </div>
                ))}
            </div>
            <span className="text-info">Subtotal: ${cart_total_price}</span>
            <div className="card-actions">
                <Link href={route('cart.index')} className="btn btn-primary btn-block">View cart</Link>
            </div>
        </div>
    </div>
</div>
);
}

export default MiniCartDropdown;
