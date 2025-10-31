import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Heart } from 'lucide-react';
import { Link as LinkIcon } from 'lucide-react';
import React from 'react';
import Authenticated from '@/layouts/AuthenticatedLayout';
import Carousel from '@/components/Core/Carousel';
// Removed generic currency icon; using explicit formatter instead
import CurrencyFormatter from '@/components/Core/CurrencyFormatter';
// Removed unused imports

// Local structural types to avoid module resolution issues with '@/types'
type Image = {
    id: number;
    thumb?: string;
    small?: string;
    medium?: string;
    large?: string;
};

type VariationTypeOption = {
    id: number;
    name: string;
    images?: Image[];
};

type ProductVariation = {
    price: number;
    quantity: number | null;
    variation_type_option_ids: number[];
};

type Product = {
    id: number;
    title: string;
    description: string;
    price: number;
    quantity: number | null;
    images?: Image[];
    variationTypes?: Array<{ id: number; name: string; type: string; options?: VariationTypeOption[] }>;
    variation_types?: unknown; // allow alternate key from backend
    variations?: ProductVariation[];
    user: { name: string; shop_name?: string | null };
    department: { name: string };
    average_rating?: number;
    ratings_count?: number;
    user_rating?: number;
};

function Show({ product, variationOptions }: { product: Product; variationOptions: number[] | Record<number, number> }) {
    // console.log(product, variationOptions);
    // TEMP DEBUG (remove later): Inspect incoming product data structure
    if (typeof window !== 'undefined') {
        const w = window as unknown as { __lastLoggedProductId?: number };
        if (w.__lastLoggedProductId !== product.id) {
            console.debug('[ProductShow] product payload', {
                id: product.id,
                variationTypeCount: Array.isArray(product.variationTypes) ? product.variationTypes.length : 'not-array',
                firstVariationType: Array.isArray(product.variationTypes) ? product.variationTypes[0] : null,
                variationsCount: Array.isArray(product.variations) ? product.variations.length : 'not-array',
                imagesCount: Array.isArray(product.images) ? product.images.length : 'not-array'
            });
            w.__lastLoggedProductId = product.id;
        }
    }
    const form = useForm<{
        option_ids : Record<string, number>;
        quantity: number;
        price: number | null;
    }>({
        option_ids: {},
        quantity: 1,
        price: null,
    });
    const {url} = usePage();
    const [selectedVariation, setSelectedVariation] = React.useState<Record<number, VariationTypeOption | null>>({});
    const [activeImageIndex, setActiveImageIndex] = React.useState(0);
    const images  = React.useMemo(() => {
        for (const typeId in selectedVariation) {
            const option = selectedVariation[typeId];
            if (option && Array.isArray(option.images) && option.images.length > 0) {
                return option.images; // Image[]
            }
        }
        return Array.isArray(product.images) ? product.images : [];
    }, [product.images, selectedVariation]);

    // Reset active image index when images array identity changes
    const firstImageId = images[0]?.id;
    React.useEffect(() => {
        setActiveImageIndex(0);
    }, [images.length, firstImageId]);

    // Also reset index when a variation with its own images is selected/deselected
    const selectedVariationIdSignature = React.useMemo(() => Object.values(selectedVariation).map(v => v?.id).join('-'), [selectedVariation]);
    React.useEffect(() => {
        const hasVariationImages = Object.values(selectedVariation).some(v => v && Array.isArray(v.images) && v.images.length > 0);
        if (hasVariationImages) setActiveImageIndex(0);
    }, [selectedVariationIdSignature, selectedVariation]);

    const computedProduct = React.useMemo(() => {
        const selectedVariationIds = Object.values(selectedVariation)
            .filter((v): v is VariationTypeOption => !!v)
            .map(v => v.id)
            .sort((a, b) => a - b);

        const productVariations: ProductVariation[] = Array.isArray(product.variations) ? product.variations : [];
        for (const variation of productVariations) {
            const optionIds = Array.isArray(variation.variation_type_option_ids)
                ? [...variation.variation_type_option_ids].sort((a, b) => a - b)
                : [];
            if (selectedVariationIds.length && JSON.stringify(selectedVariationIds) === JSON.stringify(optionIds)) {
                return {
                    price: variation.price,
                    quantity: variation.quantity == null ? Number.MAX_VALUE : variation.quantity,
                };
            }
        }
        return {
            price: product.price,
            quantity: product.quantity ?? Number.MAX_VALUE,
        };
    }, [product, selectedVariation]);

    const initializedRef = React.useRef(false);
    React.useEffect(() => {
        if (initializedRef.current) return; // run only once
        const vt: Array<{ id: number; options?: VariationTypeOption[] }> = Array.isArray(product.variationTypes)
            ? (product.variationTypes as Array<{ id: number; options?: VariationTypeOption[] }>)
            : [];
        if (!vt.length) return;
        const optionsLookup: Record<number, number> = Array.isArray(variationOptions)
            ? variationOptions.reduce((acc, val, idx) => { if (val != null) acc[idx] = val; return acc; }, {} as Record<number, number>)
            : (variationOptions || {});
        let anyApplied = false;
        vt.forEach((type) => {
            const selectedOptionId = optionsLookup[type.id];
            if (selectedOptionId) {
                const found = Array.isArray(type.options)
                    ? type.options.find((o: VariationTypeOption) => o.id === selectedOptionId) || null
                    : null;
                if (found && (!selectedVariation[type.id] || selectedVariation[type.id]?.id !== found.id)) {
                    anyApplied = true;
                    setSelectedVariation(prev => ({ ...prev, [type.id]: found }));
                }
            }
        });
        if (anyApplied) initializedRef.current = true; else initializedRef.current = true; // mark done regardless
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [product.id]);


    const getOptionIdsMap = (newOptions: Record<string, VariationTypeOption | null>) =>
        Object.fromEntries(
            Object.entries(newOptions).map(([typeId, option]) => [typeId, option ? option.id : null])
        );

    const chooseOption = (typeId: number, option: VariationTypeOption | null, updateUrl = true) => {
        setSelectedVariation(prev => {
            const current = prev[typeId];
            if (current && option && current.id === option.id) return prev; // no change
            const newOptions: Record<number, VariationTypeOption | null> = { ...prev, [typeId]: option };
            if (updateUrl) {
                // Debounce via requestAnimationFrame to avoid rapid loops
                requestAnimationFrame(() => {
                    router.get(url, { ...getOptionIdsMap(newOptions as unknown as Record<string, VariationTypeOption | null>) }, {
                        preserveState: true,
                        preserveScroll: true,
                        replace: true,
                    });
                });
            }
            return newOptions;
        });
    };

        const onQuantityChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
            const value = parseInt(e.target.value);
            form.setData('quantity', isNaN(value) ? 1 : value);
        };

        const onAddToCart = () => {
            // form.setData('option_ids', getOptionIdsMap(selectedVariation));
            // form.setData('price', computedProduct.price);
           
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

    // Generic normalization helper that tolerates arrays, keyed objects, null, etc.
    const normalizeToArray = <T,>(val: unknown): T[] => {
        if (!val) return [];
        if (Array.isArray(val)) return val as T[];
        if (typeof val === 'object') {
            const obj = val as Record<string, unknown>;
            // Prefer numeric-key ordering if present
            const numericKeys = Object.keys(obj).filter(k => !isNaN(Number(k)));
            if (numericKeys.length) {
                return numericKeys.sort((a,b)=>Number(a)-Number(b)).map(k => obj[k] as T);
            }
            return Object.values(obj) as T[];
        }
        return [];
    };

    interface NormalizedVariationType { id:number; name:string; type:string; options?: VariationTypeOption[] | Record<string, VariationTypeOption>; }
    // Accept both camelCase and snake_case just in case the backend prop key differs
    type ProductLike = { variationTypes?: unknown; variation_types?: unknown } & Product;
    const pLike: ProductLike = product as ProductLike;
    const rawVariationTypes = pLike.variationTypes !== undefined ? pLike.variationTypes : pLike.variation_types;
    const variationTypesNorm = React.useMemo<NormalizedVariationType[]>(() => normalizeToArray<NormalizedVariationType>(rawVariationTypes), [rawVariationTypes]);

    const renderProductVariationOptions = (vtOverride?: NormalizedVariationType[]) => {
        const vt = vtOverride ?? variationTypesNorm;
        if (!vt.length) {
            return <div className='my-4 text-sm text-neutral-500'>No variations available for this product.</div>;
        }
        return vt.map((type) => {
            const opts = normalizeToArray<VariationTypeOption>(type.options as unknown as Record<string, VariationTypeOption> | VariationTypeOption[]);
            return (
                <div key={type.id} className='mb-3 last:mb-0'>
                    <div className='text-sm font-semibold tracking-wide text-neutral-600 mb-1 uppercase'>{type.name}</div>
                    {type.type === 'image' && !!opts.length && (
                        <div className='flex gap-2 mb-2'>
                            {opts.map(option => (
                                <div onClick={() => chooseOption(type.id, option)} key={option.id}>
                                    {option.images && option.images.length > 0 && (
                                        <img
                                            src={option.images[0].thumb || option.images[0].small || option.images[0].medium || option.images[0].large}
                                            alt={option.name}
                                            className={'w-[66px] h-[56px] object-cover rounded border transition ' + (selectedVariation[type.id]?.id === option.id ? ' ring-2 ring-blue-500 border-blue-400' : 'border-transparent hover:border-neutral-300')}
                                        />
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                    {type.type === 'radio' && !!opts.length && (
                        <div className='flex join mb-1'>
                            {opts.map(option => (
                                <input
                                    onChange={() => chooseOption(type.id, option)}
                                    key={option.id}
                                    type="radio"
                                    value={option.id}
                                    className='join-item btn '
                                    checked={selectedVariation[type.id]?.id === option.id}
                                    name={'variation_type_' + type.id}
                                    aria-label={option.name}
                                />
                            ))}
                        </div>
                    )}
                </div>
            );
        });
    };

    const renderAddToCartButton = () => {
        return(
            <div className='mb-8 flex gap-4'>
                <select name="quantity" id="quantity" value={form.data.quantity} onChange={onQuantityChange} className='select select-bordered w-full '>
                    {
                        Array.from({ length: Math.min(computedProduct.quantity ?? 0, 10) })
                            .map((_, idx) => (
                                <option key={idx + 1} value={idx + 1}>{idx + 1}</option>
                            ))
                    }
                </select>
                                <button onClick={onAddToCart} className='btn btn-primary'>Add to Cart</button>
                                <button
                                    type="button"
                                    onClick={() => router.post(route('wishlist.toggle', product.id), {}, { preserveScroll: true, preserveState: true })}
                                    className='btn btn-ghost border'
                                    aria-label="Toggle wishlist"
                                >
                                    <Heart size={18} className='mr-1'/> Wishlist
                                </button>
            </div>
        )
    }

    React.useEffect(() => {
        const idsMap = Object.fromEntries(
            Object.entries(selectedVariation)
                .filter(([, option]) => !!option)
                .map(([typeId, option]) => [typeId, (option as VariationTypeOption).id])
        );
        form.setData('option_ids', idsMap as Record<string, number>);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [JSON.stringify(Object.keys(selectedVariation)), JSON.stringify(Object.values(selectedVariation).map(v => v?.id))]);

    return (
        <Authenticated>
            <Head title={product.title}/>

            <div className="container mx-auto px-4 py-6 lg:py-8 text-[17px] leading-relaxed md:text-lg">
                <div className='grid gap-6 lg:gap-8 grid-cols-1 lg:grid-cols-12'>
                    <div className="col-span-7 flex flex-col gap-4">
                                                <Carousel
                                                    images={images}
                                                    activeIndex={activeImageIndex}
                                                    onSelect={(i) => setActiveImageIndex(i)}
                                                />
                    </div>
                    <div className="col-span-5">
                        <h1 className='text-4xl font-bold mb-4 leading-tight tracking-tight'>{product.title}</h1>
                        <p className='text-sm text-neutral-500 mb-2'>
                            by{' '}
                            {product.user.shop_name ? (
                                <Link href={route('vendor.profile', product.user.shop_name)} className='link link-hover'>
                                    {product.user.name}
                                </Link>
                            ) : (
                                <span>{product.user.name}</span>
                            )}
                            &nbsp;
                            <Link href="/" className='link link-hover'> <LinkIcon className='inline mb-1' size={16}/> {product.department.name}</Link>
                        </p>
                        {/* Rating summary and form */}
                        <div className="mb-3 flex items-center gap-3">
                            <StarDisplay value={Math.round((product.average_rating ?? 0) * 2) / 2} />
                            <span className="text-sm text-neutral-600">{(product.average_rating ?? 0).toFixed(1)} ({product.ratings_count ?? 0})</span>
                        </div>
                        <RateProduct productId={product.id} current={product.user_rating ?? 0} />
                        <div className="mb-4 pb-3 border-b border-neutral-200 flex items-end justify-between">
                            <div className="text-4xl font-semibold tracking-tight">
                                <CurrencyFormatter amount={computedProduct.price} currency="USD" />
                            </div>
                        </div>

                        {renderProductVariationOptions(variationTypesNorm)}

                        {computedProduct.quantity != undefined && computedProduct.quantity < 10 && (
                            <div className="text-error mb-4 text-sm font-medium">
                                Only {computedProduct.quantity} left in stock
                            </div>
                        )}

                        {renderAddToCartButton()}

                        <strong className='block text-xl font-semibold mt-4 mb-3'>About this product</strong>

                        <div className="wysiwyg-output text-[17px] md:text-lg leading-relaxed"
                        dangerouslySetInnerHTML={{__html: product.description}}
                        />
                    </div>
                </div>
            </div>
        </Authenticated>  
    );
}

export default Show;

function StarDisplay({ value }: { value: number }) {
    const stars = [1, 2, 3, 4, 5];
    return (
        <div className="rating rating-sm">
            {stars.map((i) => {
                const filled = value >= i - 0.25;
                return (
                    <input key={i} type="radio" className={`mask mask-star-2 ${filled ? 'bg-yellow-400' : 'bg-gray-300'}`} readOnly />
                );
            })}
        </div>
    );
}

function RateProduct({ productId, current }: { productId: number; current: number }) {
    const [rating, setRating] = React.useState<number>(current || 0);
    const [submitting, setSubmitting] = React.useState(false);
    const onClick = (v: number) => setRating(v);
    const onSubmit = () => {
        if (!rating) return;
        setSubmitting(true);
        router.post(route('products.rate', productId), { rating }, {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => setSubmitting(false),
        });
    };

    return (
        <div className="mb-4">
            <div className="rating rating-md mr-3 inline-flex align-middle">
                {[1,2,3,4,5].map((i) => (
                    <input
                        key={i}
                        type="radio"
                        name="rating"
                        className={`mask mask-star-2 ${i <= rating ? 'bg-amber-400' : 'bg-gray-300'}`}
                        onClick={() => onClick(i)}
                    />
                ))}
            </div>
            <button className="btn btn-sm btn-outline" disabled={!rating || submitting} onClick={onSubmit}>
                {submitting ? 'Submitting…' : (current ? 'Update Rating' : 'Rate this product')}
            </button>
        </div>
    );
}