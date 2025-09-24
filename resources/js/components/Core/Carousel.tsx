import React from 'react';
import { Image}  from '@/types';
function Carousel({images}: {images: Image[]}) {
    return (
        <div className="flex items-start gap-8">
            <div className="flex flex-col items-center gap-2 py-2">
                {images.map((img, i) => (
                    <a href={'#item' + i} className='border-2 hover:border-blue-400' key={img.id}>
                        <img
                            src={img.thumb}
                            alt={img.alt_text || 'Thumbnail'}
                            className="h-16 w-16 cursor-pointer object-cover rounded"
                            onError={(e) => {
                                e.currentTarget.onerror = null;
                                e.currentTarget.src = '/favicon.svg';
                            }}
                        />
                    </a>
                ))}
            </div>
            <div className="carousel w-96">
                {images.map((img, i) => (
                    <div id={'item' + i} className="carousel-item w-full" key={img.id}>
                        <img
                            src={img.medium}
                            alt={img.alt_text || 'Image ' + (i + 1)}
                            className="w-full object-cover rounded"
                            onError={(e) => {
                                e.currentTarget.onerror = null;
                                e.currentTarget.src = '/favicon.svg';
                            }}
                        />
                    </div>
                ))}
            </div>
 
        </div>
    );
}

export default Carousel;