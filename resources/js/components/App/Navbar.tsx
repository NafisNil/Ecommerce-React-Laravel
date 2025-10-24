import { Link, router, usePage } from '@inertiajs/react';
import React, { useCallback, useEffect, useState } from 'react';
import MiniCartDropdown from './MiniCartDropdown';


function Navbar() {
const { auth, departments, keyword: pageKeyword, department: pageDepartment } = usePage().props as unknown as {
  auth?: { user?: unknown };
  departments?: Array<{
    id: number;
    name: string;
    slug: string;
    categories?: Array<{ id: number; name: string; slug: string }>
  }>;
  keyword?: string | null;
  department?: { id: number; name: string; slug: string } | null;
};

const user = (auth as { user?: unknown } | undefined)?.user ?? null;

// Local state for search
const [keyword, setKeyword] = useState<string>(typeof pageKeyword === 'string' ? pageKeyword : '');
const [selectedDept, setSelectedDept] = useState<string>(() => (pageDepartment && pageDepartment.slug) ? pageDepartment.slug : 'all');

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

const onSubmit = useCallback((e: React.FormEvent) => {
  e.preventDefault();
  const trimmed = keyword.trim();
  if (selectedDept && selectedDept !== 'all') {
    // Search within selected department
    router.get(route('departments.products', selectedDept), trimmed ? { keyword: trimmed } : {}, { preserveScroll: true, preserveState: true });
  } else {
    // Global search on homepage
    router.get('/', trimmed ? { keyword: trimmed } : {}, { preserveScroll: true, preserveState: true });
  }
}, [keyword, selectedDept]);

    return (
    <div className="navbar bg-base-100 shadow-sm">
        <div className="flex-1">
            <Link href="/" className="btn btn-ghost text-xl">daisyUI</Link>
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
                                <Link href={`${route('departments.products',
                                    dept.slug)}?category=${encodeURIComponent(cat.slug)}`}>
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
                  onChange={(e) => setSelectedDept(e.target.value)}
                  aria-label="Filter by department"
                >
                    <option value="all">All departments</option>
                    {hasDepartments && departments!.map((d) => (
                      <option key={d.id} value={d.slug}>{d.name}</option>
                    ))}
                </select>
                <div className="indicator">
                    <button type="submit" className="btn join-item">Search</button>
                </div>
            </form>
            {/* search */}
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
