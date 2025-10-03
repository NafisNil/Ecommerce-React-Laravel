import React from 'react';
import { Image } from '@/types';

interface CarouselProps {
    images: Image[];
    activeIndex?: number;
    onSelect?: (index: number) => void;
    debug?: boolean;
}

const Carousel: React.FC<CarouselProps> = ({ images, activeIndex = 0, onSelect, debug = false }) => {
    const safeActive = images.length === 0 ? -1 : Math.min(Math.max(activeIndex, 0), images.length - 1);

    React.useEffect(() => {
        if (debug) {
                // Lightweight debug info (remove later)
            console.debug('[Carousel] images payload', images);
        }
    }, [debug, images]);

    return (
        <div className="flex items-start gap-3">
            <div className="flex flex-col items-center gap-1.5 py-1 max-h-[460px] overflow-auto pr-1">
                {images.map((img, i) => {
                    const isActive = i === safeActive;
                    return (
                        <button
                            type="button"
                            key={img.id}
                            onClick={() => onSelect && onSelect(i)}
                            className={
                                'border-2 rounded focus:outline-none transition ' +
                                (isActive ? 'border-blue-500 ring-1 ring-blue-300' : 'border-transparent hover:border-blue-400')
                            }
                        >
                            <img
                                src={img.thumb || img.small || img.medium || img.large || img.original || '/favicon.svg'}
                                alt={img.alt_text || 'Thumbnail'}
                                className="h-24 w-24 cursor-pointer object-cover rounded"
                                onError={(e) => {
                                    e.currentTarget.onerror = null;
                                    e.currentTarget.src = '/favicon.svg';
                                }}
                            />
                        </button>
                    );
                })}
            </div>
            <div className="flex-1">
                {safeActive >= 0 && images[safeActive] && (
                    <img
                        key={images[safeActive].id}
                        src={
                            images[safeActive].medium ||
                            images[safeActive].large ||
                            images[safeActive].small ||
                            images[safeActive].thumb ||
                            images[safeActive].original ||
                            '/favicon.svg'
                        }
                        alt={images[safeActive].alt_text || 'Selected image'}
                        className="w-full object-cover rounded border"
                        onError={(e) => {
                            e.currentTarget.onerror = null;
                            e.currentTarget.src = '/favicon.svg';
                        }}
                    />
                )}
                {safeActive === -1 && (
                    <div className="w-full max-w-md aspect-square flex items-center justify-center border rounded bg-neutral-50 text-neutral-400">
                        No images
                    </div>
                )}
            </div>
        </div>
    );
};

export default Carousel;