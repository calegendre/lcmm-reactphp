import React, { useState, useEffect } from 'react';

const Additions = () => {
  const [users, setUsers] = useState([]);
  const [selectedUser, setSelectedUser] = useState('all');
  const [additions, setAdditions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [userAdditionsMap, setUserAdditionsMap] = useState({});

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    try {
      const token = localStorage.getItem('token');
      
      if (!token) {
        setError('Authentication required. Please log in.');
        setLoading(false);
        return;
      }
      
      // Fetch users
      const usersResponse = await fetch('/api/users', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      if (!usersResponse.ok) {
        throw new Error('Failed to fetch users');
      }
      
      const userData = await usersResponse.json();
      setUsers(userData);
      
      // Fetch additions
      const additionsResponse = await fetch('/api/additions', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      if (!additionsResponse.ok) {
        throw new Error('Failed to fetch additions');
      }
      
      const additionsData = await additionsResponse.json();
      
      // Map user emails to additions
      const additionsWithUserEmails = additionsData.map(addition => {
        const user = userData.find(u => u.id === addition.user_id);
        return {
          ...addition,
          userEmail: user ? user.email : 'Unknown User'
        };
      });
      
      // Sort additions by creation date (newest first)
      additionsWithUserEmails.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      setAdditions(additionsWithUserEmails);
      
      // Create a map of user additions for filtering
      const additionsMap = {};
      userData.forEach(user => {
        additionsMap[user.id] = additionsWithUserEmails.filter(
          addition => addition.user_id === user.id
        );
      });
      
      setUserAdditionsMap(additionsMap);
      setLoading(false);
    } catch (err) {
      console.error('Error:', err);
      setError('Failed to load data. Please try again.');
      setLoading(false);
    }
  };

  const handleUserChange = (e) => {
    const userId = e.target.value;
    setSelectedUser(userId);
    
    if (userId === 'all') {
      fetchUsers();
    } else {
      const userAdditions = userAdditionsMap[userId] || [];
      setAdditions(userAdditions);
    }
  };

  if (loading) {
    return (
      <div className="text-center py-10">
        <svg className="animate-spin h-12 w-12 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p className="mt-4 text-lg text-gray-400">Loading additions...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="rounded-lg bg-red-900/30 border border-red-700 p-4 text-red-100">
        {error}
      </div>
    );
  }

  return (
    <div>
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h3 className="text-xl font-bold text-white mb-4 sm:mb-0">Additions</h3>
        
        <div>
          <label htmlFor="user-filter" className="mr-2 text-sm text-gray-400">
            Filter by user:
          </label>
          <select
            id="user-filter"
            value={selectedUser}
            onChange={handleUserChange}
            className="bg-dark-300 border border-dark-400 text-white rounded-md px-3 py-1.5 text-sm"
          >
            <option value="all">All Users</option>
            {users.map((user) => (
              <option key={user.id} value={user.id}>
                {user.email}
              </option>
            ))}
          </select>
        </div>
      </div>
      
      {additions.length === 0 ? (
        <div className="text-center py-10 text-gray-400">
          No additions found.
        </div>
      ) : (
        <div className="bg-dark-200 rounded-lg overflow-hidden">
          <table className="min-w-full divide-y divide-dark-300">
            <thead className="bg-dark-300">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Date & Time
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  User
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Action
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Details
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  IP Address
                </th>
              </tr>
            </thead>
            <tbody className="bg-dark-200 divide-y divide-dark-300">
              {additions.map((addition) => (
                <tr key={addition.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {new Date(addition.created_at).toLocaleString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {addition.userEmail || addition.user_email}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-green-900 text-green-200">
                      {addition.action}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-300">
                    {addition.details}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {addition.ip_address}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default Additions;