import ProductItem from '@/components/App/ProductItem';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import React from 'react';

// Minimal local types to avoid build issues with ambient '@/types'
type ProductLite = {
  id: number;
  title: string;
  slug: string;
  image: string | null;
  price: number;
  user: { name: string; shop_name?: string | null };
  department: { name: string };
};

type Pagination<T> = {
  data: T[];
  current_page?: number;
  last_page?: number;
  per_page?: number;
};

type DepartmentLite = {
  id: number;
  name: string;
  slug: string;
  meta_title?: string | null;
  meta_description?: string | null;
};

export default function DepartmentIndex({
  products,
  keyword,
  department,
}: {
  products: Pagination<ProductLite>;
  keyword?: string | null;
  department: DepartmentLite;
}) {
  const [q, setQ] = React.useState(keyword || '');

  const submitSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(route('departments.products', department.slug), { keyword: q }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  };

  return (
    <Authenticated>
      <Head title={department.name} />
      <div className="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div className="border-b bg-base-100">
          <div className="container mx-auto px-4 py-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold text-base-content">{department.name}</h1>
              {department.meta_description && (
                <p className="mt-1 text-sm text-base-content/70 max-w-2xl">{department.meta_description}</p>
              )}
            </div>
            <form onSubmit={submitSearch} className="join w-full md:w-auto">
              <input
                type="text"
                className="input input-bordered join-item w-full md:w-80"
                placeholder="Search in this department"
                value={q}
                onChange={(e) => setQ(e.target.value)}
              />
              <button className="btn btn-primary join-item" type="submit">Search</button>
            </form>
          </div>
        </div>

        <div className="container mx-auto p-4">
          {products.data.length === 0 ? (
            <div className="py-12 text-center text-base-content/70">No products found.</div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {products.data.map((product) => (
                <ProductItem key={product.id} product={product} />
              ))}
            </div>
          )}
        </div>
      </div>
    </Authenticated>
  );
}
