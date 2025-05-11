import React, { useState, useEffect } from 'react';
import { userApi } from '../../utils/api';

const ActivityLogs = () => {
  const [users, setUsers] = useState([]);
  const [selectedUser, setSelectedUser] = useState('all');
  const [activities, setActivities] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [userActivitiesMap, setUserActivitiesMap] = useState({});

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    try {
      const data = await userApi.getUsers();
      setUsers(data);
      
      // Fetch activities for all users
      const allActivities = [];
      const activitiesMap = {};
      
      setLoading(true);
      for (const user of data) {
        try {
          const userActivities = await userApi.getUserActivity(user.id);
          allActivities.push(...userActivities.map(activity => ({
            ...activity,
            userEmail: user.email
          })));
          
          activitiesMap[user.id] = userActivities;
        } catch (error) {
          console.error(`Failed to fetch activities for user ${user.id}:`, error);
        }
      }
      
      // Sort activities by creation date (newest first)
      allActivities.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      setActivities(allActivities);
      setUserActivitiesMap(activitiesMap);
    } catch (err) {
      console.error('Error fetching users:', err);
      setError('Failed to load users and activities. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleUserChange = (e) => {
    const userId = e.target.value;
    setSelectedUser(userId);
    
    if (userId === 'all') {
      // Show activities for all users
      const allActivities = [];
      for (const user of users) {
        if (userActivitiesMap[user.id]) {
          allActivities.push(...userActivitiesMap[user.id].map(activity => ({
            ...activity,
            userEmail: user.email
          })));
        }
      }
      // Sort activities by creation date (newest first)
      allActivities.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      setActivities(allActivities);
    } else {
      // Show activities for selected user
      const userActivities = userActivitiesMap[userId] || [];
      const selectedUserEmail = users.find(user => user.id.toString() === userId)?.email || '';
      
      setActivities(userActivities.map(activity => ({
        ...activity,
        userEmail: selectedUserEmail
      })));
    }
  };

  if (loading) {
    return (
      <div className="text-center py-10">
        <svg className="animate-spin h-12 w-12 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p className="mt-4 text-lg text-gray-400">Loading activity logs...</p>
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
        <h3 className="text-xl font-bold text-white mb-4 sm:mb-0">Activity Logs</h3>
        
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
      
      {activities.length === 0 ? (
        <div className="text-center py-10 text-gray-400">
          No activity logs found.
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
              {activities.map((activity) => (
                <tr key={activity.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {new Date(activity.created_at).toLocaleString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {activity.userEmail}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    <span className={`px-2 py-1 text-xs font-medium rounded-full 
                      ${activity.action.includes('search') ? 'bg-blue-900 text-blue-200' : 
                      activity.action.includes('add') ? 'bg-green-900 text-green-200' : 
                      activity.action.includes('login') ? 'bg-purple-900 text-purple-200' : 
                      activity.action.includes('register') ? 'bg-yellow-900 text-yellow-200' : 
                      'bg-gray-700 text-gray-200'}`}
                    >
                      {activity.action}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-300">
                    {activity.details}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {activity.ip_address}
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

export default ActivityLogs;
