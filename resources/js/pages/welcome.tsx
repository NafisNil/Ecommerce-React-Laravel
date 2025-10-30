import ProductItem from '@/components/App/ProductItem';
import Authenticated from '@/layouts/AuthenticatedLayout';
// Local lightweight types to avoid coupling to ambient '@/types'
type PaginationProps<T> = {
  data: T[];
  per_page: number;
  current_page: number;
  last_page: number;
};
type PageProps<T> = T & { csrf_token: string; auth: { user: unknown } };
import { Head } from '@inertiajs/react';

type Slider = { id:number; title?:string|null; subtitle?:string|null; link_url?:string|null; image_url:string };
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

export default function Welcome({
products, sliders = [], offered = [], featured = []
}: PageProps<{ products: PaginationProps<ProductCard>, sliders?: Slider[]; offered?: ProductCard[]; featured?: ProductCard[] }>) {


    return (
    <Authenticated>

        <Head title="Welcome" />
        <div className="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
                {/* Carousel Section */}
                        {Array.isArray(sliders) && sliders.length > 0 ? (
                            <div className="carousel w-full h-[38vh] md:h-[50vh] lg:h-[50vh]">
                                {sliders.map((slide, idx) => (
                                    <div id={`slide${idx+1}`} key={slide.id} className="carousel-item relative w-full h-full">
                                        <img src={slide.image_url} className="w-full h-full object-cover" />
                    {(slide.title || slide.subtitle || slide.link_url) && (
                      <div className="absolute inset-0 flex items-center justify-center z-10 px-4 pointer-events-none">
                        <div className="bg-black/40 text-white p-4 rounded max-w-xl w-full sm:w-auto text-center pointer-events-auto">
                                                    {slide.title && <h3 className="text-2xl font-bold mb-1">{slide.title}</h3>}
                                                    {slide.subtitle && <p className="mb-3">{slide.subtitle}</p>}
                                                    {slide.link_url && <a href={slide.link_url} className="btn btn-primary">See more</a>}
                                                </div>
                                            </div>
                                        )}
                    <div className="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between z-20">
                                            <a href={`#slide${idx === 0 ? sliders.length : idx}`} className="btn btn-circle">❮</a>
                                            <a href={`#slide${idx + 2 > sliders.length ? 1 : idx + 2}`} className="btn btn-circle">❯</a>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="hero bg-zinc-200 h-[38vh] md:h-[50vh]">
                                <div className="hero-content text-center">
                                    <div className="max-w-md">
                                        <h1 className="text-5xl font-bold">Hello there</h1>
                                        <p className="py-6">Welcome to our store.</p>
                                    </div>
                                </div>
                            </div>
                        )}

        {/* Offered products */}
        {Array.isArray(offered) && offered.length > 0 && (
          <section className="px-6 mt-8">
            <h2 className="text-xl font-semibold mb-3">Offered Products</h2>
            <div className="carousel carousel-center w-full space-x-4 p-2">
              {offered.map((p) => (
                <div key={p.id} className="carousel-item w-64">
                  <div className="w-64">
                    <ProductItem product={p as unknown as ProductCard} />
                  </div>
                </div>
              ))}
            </div>
          </section>
        )}

        {/* Featured products */}
        {Array.isArray(featured) && featured.length > 0 && (
          <section className="px-6 mt-10">
            <h2 className="text-xl font-semibold mb-3">Featured Products</h2>
            <div className="carousel carousel-center w-full space-x-4 p-2">
              {featured.map((p) => (
                <div key={p.id} className="carousel-item w-64">
                  <div className="w-64">
                    <ProductItem product={p as unknown as ProductCard} />
                  </div>
                </div>
              ))}
            </div>
          </section>
        )}

        {/* Products Section */}
            

            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 p-6">
                {products.data.map((product: ProductCard) => (
                <ProductItem key={product.id} product={product} />
                ))}
            </div>

        </div>
    </Authenticated>
    );
    }
