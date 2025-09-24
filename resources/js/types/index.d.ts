import { Config } from 'ziggy-js';
import { id } from 'filepond/locale/id-id';
import { type } from '../../../vendor/laravel/wayfinder/resources/js/wayfinder';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type Product ={
    id: number;
    title: string;
    slug: string;
    price: number;
    quantity: string;
    image: string;
    images: Image[];
    description: string;
    short_description: string;

    user:{
        id: number;
        name: string;
    }
    department:{
        id: number;
        name: string;
    };
    variationTypes: VariationType[],
    variations: Array<{
        id: number;
        price: number;
        quantity: number;
        variation_type_id: number;

    }>[];

};

export type Image = {
    id: number;
    thumb: string;
    small: string;
    medium: string;
    large: string;
    alt_text?: string;
};

export type VariationTypeOption = {
    id: number;
    name: string;
    image: Image[];
    type:VariationType;
};

export type VariationType = {
    id: number;
    name: string;
    type : 'select' | 'radio' | 'image';
    options: VariationTypeOption[];
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
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};
