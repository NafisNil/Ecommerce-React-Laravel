import React from 'react';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { CheckCircleIcon } from 'lucide-react';
import CurrencyFormatter from '@/components/Core/CurrencyFormatter';

type OrderItemRes = {
    id: number;
    quantity: number;
    price: number;
    variation_type_option_ids?: number[] | string | null;
    product: {
        id: number;
        title: string;
        slug: string;
        description: string;
        image: string | null;
    };
};

type VendorUserRes = {
    id?: number;
    store_name?: string;
    shop_name?: string;
    name?: string;
};

type OrderRes = {
    id: number;
    order_number?: string;
    status: string;
    total_amount?: number;
    total_price?: number;
    created_at: string;
    vendorUser?: VendorUserRes | null;
    orderItems: OrderItemRes[];
};

function Success({ orders }: { orders: OrderRes[] }) {
    const list = Array.isArray(orders) ? orders : [];
    return (
        <Authenticated>
            <Head title="Order Success" />
            <div className="w-[480px] mx-auto py-4 px-8">
                <div className="flex flex-col gap-2 items-center">
                    <div className="text-6xl text-green-500">
                        <CheckCircleIcon className={'size-24'} />
                    </div>
                    <div className="text-3xl">
                        Thank you for your order!
                    </div>
                    {list.length === 0 && (
                        <div className="text-center text-gray-600 mt-4">
                            We couldn't find your order details yet. If you just completed checkout, please wait a moment and refresh.
                            <div className='mt-3'>
                                <Link href='/' className='btn btn-secondary btn-sm'>Back To Shop</Link>
                            </div>
                        </div>
                    )}
                    {list.map((order: OrderRes) => {
                        const items: OrderItemRes[] = Array.isArray(order.orderItems) ? order.orderItems : [];
                        const firstItem = items[0];
                        const sellerName = order.vendorUser?.shop_name || order.vendorUser?.store_name || order.vendorUser?.name || 'Unknown Seller';
                        const total = order.total_amount ?? order.total_price ?? items.reduce((acc: number, it: OrderItemRes) => acc + (it.price ?? 0) * (it.quantity ?? 0), 0);
                        return (
                            <div key={order.id} className="border-b py-2 w-full">
                                <div className="text-lg font-semibold">{firstItem?.product?.title ?? `Order #${order.id}`}</div>
                                <div className="text-sm text-gray-500">Order ID: {order.id}</div>
                                <div className="text-sm text-gray-500">Seller: {' '}
                                    <Link href="#" className="hover:underline">{sellerName}</Link>
                                </div>
                                <div className="text-sm text-gray-500 mt-2">
                                    <h4 className="font-semibold">Order Summary</h4>
                                    <div className="mt-2">
                                        <Link href="#" className="hover:underline">#{order.order_number ?? order.id}</Link>
                                    </div>
                                    <div className="flex justify-between mb-3 mt-2">
                                        <span className="font-semibold">Items:</span>
                                        <span>{items.length}</span>
                                    </div>
                                    <div className="flex justify-between mb-3">
                                        <span className="font-semibold">Total:</span>
                                        <span><CurrencyFormatter amount={total} /></span>
                                    </div>
                                    {items.length > 0 && (
                                        <div className="mt-3 space-y-2">
                                            {items.map((it) => (
                                                <div key={it.id} className="flex justify-between text-sm">
                                                    <span className="truncate mr-2">{it.product?.title ?? 'Item'}</span>
                                                    <span className="text-gray-500">x{it.quantity}</span>
                                                    <span className="ml-3"><CurrencyFormatter amount={(it.price ?? 0) * (it.quantity ?? 0)} /></span>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                                <div className='flex justify-between mb-4'>
                                    <Link href='#' className='btn btn-primary btn-sm'>View Order Details</Link>
                                    <Link href='/' className='btn btn-secondary btn-sm'>Back To Shop</Link>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </Authenticated>
    );
}

export default Success;