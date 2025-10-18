import { Config } from 'ziggy-js';
import { id } from 'filepond/locale/id-id';
import { type } from '../../../vendor/laravel/wayfinder/resources/js/wayfinder';
import { store } from '../routes/login/index';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    vendor?: {
        id: number;
        status: string;
        status_label: string;
        shop_name: string;
        shop_address: string;
        cover_image: string | null;
    } | null;
}

export type ProductVariation = {
    id: number;
    price: number;
    quantity: number | null;
    variation_type_option_ids: number[]; // array of option ids composing this variation
    sku?: string;
};

export type Product = {
    id: number;
    title: string;
    slug: string;
    price: number;
    quantity: number | null;
    image: string | null;
    images: Image[];
    description: string;
    short_description?: string;
    user: {
        id: number;
        name: string;
    };
    department: {
        id: number;
        name: string;
    };
    variationTypes: VariationType[];
    variations: ProductVariation[]; // flat array
};

export type Image = {
    id: number;
    thumb: string;
    small: string;
    medium: string;
    large: string;
    original?: string;
    alt_text?: string;
};

export type VariationTypeOption = {
    id: number;
    name: string;
    images: Image[]; // plural to align with API (ProductResource returns 'images')
};

export type VariationType = {
    id: number;
    name: string;
    type : 'select' | 'radio' | 'image';
    options: VariationTypeOption[];
};

export type CartItem = {
    id: number;
    product_id: number;
    title: string;
    slug: string;
    image: string | null;
    quantity: number;
    price: number;
    option_ids: Record<string, number | null>; // key-value pairs of variation_type_id and option_id
    options: VariationTypeOption[]; // array of selected options
};

export type GroupedCartItems = {
    items: CartItem[];
    user: User | null;
    total_price: number;
    total_quantity: number;
};

export type PaginationProps<T> = {
    data: Array<T>;
    per_page: number;
    current_page: number;
    last_page: number;
    data: T[];
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    csrf_token: string;
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
    cart_total_quantity: number;
    cart_total_price: number;
    cart_items: CartItem[];
};

export type OrderItem = {
    id: number;
    quantity: number;
    price: number;
    variation_type_option_ids: number[]; // array of option ids composing this variation
    product:{
        id: number;
        title: string;
        slug: string;
        description: string | null;
        image : string | null;
    }
};

export type Order ={
    id: number;
    total_price: number;
    status:string
    created_at:string
    vendorUser: {
        id: number;
        name: string;
        email: string;
        shop_name: string | null;
        shop_address: string | null;

    };
    items: OrderItem[];
}
