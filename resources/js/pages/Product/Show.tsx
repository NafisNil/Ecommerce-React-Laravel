import { Product } from '@/types';
import { useForm } from '@inertiajs/react';
import React from 'react';

function Show({product, variationOptions} : {product: Product, variationOptions: number[]}) {
    console.log(product, variationOptions);
    const form = useForm<{
        option_ids : Record<string, number>;
        quantity: number;
        price: number | null;
    }>({});
    return (
        <div>
            <h1>text</h1>
        </div>
    );
}

export default Show;