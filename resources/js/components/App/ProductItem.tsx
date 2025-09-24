import { Product } from '@/types';
import { Link } from '@inertiajs/react';
import { Link as LinkIcon } from 'lucide-react';
import React from 'react';
import CurrencyFormatter from '../Core/CurrencyFormatter';

const ProductItem = ({product} : {product: Product}) => {
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
                        <button className='btn btn-primary'>Add to Cart</button>
                        <p className='text-lg font-bold'>
                            <CurrencyFormatter amount={product.price} />
                        </p>
                    </div>
                </div>
        </div>
    );
};

export default ProductItem;