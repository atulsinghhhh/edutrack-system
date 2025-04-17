import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';

interface Listener {
  id: number;
  name: string;
  photo?: string;
  specialization: string;
  is_available: boolean;
}

interface Conversation {
  id: number;
  listener_id: number;
  problem: string;
  status: string;
}

// Update this URL to match your actual backend setup
const API_BASE_URL = 'http://localhost/drop-rate-main/backend/api';

const TalkToSomeone = () => {
  const { user } = useAuth();
  const [problem, setProblem] = useState('');
  const [listeners, setListeners] = useState<Listener[]>([]);
  const [selectedListener, setSelectedListener] = useState<Listener | null>(null);
  const [isConnecting, setIsConnecting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (user) {
      fetchAvailableListeners();
    }
  }, [user]);

  const fetchAvailableListeners = async () => {
    setIsLoading(true);
    setError(null);
    try {
      const url = `${API_BASE_URL}/listeners.php`;
      console.log('Fetching listeners from:', url);
      
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        credentials: 'include', // Include cookies in the request
      });
      
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);
      
      if (!response.ok) {
        let errorMessage = `HTTP error! status: ${response.status}`;
        try {
          const errorData = await response.json();
          errorMessage = errorData.message || errorMessage;
        } catch (e) {
          console.error('Error parsing error response:', e);
        }
        throw new Error(errorMessage);
      }
      
      const data = await response.json();
      console.log('Received listeners data:', data);
      
      if (Array.isArray(data)) {
        setListeners(data);
        if (data.length === 0) {
          setError('No listeners are currently available. Please try again later.');
        }
      } else {
        console.error('Invalid data format:', data);
        throw new Error('Invalid data format received from server');
      }
    } catch (err) {
      console.error('Error fetching listeners:', err);
      setError(err instanceof Error ? err.message : 'Failed to load available listeners. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user || !selectedListener) return;

    setIsConnecting(true);
    setError(null);
    setSuccess(null);

    try {
      console.log('Creating conversation with data:', {
        user_id: user.id,
        listener_id: selectedListener.id,
        problem: problem,
      });

      const response = await fetch(`${API_BASE_URL}/conversations.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: user.id,
          listener_id: selectedListener.id,
          problem: problem,
        }),
      });

      const data = await response.json();
      console.log('Server response:', data);

      if (!response.ok) {
        throw new Error(data.message || `HTTP error! status: ${response.status}`);
      }

      // Clear form and show success message
      setSelectedListener(null);
      setProblem('');
      setSuccess('Successfully connected with listener!');
      
      // Refresh available listeners
      fetchAvailableListeners();
    } catch (err) {
      console.error('Error creating conversation:', err);
      setError(err instanceof Error ? err.message : 'Failed to connect with listener. Please try again.');
    } finally {
      setIsConnecting(false);
    }
  };

  if (!user) {
    return null;
  }

  return (
    <div className="max-w-4xl mx-auto p-6">
      <div className="bg-gradient-to-br from-pink-50 to-purple-50 rounded-2xl p-8 shadow-lg">
        <h2 className="text-3xl font-semibold text-gray-800 mb-4 font-comfortaa">
          Talk to Someone
        </h2>
        <p className="text-gray-600 mb-6">
          We're here to listen and support you. Share what's on your mind, and we'll connect you with a caring listener.
        </p>

        {error && (
          <div className="mb-4 p-4 bg-red-50 text-red-600 rounded-lg">
            {error}
            <button 
              onClick={fetchAvailableListeners}
              className="mt-2 text-sm text-red-600 hover:text-red-800 underline"
            >
              Try Again
            </button>
          </div>
        )}

        {success && (
          <div className="mb-4 p-4 bg-green-50 text-green-600 rounded-lg">
            {success}
            <button 
              onClick={() => {
                setSuccess(null);
                fetchAvailableListeners();
              }}
              className="mt-2 text-sm text-green-600 hover:text-green-800 underline"
            >
              Start New Conversation
            </button>
          </div>
        )}

        {isLoading ? (
          <div className="flex justify-center items-center py-8">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label htmlFor="problem" className="block text-sm font-medium text-gray-700 mb-1">
                What would you like to talk about?
              </label>
              <textarea
                id="problem"
                value={problem}
                onChange={(e) => setProblem(e.target.value)}
                className="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-200 focus:border-purple-400 transition-all duration-200"
                rows={4}
                placeholder="Share your thoughts here..."
                required
              />
            </div>

            {!selectedListener && (
              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Available Listeners
                </label>
                {listeners.length === 0 ? (
                  <div className="text-center py-4 text-gray-500">
                    No listeners are currently available. Please check back later.
                  </div>
                ) : (
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {listeners.map((listener) => (
                      <div
                        key={listener.id}
                        onClick={() => setSelectedListener(listener)}
                        className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                      >
                        <div className="flex items-center space-x-3">
                          {listener.photo && (
                            <img
                              src={listener.photo}
                              alt={listener.name}
                              className="w-12 h-12 rounded-full object-cover"
                            />
                          )}
                          <div>
                            <h3 className="font-medium text-gray-800">{listener.name}</h3>
                            <p className="text-sm text-purple-600">{listener.specialization}</p>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}

            {selectedListener && (
              <div className="mt-4 bg-white rounded-xl p-6 shadow-md">
                <div className="flex items-center space-x-4">
                  {selectedListener.photo && (
                    <img
                      src={selectedListener.photo}
                      alt={selectedListener.name}
                      className="w-16 h-16 rounded-full object-cover"
                    />
                  )}
                  <div>
                    <h3 className="text-xl font-semibold text-gray-800">{selectedListener.name}</h3>
                    <p className="text-purple-600">{selectedListener.specialization}</p>
                    <div className="flex items-center mt-1">
                      <span className="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                      <span className="text-sm text-gray-600">Available now</span>
                    </div>
                  </div>
                </div>
              </div>
            )}

            <button
              type="submit"
              disabled={isConnecting || !selectedListener}
              className="w-full bg-gradient-to-r from-purple-400 to-pink-400 text-white py-3 px-6 rounded-lg font-medium hover:from-purple-500 hover:to-pink-500 transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isConnecting ? 'Connecting...' : 'Connect with a Listener'}
            </button>
          </form>
        )}
      </div>
    </div>
  );
};

export default TalkToSomeone; 