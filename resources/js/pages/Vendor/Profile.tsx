import ProductItem from '@/components/App/ProductItem';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

type ProductLite = {
	id: number;
	title: string;
	slug: string;
	image: string | null;
	price: number;
	user: { name: string; shop_name?: string | null };
	department: { name: string; slug: string };
};

type Pagination<T> = {
	data: T[];
};

type VendorLite = { cover_image?: string | null; cover_image_url?: string | null; shop_name?: string };

export default function Profile({
	products,
	vendor,
}: { vendor?: VendorLite | null; products: Pagination<ProductLite> }) {
	const coverUrl = vendor?.cover_image_url ?? (vendor?.cover_image ? `/storage/${vendor.cover_image}` : null);
	return (
		<Authenticated>
			<Head title="Welcome" />
			<div className="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
				<div
					className={`hero h-[30vh] md:h-[40vh] ${coverUrl ? 'bg-cover bg-center' : 'bg-zinc-200'}`}
					style={coverUrl ? { backgroundImage: `url(${coverUrl})` } : undefined}
				>
					<div className="hero-content text-center">
						<div className="max-w-md bg-slate-800 opacity-50 p-4" >
							<h1 className="text-4xl font-bold text-white drop-shadow">{vendor?.shop_name ?? 'Vendor'}</h1>
							{!coverUrl && (
								<p className="py-6">
									Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem
									quasi. In deleniti eaque aut repudiandae et a id nisi.
								</p>
							)}
						</div>
					</div>
				</div>

				<div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 p-6">
					{products.data.map((product: ProductLite) => (
						<ProductItem key={product.id} product={product} />
					))}
				</div>
			</div>
		</Authenticated>
	);
}

