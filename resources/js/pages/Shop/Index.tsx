import ProductItem from '@/components/App/ProductItem';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

// Lightweight local types
type ProductCard = {
  id: number;
  title: string;
  slug: string;
  image: string | null;
  price: number;
  user: { name: string; shop_name?: string | null };
  department: { name: string; slug: string };
  is_offered?: boolean;
  offered_price?: number | null;
};
type Pagination<T> = {
  data: T[];
  // Some responses include meta/links; support both shapes defensively
  current_page?: number;
  last_page?: number;
  meta?: { current_page: number; last_page: number };
};

export default function ShopIndex({
  products,
  keyword = '',
}: {
  products: Pagination<ProductCard>;
  keyword?: string;
}) {
  const [term, setTerm] = useState<string>(keyword ?? '');

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(route('shop.index'), { keyword: term || undefined }, { preserveScroll: true, preserveState: true });
  };

  const currentPage = products.meta?.current_page ?? products.current_page ?? 1;
  const lastPage = products.meta?.last_page ?? products.last_page ?? 1;

  const goToPage = (page: number) => {
    router.get(route('shop.index'), { keyword: term || undefined, page }, { preserveScroll: true });
  };

  return (
    <Authenticated>
      <Head title="Shop" />
      <div className="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <section className="px-6 pt-6">
          <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <h1 className="text-2xl font-semibold text-base-content">Shop</h1>
            <form onSubmit={onSearch} className="join w-full md:w-auto">
              <input
                type="text"
                value={term}
                onChange={(e) => setTerm(e.target.value)}
                placeholder="Search products..."
                className="input input-bordered join-item w-full md:w-80"
              />
              <button type="submit" className="btn btn-primary join-item">Search</button>
            </form>
          </div>
        </section>

        <section className="p-6">
          {products.data.length === 0 ? (
            <div className="text-center py-16 opacity-70">No products found.</div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {products.data.map((product) => (
                <ProductItem key={product.id} product={product} />
              ))}
            </div>
          )}

          {/* Simple pagination */}
          {lastPage > 1 && (
            <div className="flex items-center justify-center gap-2 mt-8">
              <button
                className="btn btn-sm"
                disabled={currentPage <= 1}
                onClick={() => goToPage(currentPage - 1)}
              >
                Previous
              </button>
              <span className="text-sm">Page {currentPage} of {lastPage}</span>
              <button
                className="btn btn-sm"
                disabled={currentPage >= lastPage}
                onClick={() => goToPage(currentPage + 1)}
              >
                Next
              </button>
            </div>
          )}
        </section>
      </div>
    </Authenticated>
  );
}
