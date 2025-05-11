import React, { useState } from 'react';
import { toast } from 'react-hot-toast';

const SearchResult = ({ result, type, rootFolders, onAdd }) => {
  const [selectedFolder, setSelectedFolder] = useState('');
  const [isAdding, setIsAdding] = useState(false);
  const [detailsExpanded, setDetailsExpanded] = useState(false);
  const [imgError, setImgError] = useState(false);
  
  // Use fallback image if original image fails to load or doesn't exist
  const imageUrl = imgError || 
                  !(result.images?.find(img => img.coverType === 'poster')?.remoteUrl || 
                    result.images?.find(img => img.coverType === 'poster')?.url)
                    ? '/coverunavailable.png'
                    : (result.images?.find(img => img.coverType === 'poster')?.remoteUrl || 
                       result.images?.find(img => img.coverType === 'poster')?.url);
  
  // Handle image loading error
  const handleImageError = () => {
    setImgError(true);
  };
  
  const handleAdd = async () => {
    if (!selectedFolder) {
      toast.error('Please select a root folder');
      return;
    }
    
    setIsAdding(true);
    
    try {
      await onAdd(result, selectedFolder);
      toast.success(`Added ${result.title} to your library`);
    } catch (err) {
      console.error('Failed to add media:', err);
      toast.error(`Failed to add ${result.title}`);
    } finally {
      setIsAdding(false);
    }
  };
  
  return (
    <div className="bg-dark-200 rounded-lg overflow-hidden border border-dark-300">
      <div className="p-4 sm:p-6 flex flex-col sm:flex-row gap-6">
        {/* Poster */}
        <div className="w-full sm:w-36 md:w-48 flex-shrink-0">
          <img 
            src={imageUrl} 
            alt={result.title} 
            onError={handleImageError}
            className="w-full h-auto rounded-md object-cover aspect-[2/3]"
          />
        </div>
        
        {/* Content */}
        <div className="flex-grow">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div>
              <h3 className="text-xl font-bold text-white">
                {result.title}
                {result.year && <span className="ml-2 text-gray-400 font-normal">{result.year}</span>}
              </h3>
              
              {/* Ratings */}
              {result.ratings?.value && (
                <div className="flex items-center mt-1">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-yellow-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  <span className="text-sm text-yellow-500">{result.ratings.value}</span>
                </div>
              )}
            </div>
            
            {/* In Library Badge */}
            {result.inLibrary && (
              <div className="badge-in-library inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                In Library
              </div>
            )}
          </div>
          
          {/* Overview */}
          <p className={`text-gray-300 text-sm mb-4 ${!detailsExpanded ? 'line-clamp-3' : ''}`}>
            {result.overview || 'No overview available'}
          </p>
          
          {result.overview && result.overview.length > 150 && (
            <button 
              className="text-xs text-primary-400 hover:text-primary-300 mb-4"
              onClick={() => setDetailsExpanded(!detailsExpanded)}
            >
              {detailsExpanded ? 'Show less' : 'Show more'}
            </button>
          )}
          
          {/* Additional details */}
          <div className="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm mb-6">
            <div>
              <span className="text-gray-500">Status:</span>{' '}
              <span className="text-gray-300">{result.status || 'N/A'}</span>
            </div>
            
            {type === 'show' ? (
              <>
                <div>
                  <span className="text-gray-500">Network:</span>{' '}
                  <span className="text-gray-300">{result.network || 'N/A'}</span>
                </div>
                <div>
                  <span className="text-gray-500">Seasons:</span>{' '}
                  <span className="text-gray-300">{result.seasonCount || 'N/A'}</span>
                </div>
              </>
            ) : (
              <>
                <div>
                  <span className="text-gray-500">Runtime:</span>{' '}
                  <span className="text-gray-300">{result.runtime ? `${result.runtime} mins` : 'N/A'}</span>
                </div>
                <div>
                  <span className="text-gray-500">Release:</span>{' '}
                  <span className="text-gray-300">
                    {result.digitalRelease || result.inCinemas || result.physicalRelease
                      ? new Date(result.digitalRelease || result.inCinemas || result.physicalRelease).toLocaleDateString()
                      : 'N/A'}
                  </span>
                </div>
              </>
            )}
          </div>
          
          {/* Add controls */}
          {!result.inLibrary && (
            <div className="flex flex-col sm:flex-row gap-3 sm:items-center">
              <div className="w-full sm:w-64">
                <select
                  value={selectedFolder}
                  onChange={(e) => setSelectedFolder(e.target.value)}
                  className="input w-full"
                  disabled={isAdding}
                >
                  <option value="">Select storage location...</option>
                  {rootFolders.map((folder) => (
                    <option key={folder.id} value={folder.path}>
                      {folder.path}
                    </option>
                  ))}
                </select>
              </div>
              <button
                onClick={handleAdd}
                disabled={!selectedFolder || isAdding}
                className="btn-primary"
              >
                {isAdding ? (
                  <>
                    <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Adding...
                  </>
                ) : (
                  'Add to Library'
                )}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SearchResult;