import { Product } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { Link as LinkIcon } from 'lucide-react';
import React from 'react';
import CurrencyFormatter from '../Core/CurrencyFormatter';

const ProductItem = ({product} : {product: Product}) => {

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
                            src={product.image}
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
                    <p> by 
                        <Link href="/" className='link link-hover'> {product.user.name}</Link> &nbsp;
                        <Link href="/" className='link link-hover'> <LinkIcon className='inline mb-1' size={16}/> {product.department.name}</Link>
                    </p>
                    <div className='card-actions items-center justify-between mt-4'>
                        <button onClick={addToCart} className='btn btn-primary'>Add to Cart</button>
                        <p className='text-lg font-bold'>
                            <CurrencyFormatter amount={product.price} />
                        </p>
                    </div>
                </div>
        </div>
    );
};

export default ProductItem;