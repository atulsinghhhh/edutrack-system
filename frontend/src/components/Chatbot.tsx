import React, { useState, useRef, useEffect } from 'react';
import { MessageSquare, X } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { useAuth } from '../context/AuthContext';

interface Message {
  id: number;
  text: string;
  sender_id: number;
  sender_name: string;
  created_at: string;
}

interface Conversation {
  id: number;
  listener_id: number;
  listener_name: string;
  problem: string;
  status: string;
}

const API_BASE_URL = 'http://localhost/drop-rate-main/backend/api';

const Chatbot: React.FC = () => {
  const { user } = useAuth();
  const [messages, setMessages] = useState<Message[]>([]);
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [activeConversation, setActiveConversation] = useState<Conversation | null>(null);
  const [inputMessage, setInputMessage] = useState('');
  const [isOpen, setIsOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  useEffect(() => {
    if (user && isOpen) {
      fetchConversations();
    }
  }, [user, isOpen]);

  useEffect(() => {
    if (activeConversation) {
      fetchMessages();
      // Set up polling for new messages
      const interval = setInterval(fetchMessages, 5000);
      return () => clearInterval(interval);
    }
  }, [activeConversation]);

  const fetchConversations = async () => {
    try {
      console.log('Fetching conversations for user:', user?.id);
      const response = await fetch(`${API_BASE_URL}/conversations.php?user_id=${user?.id}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        credentials: 'include',
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to fetch conversations');
      }

      const data = await response.json();
      console.log('Received conversations:', data);
      setConversations(data);
      
      // If there's an active conversation, set it
      if (data.length > 0 && !activeConversation) {
        setActiveConversation(data[0]);
      }
    } catch (err) {
      console.error('Error fetching conversations:', err);
      setError(err instanceof Error ? err.message : 'Failed to load conversations');
    }
  };

  const fetchMessages = async () => {
    if (!activeConversation) return;

    try {
      console.log('Fetching messages for conversation:', activeConversation.id);
      const response = await fetch(`${API_BASE_URL}/messages.php?conversation_id=${activeConversation.id}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        credentials: 'include',
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to fetch messages');
      }

      const data = await response.json();
      console.log('Received messages:', data);
      setMessages(data);
    } catch (err) {
      console.error('Error fetching messages:', err);
      setError(err instanceof Error ? err.message : 'Failed to load messages');
    }
  };

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!inputMessage.trim() || !activeConversation || !user) return;

    setIsLoading(true);
    setError(null);

    try {
      console.log('Sending message:', {
        conversation_id: activeConversation.id,
        sender_id: user.id,
        message: inputMessage,
      });

      const response = await fetch(`${API_BASE_URL}/messages.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          conversation_id: activeConversation.id,
          sender_id: user.id,
          message: inputMessage,
        }),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to send message');
      }

      const data = await response.json();
      console.log('Message sent successfully:', data);

      setInputMessage('');
      // Wait a moment for the auto-reply to be processed
      setTimeout(fetchMessages, 1000);
    } catch (err) {
      console.error('Error sending message:', err);
      setError(err instanceof Error ? err.message : 'Failed to send message');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      {!isOpen && (
        <motion.button
          onClick={() => setIsOpen(true)}
          className="fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 transition"
          whileTap={{ scale: 0.9 }}
        >
          <MessageSquare className="w-6 h-6" />
        </motion.button>
      )}

      <AnimatePresence>
        {isOpen && (
          <motion.div
            className="fixed bottom-4 right-4 w-96 h-[600px] bg-zinc-900 text-white shadow-xl rounded-xl flex flex-col overflow-hidden z-50"
            initial={{ opacity: 0, y: 50 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 50 }}
            transition={{ duration: 0.3 }}
          >
            {/* Header */}
            <div className="px-5 py-4 border-b border-zinc-700 flex justify-between items-center bg-zinc-800">
              <h2 className="text-lg font-semibold text-white">
                {activeConversation ? `Chat with ${activeConversation.listener_name}` : 'Your Conversations'}
              </h2>
              <button
                onClick={() => setIsOpen(false)}
                className="text-zinc-400 hover:text-red-400"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Conversations List */}
            {!activeConversation && (
              <div className="flex-1 overflow-y-auto p-4 space-y-2 bg-zinc-900">
                {conversations.map((conv) => (
                  <button
                    key={conv.id}
                    onClick={() => setActiveConversation(conv)}
                    className="w-full p-3 bg-zinc-800 rounded-lg hover:bg-zinc-700 text-left"
                  >
                    <h3 className="font-medium">{conv.listener_name}</h3>
                    <p className="text-sm text-zinc-400 truncate">{conv.problem}</p>
                  </button>
                ))}
                {conversations.length === 0 && (
                  <p className="text-center text-zinc-400 py-4">No active conversations</p>
                )}
              </div>
            )}

            {/* Messages */}
            {activeConversation && (
              <>
                <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-zinc-900">
                  {error && (
                    <div className="bg-red-500 text-white p-3 rounded-lg text-sm">
                      {error}
                    </div>
                  )}
                  {messages.map((msg) => (
                    <motion.div
                      key={msg.id}
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      className={`flex ${
                        msg.sender_id === user?.id ? 'justify-end' : 'justify-start'
                      }`}
                    >
                      <div
                        className={`rounded-lg px-4 py-2 max-w-[75%] text-sm shadow ${
                          msg.sender_id === user?.id
                            ? 'bg-blue-600 text-white'
                            : 'bg-zinc-800 border border-zinc-700 text-gray-200'
                        }`}
                      >
                        <p className="whitespace-pre-wrap">{msg.text}</p>
                        <span className="block mt-1 text-[10px] text-right text-gray-400">
                          {new Date(msg.created_at).toLocaleTimeString()}
                        </span>
                      </div>
                    </motion.div>
                  ))}
                  {isLoading && (
                    <div className="flex justify-start">
                      <div className="bg-zinc-800 border border-zinc-700 px-4 py-2 rounded-lg shadow text-sm text-gray-300">
                        Sending...
                      </div>
                    </div>
                  )}
                  <div ref={messagesEndRef} />
                </div>

                {/* Input */}
                <form onSubmit={handleSendMessage} className="p-3 bg-zinc-800 border-t border-zinc-700">
                  <div className="flex space-x-2">
                    <input
                      type="text"
                      value={inputMessage}
                      onChange={(e) => setInputMessage(e.target.value)}
                      placeholder="Type your message..."
                      className="flex-1 px-4 py-2 bg-zinc-700 border border-zinc-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                      disabled={isLoading}
                    />
                    <button
                      type="submit"
                      className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                      disabled={isLoading}
                    >
                      Send
                    </button>
                  </div>
                </form>
              </>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
};

export default Chatbot;
