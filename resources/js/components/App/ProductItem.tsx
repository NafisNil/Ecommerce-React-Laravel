// Local minimal Product shape to avoid coupling to ambient '@/types'
type ProductLite = {
    id: number;
    title: string;
    slug: string;
    image: string | null;
    price: number;
    is_offered?: boolean;
    offered_price?: number | null;
    user: { name: string; shop_name?: string | null };
    department: { name: string; slug: string };
};
import { Link, useForm } from '@inertiajs/react';
import { Link as LinkIcon } from 'lucide-react';
import React from 'react';
import CurrencyFormatter from '../Core/CurrencyFormatter';

const ProductItem = ({product} : {product: ProductLite}) => {

    const form = useForm<{
        option_ids : Record<string, number>;
        quantity: number;
    }>({
        option_ids: {},
        quantity: 1,
    });

    const addToCart = () => {
        form.post(route('cart.store', product.id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            },
            onError: () => {
                console.error('Failed to add product to cart');
            }
        });
    }
    return (
        
        <div className='card bg-base-100 shadow-xl'>
                <Link href={route('products.show', product.slug)}>
                    <figure>
                        <img
                            src={product.image ?? undefined}
                            alt={product.title}
                            className='h-48 w-full object-cover'
                            onError={(e) => {
                                e.currentTarget.onerror = null;
                                e.currentTarget.src = '/favicon.svg';
                            }}
                        />
                    </figure>
                </Link>
                <div className='card-body'>
                    <h2 className='card-title'>{product.title}</h2>
                    <p>
                        by{' '}
                        {product.user.shop_name ? (
                            <Link href={route('vendor.profile', product.user.shop_name)} className='link link-hover'>
                                {product.user.name}
                            </Link>
                        ) : (
                            <span>{product.user.name}</span>
                        )}
                        &nbsp;
                        <Link href={route('departments.products', product.department.slug)} className='link link-hover'> <LinkIcon className='inline mb-1' size={16}/> {product.department.name}</Link>
                    </p>
                    <div className='card-actions items-center justify-between mt-4'>
                        <button onClick={addToCart} className='btn btn-primary'>Add to Cart</button>
                        <p className='text-lg font-bold'>
                            {product.is_offered && product.offered_price != null ? (
                                <span>
                                    <span className='text-error mr-2'>
                                        <CurrencyFormatter amount={Number(product.offered_price)} />
                                    </span>
                                    <span className='line-through text-sm text-gray-500'>
                                        <CurrencyFormatter amount={product.price} />
                                    </span>
                                </span>
                            ) : (
                                <CurrencyFormatter amount={product.price} />
                            )}
                        </p>
                    </div>
                </div>
        </div>
    );
};

export default ProductItem;