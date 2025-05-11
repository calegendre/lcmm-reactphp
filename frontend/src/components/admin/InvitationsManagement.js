import React, { useState, useEffect } from 'react';
import { toast } from 'react-hot-toast';
import { invitationApi } from '../../utils/api';

const InvitationsManagement = () => {
  const [invitations, setInvitations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [newInviteEmail, setNewInviteEmail] = useState('');
  const [creatingInvite, setCreatingInvite] = useState(false);

  useEffect(() => {
    fetchInvitations();
  }, []);

  const fetchInvitations = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await invitationApi.getInvitations();
      setInvitations(data);
    } catch (err) {
      console.error('Error fetching invitations:', err);
      setError('Failed to load invitations. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleCreateInvitation = async (e) => {
    e.preventDefault();
    setCreatingInvite(true);
    
    try {
      const invitation = await invitationApi.createInvitation(newInviteEmail || null);
      setInvitations([invitation, ...invitations]);
      setNewInviteEmail('');
      toast.success('Invitation created successfully');
    } catch (err) {
      console.error('Error creating invitation:', err);
      toast.error('Failed to create invitation');
    } finally {
      setCreatingInvite(false);
    }
  };

  const handleDeleteInvitation = async (invitationId) => {
    try {
      await invitationApi.deleteInvitation(invitationId);
      setInvitations(invitations.filter(invite => invite.id !== invitationId));
      toast.success('Invitation deleted successfully');
    } catch (err) {
      console.error('Error deleting invitation:', err);
      toast.error('Failed to delete invitation');
    }
  };

  const copyInvitationCode = (code) => {
    navigator.clipboard.writeText(code)
      .then(() => toast.success('Invitation code copied to clipboard'))
      .catch(() => toast.error('Failed to copy code'));
  };

  if (loading) {
    return (
      <div className="text-center py-10">
        <svg className="animate-spin h-12 w-12 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p className="mt-4 text-lg text-gray-400">Loading invitations...</p>
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
      <h3 className="text-xl font-bold text-white mb-6">Invitation Management</h3>
      
      <div className="bg-dark-100 rounded-lg border border-dark-300 p-6 mb-8">
        <h4 className="text-lg font-medium text-white mb-4">Create New Invitation</h4>
        <form onSubmit={handleCreateInvitation} className="flex flex-col sm:flex-row gap-4">
          <div className="flex-grow">
            <input
              type="email"
              value={newInviteEmail}
              onChange={(e) => setNewInviteEmail(e.target.value)}
              placeholder="Email address (optional)"
              className="input w-full"
            />
            <p className="text-xs text-gray-400 mt-1">
              If you provide an email, it will be associated with this invitation code.
            </p>
          </div>
          <button
            type="submit"
            disabled={creatingInvite}
            className={`btn-primary whitespace-nowrap ${creatingInvite ? 'opacity-70 cursor-not-allowed' : ''}`}
          >
            {creatingInvite ? (
              <>
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating...
              </>
            ) : (
              'Create Invitation'
            )}
          </button>
        </form>
      </div>
      
      {invitations.length === 0 ? (
        <div className="text-center py-10 text-gray-400">
          No invitations found. Create one using the form above.
        </div>
      ) : (
        <div className="bg-dark-200 rounded-lg overflow-hidden">
          <table className="min-w-full divide-y divide-dark-300">
            <thead className="bg-dark-300">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Code
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Email
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Created By
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Created At
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-dark-200 divide-y divide-dark-300">
              {invitations.map((invitation) => (
                <tr key={invitation.id}>
                  <td className="px-6 py-4 text-sm">
                    <div className="flex items-center">
                      <span className="text-gray-300 font-mono">{invitation.code}</span>
                      <button 
                        onClick={() => copyInvitationCode(invitation.code)}
                        className="ml-2 text-gray-400 hover:text-gray-300"
                        title="Copy code"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-4 h-4">
                          <path strokeLinecap="round" strokeLinejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                        </svg>
                      </button>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {invitation.email || 'N/A'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {invitation.created_by_email}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {invitation.used ? (
                      <span className="px-2 py-1 text-xs font-medium rounded-full bg-green-900 text-green-200">
                        Used by {invitation.used_by_email}
                      </span>
                    ) : (
                      <span className="px-2 py-1 text-xs font-medium rounded-full bg-blue-900 text-blue-200">
                        Available
                      </span>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {new Date(invitation.created_at).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {!invitation.used && (
                      <button
                        onClick={() => handleDeleteInvitation(invitation.id)}
                        className="text-red-500 hover:text-red-400"
                      >
                        Delete
                      </button>
                    )}
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

export default InvitationsManagement;
