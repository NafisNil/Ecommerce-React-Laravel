import ProductItem from '@/components/App/ProductItem';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

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
  filters,
}: {
  products: Pagination<ProductCard>;
  keyword?: string;
  filters?: Partial<{ department_id: number | string; category_id: number | string; featured: boolean; offered: boolean; min_price: string | number | null; max_price: string | number | null }>;
}) {
  type Department = { id: number; name: string; slug: string; categories?: { id: number; name: string; slug: string }[] };
  const page = usePage<{ departments?: Department[] }>();
  const departments = useMemo(() => (page.props?.departments ?? []) as Department[], [page.props?.departments]);

  const [term, setTerm] = useState<string>(keyword ?? '');
  const [departmentId, setDepartmentId] = useState<string | number | ''>(filters?.department_id ?? '');
  const [categoryId, setCategoryId] = useState<string | number | ''>(filters?.category_id ?? '');
  const [featured, setFeatured] = useState<boolean>(!!filters?.featured);
  const [offered, setOffered] = useState<boolean>(!!filters?.offered);
  const [minPrice, setMinPrice] = useState<string>(filters?.min_price ? String(filters?.min_price) : '');
  const [maxPrice, setMaxPrice] = useState<string>(filters?.max_price ? String(filters?.max_price) : '');

  const categories = useMemo(() => {
    const dep = departments?.find(d => String(d.id) === String(departmentId));
    return dep?.categories ?? [];
  }, [departments, departmentId]);

  useEffect(() => {
    // Reset category when department changes
    setCategoryId('');
  }, [departmentId]);

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(route('shop.index'), {
      keyword: term || undefined,
      department_id: departmentId || undefined,
      category_id: categoryId || undefined,
      featured: featured || undefined,
      offered: offered || undefined,
      min_price: minPrice || undefined,
      max_price: maxPrice || undefined,
    }, { preserveScroll: true, preserveState: true });
  };

  const currentPage = products.meta?.current_page ?? products.current_page ?? 1;
  const lastPage = products.meta?.last_page ?? products.last_page ?? 1;

  const goToPage = (page: number) => {
    router.get(route('shop.index'), {
      keyword: term || undefined,
      page,
      department_id: departmentId || undefined,
      category_id: categoryId || undefined,
      featured: featured || undefined,
      offered: offered || undefined,
      min_price: minPrice || undefined,
      max_price: maxPrice || undefined,
    }, { preserveScroll: true });
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
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
            {/* Sidebar filters */}
            <aside className="md:col-span-1 space-y-4">
              <div className="card bg-base-100 shadow">
                <div className="card-body">
                  <h3 className="card-title text-base">Filters</h3>
                  <form onSubmit={onSearch} className="space-y-3">
                    <div className="form-control">
                      <label className="label"><span className="label-text">Department</span></label>
                      <select className="select select-bordered w-full" value={departmentId} onChange={(e) => setDepartmentId(e.target.value)}>
                        <option value="">All</option>
                        {departments?.map((d) => (
                          <option key={d.id} value={d.id}>{d.name}</option>
                        ))}
                      </select>
                    </div>
                    <div className="form-control">
                      <label className="label"><span className="label-text">Category</span></label>
                      <select className="select select-bordered w-full" value={categoryId} onChange={(e) => setCategoryId(e.target.value)} disabled={!departmentId}>
                        <option value="">All</option>
                        {categories?.map((c) => (
                          <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                      </select>
                    </div>
                    <div className="flex gap-2">
                      <input type="checkbox" className="checkbox" checked={featured} onChange={(e) => setFeatured(e.target.checked)} id="flt-featured" />
                      <label htmlFor="flt-featured" className="label-text">Featured</label>
                    </div>
                    <div className="flex gap-2">
                      <input type="checkbox" className="checkbox" checked={offered} onChange={(e) => setOffered(e.target.checked)} id="flt-offered" />
                      <label htmlFor="flt-offered" className="label-text">Offered</label>
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                      <input type="number" min={0} step="0.01" placeholder="Min" className="input input-bordered w-full" value={minPrice} onChange={(e) => setMinPrice(e.target.value)} />
                      <input type="number" min={0} step="0.01" placeholder="Max" className="input input-bordered w-full" value={maxPrice} onChange={(e) => setMaxPrice(e.target.value)} />
                    </div>
                    <div className="flex gap-2 pt-1">
                      <button type="submit" className="btn btn-primary btn-sm">Apply</button>
                      <button type="button" className="btn btn-ghost btn-sm" onClick={() => {
                        setDepartmentId(''); setCategoryId(''); setFeatured(false); setOffered(false); setMinPrice(''); setMaxPrice('');
                        router.get(route('shop.index'), { keyword: term || undefined }, { preserveScroll: true, preserveState: true });
                      }}>Reset</button>
                    </div>
                  </form>
                </div>
              </div>
            </aside>

            {/* Products grid */}
            <div className="md:col-span-3">
              {products.data.length === 0 ? (
                <div className="text-center py-16 opacity-70">No products found.</div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  {products.data.map((product) => (
                    <ProductItem key={product.id} product={product} />
                  ))}
                </div>
              )}
            </div>
          </div>

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
