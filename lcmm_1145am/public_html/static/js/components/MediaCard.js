import React, { useState } from 'react';

const MediaCard = ({ media, type }) => {
  const [isHovered, setIsHovered] = useState(false);
  
  const imageUrl = media.images?.find(img => img.coverType === 'poster')?.remoteUrl || 
                   media.images?.find(img => img.coverType === 'poster')?.url || 
                   '/coverunavailable.png';
  
  const year = type === 'show' 
    ? media.year 
    : new Date(media.inCinemas || media.digitalRelease || media.physicalRelease || '').getFullYear();
  
  const status = type === 'show' 
    ? media.status 
    : media.status;
  
  const rating = media.ratings?.value || 'N/A';
  
  return (
    <div 
      className="media-card bg-dark-200 rounded-lg overflow-hidden shadow-lg h-full flex flex-col"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      <div className="relative overflow-hidden" style={{ minHeight: '250px' }}>
        <img 
          src={imageUrl} 
          alt={media.title} 
          className="w-full h-auto object-cover transition-transform duration-300"
          style={{ transform: isHovered ? 'scale(1.05)' : 'scale(1)' }}
        />
        <div 
          className={`media-card-overlay absolute inset-0 flex flex-col justify-end p-4 ${isHovered ? 'opacity-100' : 'opacity-0'}`}
        >
          <div className="space-y-1">
            <div className="text-xs text-gray-300 flex items-center">
              {year && <span className="mr-2">{year}</span>}
              {status && (
                <span className="px-1.5 py-0.5 bg-dark-500 rounded text-xs">
                  {status}
                </span>
              )}
            </div>
            {rating !== 'N/A' && (
              <div className="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-yellow-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span className="text-xs text-yellow-500">{rating}</span>
              </div>
            )}
          </div>
        </div>
      </div>
      
      <div className="p-4 flex-grow">
        <h3 className="font-bold text-white truncate mb-1" title={media.title}>
          {media.title}
        </h3>
        <p className="text-xs text-gray-400 line-clamp-3">
          {media.overview || 'No overview available'}
        </p>
      </div>
      
      <div className="px-4 pb-4">
        {type === 'show' ? (
          <div className="flex justify-between text-xs text-gray-400">
            <span>{media.episodeCount || 0} episodes</span>
            <span>{media.seasonCount || 0} seasons</span>
          </div>
        ) : (
          <div className="flex justify-between text-xs text-gray-400">
            <span>{media.runtime ? `${media.runtime} mins` : 'N/A'}</span>
            <span>
              {media.hasFile ? (
                <span className="text-green-500">Downloaded</span>
              ) : (
                <span className="text-yellow-500">Pending</span>
              )}
            </span>
          </div>
        )}
      </div>
    </div>
  );
};

export default MediaCard;