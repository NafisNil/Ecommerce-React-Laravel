import { Link, router, usePage } from '@inertiajs/react';
import React, { useCallback, useEffect, useState } from 'react';
import MiniCartDropdown from './MiniCartDropdown';


function Navbar() {
const { auth, departments, keyword: pageKeyword, department: pageDepartment, category: pageCategory } = usePage().props as unknown as {
  auth?: { user?: unknown };
  departments?: Array<{
    id: number;
    name: string;
    slug: string;
    categories?: Array<{ id: number; name: string; slug: string }>
  }>;
  keyword?: string | null;
  department?: { id: number; name: string; slug: string } | null;
  category?: string | null;
};

const user = (auth as { user?: unknown } | undefined)?.user ?? null;

// Local state for search
const [keyword, setKeyword] = useState<string>(typeof pageKeyword === 'string' ? pageKeyword : '');
const [selectedDept, setSelectedDept] = useState<string>(() => (pageDepartment && pageDepartment.slug) ? pageDepartment.slug : 'all');
const [selectedCategory, setSelectedCategory] = useState<string>(() => (typeof pageCategory === 'string' ? pageCategory : 'all'));

// Keep state in sync if the page props change between navigations
useEffect(() => {
  if (typeof pageKeyword === 'string') {
    setKeyword(pageKeyword);
  } else {
    setKeyword('');
  }
}, [pageKeyword]);

useEffect(() => {
  if (pageDepartment && pageDepartment.slug) {
    setSelectedDept(pageDepartment.slug);
  } else {
    setSelectedDept('all');
  }
}, [pageDepartment]);

const hasDepartments = Array.isArray(departments) && departments.length > 0;
// Sync category from page props
useEffect(() => {
  if (typeof pageCategory === 'string' && pageCategory.toLowerCase() !== 'null' && pageCategory.toLowerCase() !== 'all') {
    setSelectedCategory(pageCategory);
  } else {
    setSelectedCategory('all');
  }
}, [pageCategory]);

const onSubmit = useCallback((e: React.FormEvent) => {
  e.preventDefault();
  const trimmed = keyword.trim();
  if (selectedDept && selectedDept !== 'all') {
    // Search within selected department
    const params: Record<string, string> = {};
    if (trimmed) params.keyword = trimmed;
    if (selectedCategory && selectedCategory !== 'all') params.category = selectedCategory;
    router.get(route('departments.products', selectedDept), params, { preserveScroll: true, preserveState: true });
  } else {
    // Global search on homepage
    router.get('/', trimmed ? { keyword: trimmed } : {}, { preserveScroll: true, preserveState: true });
  }
}, [keyword, selectedDept, selectedCategory]);

    return (
    <div className="navbar bg-base-100 shadow-sm">
        <div className="flex-1">
            <Link href="/" className="btn btn-ghost text-xl">daisyUI</Link>
            <Link href={route('shop.index')} className="btn btn-ghost text-md">Shop</Link>
            {hasDepartments && (
            <ul className="menu menu-horizontal px-1">
                {departments.map((dept) => (
                <li key={dept.id}>
                    {Array.isArray(dept.categories) && dept.categories.length > 0 ? (
                    <details>
                        <summary>
                            <Link href={route('departments.products', dept.slug)}>
                            {dept.name}
                            </Link>
                        </summary>
                        <ul className="p-2 bg-base-100 z-10">
                            {dept.categories.map((cat) => (
              <li key={cat.id}>
                <Link
                  href={cat.slug ? `${route('departments.products', dept.slug)}?category=${encodeURIComponent(cat.slug)}` : route('departments.products', dept.slug)}
                >
                {cat.name}
                </Link>
              </li>
                            ))}
                        </ul>
                    </details>
                    ) : (
                    <Link href={route('departments.products', dept.slug)}>{dept.name}</Link>
                    )}
                </li>
                ))}
            </ul>
            )}
        </div>
        <div className="flex-none gap-2">

            {/* search */}
            <form className="join" onSubmit={onSubmit} role="search">
                <div>
                    <div>
                        <input
                          className="input join-item"
                          placeholder="Search products..."
                          value={keyword}
                          onChange={(e) => setKeyword(e.target.value)}
                          aria-label="Search products"
                        />
                    </div>
                </div>
                <select
                  className="select join-item"
                  value={selectedDept}
                  onChange={(e) => { setSelectedDept(e.target.value); setSelectedCategory('all'); }}
                  aria-label="Filter by department"
                >
                    <option value="all">All departments</option>
                    {hasDepartments && departments!.map((d) => (
                      <option key={d.id} value={d.slug}>{d.name}</option>
                    ))}
                </select>
                {selectedDept !== 'all' && (
                  <select
                    className="select join-item"
                    value={selectedCategory}
                    onChange={(e) => setSelectedCategory(e.target.value)}
                    aria-label="Filter by category"
                  >
                    <option value="all">All categories</option>
                    {hasDepartments && departments!.find(d => d.slug === selectedDept)?.categories?.filter((c) => Boolean(c.slug))?.map((c) => (
                      <option key={c.id} value={c.slug}>{c.name}</option>
                    ))}
                  </select>
                )}
                <div className="indicator">
                    <button type="submit" className="btn join-item">Search</button>
                </div>
            </form>
            {/* search */}
                  {/* wishlist */}
      <Link href={route('wishlist.index')} className="btn btn-ghost">
        <span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
            <path strokeLinecap="round" strokeLinejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
          </svg>
        </span>
      </Link>
            <MiniCartDropdown />
            {user && <div className="dropdown dropdown-end">
                <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
                    <div className="w-10 rounded-full">
                        <img alt="Tailwind CSS Navbar component"
                            src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" />
                    </div>
                </div>
                <ul tabIndex={0}
                    className="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                    <li>
                        <Link href={route('profile.edit')} className="justify-between">
                        Profile
                        <span className="badge">New</span>
                        </Link>
                    </li>
                    <li>
                        <Link href="#"className="justify-between">Settings</Link>
                    </li>
                    <li>
                        <Link href={route('logout')} method={"post"} as="button" className="justify-between">Logout
                        </Link>
                    </li>
                </ul>
            </div>}
            {!user && <>
                <Link href={route('login')} className="btn btn-primary">Login</Link>
                <Link href={route('register')} className="btn">Register</Link>
            </>}
        </div>
    </div>
    );
    }

    export default Navbar;
