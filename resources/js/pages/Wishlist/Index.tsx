import ProductItem from '@/components/App/ProductItem';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

type Pagination<T> = { data: T[] };
type ProductLite = {
  id: number;
  title: string;
  slug: string;
  image: string | null;
  price: number;
  user: { name: string; shop_name?: string | null };
  department: { name: string; slug: string };
};

export default function WishlistIndex({ products }: { products: Pagination<ProductLite> }) {
  return (
    <Authenticated>
      <Head title="My Wishlist" />
      <div className="container mx-auto px-4 py-6">
        <h1 className="text-3xl font-bold mb-6">My Wishlist</h1>
        {products.data.length === 0 ? (
          <div className="text-neutral-500">Your wishlist is empty.</div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {products.data.map((product: ProductLite) => (
              <ProductItem key={product.id} product={product} />
            ))}
          </div>
        )}
      </div>
    </Authenticated>
  );
}
