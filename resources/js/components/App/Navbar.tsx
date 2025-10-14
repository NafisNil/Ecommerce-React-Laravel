import { Link } from '@inertiajs/react';
import React from 'react';
import { usePage } from '@inertiajs/react';
import MiniCartDropdown from './MiniCartDropdown';


function Navbar() {
    const {auth, cart_total_quantity, cart_total_price} = usePage().props;
    const {user} = auth;
return (
<div className="navbar bg-base-100 shadow-sm">
    <div className="flex-1">
        <Link href="/" className="btn btn-ghost text-xl">daisyUI</Link>
    </div>
    <div className="flex-none gap-2">

        <MiniCartDropdown />
        {user && <div className="dropdown dropdown-end">
            <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
                <div className="w-10 rounded-full">
                    <img alt="Tailwind CSS Navbar component"
                        src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" />
                </div>
            </div>
            <ul tabIndex={0} className="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                <li>
                    <Link href={route('profile.edit')} className="justify-between">
                        Profile
                        <span className="badge">New</span>
                    </Link>
                </li>
                <li><Link href="#"className="justify-between">Settings</Link></li>
                <li><Link href={route('logout')} method={"post"} as="button" className="justify-between">Logout</Link></li>
            </ul>
        </div>}
        {!user && <> <Link href={route('login')} className="btn btn-primary">Login</Link> <Link href={route('register')} className="btn">Register</Link> </>}
    </div>
</div>
);
}

export default Navbar;


