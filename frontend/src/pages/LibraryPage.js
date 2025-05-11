import React, { useState, useEffect } from 'react';
import { toast } from 'react-hot-toast';
import { sonarrApi, radarrApi } from '../utils/api';
import MediaCard from '../components/media/MediaCard';

const LibraryPage = () => {
  const [activeTab, setActiveTab] = useState('shows');
  const [shows, setShows] = useState([]);
  const [movies, setMovies] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchLibrary = async () => {
      setLoading(true);
      setError(null);
      
      try {
        if (activeTab === 'shows') {
          const seriesData = await sonarrApi.getSeries();
          setShows(seriesData);
        } else {
          const moviesData = await radarrApi.getMovies();
          setMovies(moviesData);
        }
      } catch (err) {
        setError('Failed to load library. Please try again later.');
        toast.error('Failed to load library');
        console.error('Error fetching library:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchLibrary();
  }, [activeTab]);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex items-center justify-between mb-8">
        <h1 className="text-3xl font-bold text-white">Your Media Library</h1>
        
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

      {loading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          {[...Array(10)].map((_, i) => (
            <div key={i} className="bg-dark-200 rounded-lg overflow-hidden h-80 animate-pulse">
              <div className="h-3/5 bg-dark-300"></div>
              <div className="p-4 space-y-3">
                <div className="h-4 bg-dark-300 rounded w-3/4"></div>
                <div className="h-4 bg-dark-300 rounded w-1/2"></div>
                <div className="h-4 bg-dark-300 rounded w-5/6"></div>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          {activeTab === 'shows' ? (
            shows.length > 0 ? (
              shows.map((show) => (
                <MediaCard 
                  key={show.id} 
                  media={show} 
                  type="show"
                />
              ))
            ) : (
              <div className="col-span-full text-center py-12">
                <h3 className="text-xl text-gray-400 mb-4">No TV shows in your library</h3>
                <p className="text-gray-500">Add shows from the Search page</p>
              </div>
            )
          ) : (
            movies.length > 0 ? (
              movies.map((movie) => (
                <MediaCard 
                  key={movie.id} 
                  media={movie} 
                  type="movie"
                />
              ))
            ) : (
              <div className="col-span-full text-center py-12">
                <h3 className="text-xl text-gray-400 mb-4">No movies in your library</h3>
                <p className="text-gray-500">Add movies from the Search page</p>
              </div>
            )
          )}
        </div>
      )}
    </div>
  );
};

export default LibraryPage;
