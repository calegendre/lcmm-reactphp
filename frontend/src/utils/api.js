import axios from 'axios';

const backendUrl = process.env.REACT_APP_BACKEND_URL || '';

// Initialize API with auth header if token exists
const token = localStorage.getItem('lcmm_token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Sonarr API
export const sonarrApi = {
  // Get all series
  getSeries: async () => {
    try {
      const response = await axios.get(`${backendUrl}/api/sonarr?action=series`);
      return response.data;
    } catch (error) {
      console.error('Error fetching series:', error);
      throw error;
    }
  },

  // Get root folders
  getRootFolders: async () => {
    try {
      const response = await axios.get(`${backendUrl}/api/sonarr?action=rootfolders`);
      return response.data;
    } catch (error) {
      console.error('Error fetching root folders:', error);
      throw error;
    }
  },

  // Search for series
  searchSeries: async (term) => {
    try {
      const response = await axios.get(`${backendUrl}/api/sonarr?action=search&term=${encodeURIComponent(term)}`);
      return response.data;
    } catch (error) {
      console.error('Error searching series:', error);
      throw error;
    }
  },

  // Add series
  addSeries: async (seriesData) => {
    try {
      const response = await axios.post(`${backendUrl}/api/sonarr?action=add`, seriesData);
      return response.data;
    } catch (error) {
      console.error('Error adding series:', error);
      throw error;
    }
  }
};

// Radarr API
export const radarrApi = {
  // Get all movies
  getMovies: async () => {
    try {
      const response = await axios.get(`${backendUrl}/api/radarr?action=movies`);
      return response.data;
    } catch (error) {
      console.error('Error fetching movies:', error);
      throw error;
    }
  },

  // Get root folders
  getRootFolders: async () => {
    try {
      const response = await axios.get(`${backendUrl}/api/radarr?action=rootfolders`);
      return response.data;
    } catch (error) {
      console.error('Error fetching root folders:', error);
      throw error;
    }
  },

  // Search for movies
  searchMovies: async (term) => {
    try {
      const response = await axios.get(`${backendUrl}/api/radarr?action=search&term=${encodeURIComponent(term)}`);
      return response.data;
    } catch (error) {
      console.error('Error searching movies:', error);
      throw error;
    }
  },

  // Add movie
  addMovie: async (movieData) => {
    try {
      const response = await axios.post(`${backendUrl}/api/radarr?action=add`, movieData);
      return response.data;
    } catch (error) {
      console.error('Error adding movie:', error);
      throw error;
    }
  }
};

// User and admin APIs
export const userApi = {
  // Get all users (admin only)
  getUsers: async () => {
    try {
      const response = await axios.get(`${backendUrl}/api/users?action=list`);
      return response.data;
    } catch (error) {
      console.error('Error fetching users:', error);
      throw error;
    }
  },

  // Get user by ID (admin only)
  getUser: async (userId) => {
    try {
      const response = await axios.get(`${backendUrl}/api/users?action=get&id=${userId}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching user:', error);
      throw error;
    }
  },

  // Update user role (admin only)
  updateUser: async (userId, role) => {
    try {
      const response = await axios.put(`${backendUrl}/api/users?action=update`, { id: userId, role });
      return response.data;
    } catch (error) {
      console.error('Error updating user:', error);
      throw error;
    }
  },

  // Delete user (admin only)
  deleteUser: async (userId) => {
    try {
      const response = await axios.delete(`${backendUrl}/api/users?action=delete&id=${userId}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting user:', error);
      throw error;
    }
  },

  // Get user activity (admin only)
  getUserActivity: async (userId) => {
    try {
      const response = await axios.get(`${backendUrl}/api/users?action=activity&id=${userId}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching user activity:', error);
      throw error;
    }
  }
};

// Invitation APIs
export const invitationApi = {
  // Get all invitations (admin only)
  getInvitations: async () => {
    try {
      const response = await axios.get(`${backendUrl}/api/invitations?action=list`);
      return response.data;
    } catch (error) {
      console.error('Error fetching invitations:', error);
      throw error;
    }
  },

  // Create invitation (admin only)
  createInvitation: async (email = null) => {
    try {
      const response = await axios.post(`${backendUrl}/api/invitations?action=create`, { email });
      return response.data;
    } catch (error) {
      console.error('Error creating invitation:', error);
      throw error;
    }
  },

  // Delete invitation (admin only)
  deleteInvitation: async (invitationId) => {
    try {
      const response = await axios.delete(`${backendUrl}/api/invitations?action=delete&id=${invitationId}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting invitation:', error);
      throw error;
    }
  }
};
