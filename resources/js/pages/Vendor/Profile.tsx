import ProductItem from '@/components/App/ProductItem';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

type ProductLite = {
	id: number;
	// Allow other properties without using `any`
	[key: string]: unknown;
};

type Pagination<T> = {
	data: T[];
};

export default function Profile({
	products,
}: { vendor?: unknown; products: Pagination<ProductLite> }) {
	return (
		<Authenticated>
			<Head title="Welcome" />
			<div className="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
				<div className="hero bg-zinc-200 h-[300px]">
					<div className="hero-content text-center">
						<div className="max-w-md">
							<h1 className="text-5xl font-bold">Hello there</h1>
							<p className="py-6">
								Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem
								quasi. In deleniti eaque aut repudiandae et a id nisi.
							</p>
							<button className="btn btn-primary">Get Started</button>
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

