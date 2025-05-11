import React, { useState, useEffect } from 'react';
import { toast } from 'react-hot-toast';
import { sonarrApi, radarrApi } from '../utils/api';
import SearchResult from '../components/media/SearchResult';

const SearchPage = () => {
  const [activeTab, setActiveTab] = useState('shows');
  const [searchTerm, setSearchTerm] = useState('');
  const [isSearching, setIsSearching] = useState(false);
  const [searchResults, setSearchResults] = useState([]);
  const [rootFolders, setRootFolders] = useState([]);
  const [error, setError] = useState(null);

  // Fetch root folders when tab changes
  useEffect(() => {
    const fetchRootFolders = async () => {
      try {
        if (activeTab === 'shows') {
          const folders = await sonarrApi.getRootFolders();
          setRootFolders(folders);
        } else {
          const folders = await radarrApi.getRootFolders();
          setRootFolders(folders);
        }
      } catch (err) {
        console.error('Error fetching root folders:', err);
        setError('Failed to load storage locations. Some features may be limited.');
      }
    };

    fetchRootFolders();
  }, [activeTab]);

  const handleSearch = async (e) => {
    e.preventDefault();
    
    if (!searchTerm.trim()) {
      return;
    }
    
    setIsSearching(true);
    setSearchResults([]);
    setError(null);
    
    try {
      if (activeTab === 'shows') {
        const results = await sonarrApi.searchSeries(searchTerm);
        setSearchResults(results);
      } else {
        const results = await radarrApi.searchMovies(searchTerm);
        setSearchResults(results);
      }
    } catch (err) {
      console.error('Search error:', err);
      setError('Search failed. Please try again later.');
      toast.error('Search failed');
    } finally {
      setIsSearching(false);
    }
  };

  const handleAddMedia = async (media, rootFolderPath) => {
    try {
      let response;
      
      if (activeTab === 'shows') {
        // Add TV show
        const seriesData = {
          ...media,
          rootFolderPath,
          monitored: true,
          qualityProfileId: 1, // Default quality
          languageProfileId: 1, // Default language
          seasonFolder: true,
          addOptions: {
            searchForMissingEpisodes: true
          }
        };
        
        response = await sonarrApi.addSeries(seriesData);
      } else {
        // Add movie
        const movieData = {
          ...media,
          rootFolderPath,
          monitored: true,
          qualityProfileId: 1, // Default quality
          addOptions: {
            searchForMovie: true
          }
        };
        
        response = await radarrApi.addMovie(movieData);
      }
      
      // Update the search results to mark this as in library
      setSearchResults(prev => 
        prev.map(item => {
          if (activeTab === 'shows' && item.tvdbId === media.tvdbId) {
            return { ...item, inLibrary: true };
          } else if (activeTab === 'movies' && item.tmdbId === media.tmdbId) {
            return { ...item, inLibrary: true };
          }
          return item;
        })
      );
      
      toast.success(`${media.title} added to your library`);
      return response;
    } catch (err) {
      console.error('Error adding media:', err);
      toast.error(`Failed to add ${media.title}`);
      throw err;
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex items-center justify-between mb-8">
        <h1 className="text-3xl font-bold text-white">Search &amp; Add Media</h1>
        
        <div className="bg-dark-200 rounded-lg p-1">
          <button
            onClick={() => setActiveTab('shows')}
            className={`px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 ${
              activeTab === 'shows'
                ? 'bg-primary-500 text-white'
                : 'text-gray-300 hover:text-white'
            }`}
          >
            TV Shows
          </button>
          <button
            onClick={() => setActiveTab('movies')}
            className={`px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 ${
              activeTab === 'movies'
                ? 'bg-primary-500 text-white'
                : 'text-gray-300 hover:text-white'
            }`}
          >
            Movies
          </button>
        </div>
      </div>

      {error && (
        <div className="rounded-lg bg-red-900/30 border border-red-700 p-4 text-red-100 mb-6">
          {error}
        </div>
      )}

      <div className="bg-dark-100 rounded-lg border border-dark-300 p-6 mb-8">
        <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-4">
          <div className="flex-grow">
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder={`Search for ${activeTab === 'shows' ? 'TV shows' : 'movies'}...`}
              className="input w-full"
            />
          </div>
          <button
            type="submit"
            disabled={isSearching || !searchTerm.trim()}
            className="btn-primary whitespace-nowrap"
          >
            {isSearching ? (
              <>
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Searching...
              </>
            ) : (
              'Search'
            )}
          </button>
        </form>
      </div>

      <div className="space-y-6">
        {isSearching ? (
          <div className="text-center py-12">
            <svg className="animate-spin h-12 w-12 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p className="mt-4 text-lg text-gray-400">Searching {activeTab === 'shows' ? 'TV shows' : 'movies'}...</p>
          </div>
        ) : searchResults.length > 0 ? (
          <>
            <h2 className="text-xl font-semibold text-white">Search Results</h2>
            <div className="space-y-4">
              {searchResults.map((result) => (
                <SearchResult
                  key={activeTab === 'shows' ? result.tvdbId : result.tmdbId}
                  result={result}
                  type={activeTab === 'shows' ? 'show' : 'movie'}
                  rootFolders={rootFolders}
                  onAdd={handleAddMedia}
                />
              ))}
            </div>
          </>
        ) : searchTerm && !isSearching ? (
          <div className="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-12 h-12 mx-auto text-gray-500">
              <path strokeLinecap="round" strokeLinejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
            </svg>
            <p className="mt-4 text-lg text-gray-400">No results found</p>
            <p className="text-gray-500">Try a different search term</p>
          </div>
        ) : (
          <div className="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-12 h-12 mx-auto text-gray-500">
              <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <p className="mt-4 text-lg text-gray-400">Search for {activeTab === 'shows' ? 'TV shows' : 'movies'}</p>
            <p className="text-gray-500">Type a title to search the {activeTab === 'shows' ? 'Sonarr' : 'Radarr'} database</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default SearchPage;
