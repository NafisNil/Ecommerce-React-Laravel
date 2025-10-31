import React from 'react';
import Authenticated from '@/layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import CurrencyFormatter from '@/components/Core/CurrencyFormatter';

type OrderSummary = { id: number; status: string; total_price: number; items_count: number; created_at: string };

type PaginatorMeta = { current_page: number; last_page: number; per_page?: number; total?: number };
type PaginatorLink = { url: string | null; label: string; active: boolean };
type Paginated<T> = {
  data: T[];
  meta?: PaginatorMeta;
  links?: PaginatorLink[];
};

type PageProps<T> = T & { csrf_token?: string };

export default function OrdersIndex({ orders }: PageProps<{ orders: Paginated<OrderSummary> }>) {
  const meta: PaginatorMeta = orders.meta ?? {
    current_page: 1,
    last_page: 1,
    per_page: orders.data.length,
    total: orders.data.length,
  };
  const links: PaginatorLink[] = Array.isArray(orders.links) ? orders.links! : [];

  return (
    <Authenticated>
      <Head title="My Orders" />
      <div className="container mx-auto p-4">
        <h1 className="text-2xl font-semibold mb-4">My Orders</h1>
        {orders.data.length === 0 ? (
          <div className="text-sm text-gray-500">You have no orders yet.</div>
        ) : (
          <div className="card bg-white dark:bg-gray-800 shadow">
            <div className="overflow-x-auto">
              <table className="table table-sm">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th className="text-right">Total</th>
                  </tr>
                </thead>
                <tbody>
                  {orders.data.map((o) => (
                    <tr key={o.id}>
                      <td>#{o.id}</td>
                      <td>{new Date(o.created_at).toLocaleString()}</td>
                      <td><span className="badge badge-ghost badge-sm uppercase">{o.status}</span></td>
                      <td>{o.items_count}</td>
                      <td className="text-right"><CurrencyFormatter amount={o.total_price} /></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="p-3 flex items-center justify-between text-sm">
              <div>
                Page {meta.current_page} of {meta.last_page}
              </div>
              <div className="join">
                {links
                  .filter((l) => typeof l.label === 'string' && !l.label.toLowerCase().includes('...'))
                  .map((l, idx) => (
                    <Link
                      key={idx}
                      href={l.url || ''}
                      preserveScroll
                      className={`btn btn-xs join-item ${l.active ? 'btn-primary' : ''} ${!l.url ? 'btn-disabled' : ''}`}
                      dangerouslySetInnerHTML={{ __html: l.label }}
                    />
                  ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </Authenticated>
  );
}
