import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { CheckCircle, AlertCircle, Clock } from 'lucide-react';

const mockInterventions = [
  {
    id: 1,
    name: 'Financial Aid Program',
    description: 'Provides financial assistance to students from low-income families',
    status: 'active',
    success_rate: 78,
    target_factor: 'Economic Issues',
  },
  {
    id: 2,
    name: 'Academic Tutoring',
    description: 'One-on-one tutoring sessions for struggling students',
    status: 'planned',
    success_rate: 85,
    target_factor: 'Academic Performance',
  },
  {
    id: 3,
    name: 'Family Counseling',
    description: 'Professional counseling services for students and families',
    status: 'active',
    success_rate: 72,
    target_factor: 'Family Circumstances',
  },
  {
    id: 4,
    name: 'Mental Health Support',
    description: 'On-campus therapy and stress management workshops',
    status: 'active',
    success_rate: 70,
    target_factor: 'Mental Health',
  },
  {
    id: 5,
    name: 'Career Awareness Workshops',
    description: 'Helps students understand the relevance of education to career goals',
    status: 'completed',
    success_rate: 65,
    target_factor: 'Lack of Motivation',
  },
  {
    id: 6,
    name: 'Transportation Support',
    description: 'Provides subsidized transport for rural students',
    status: 'planned',
    success_rate: 60,
    target_factor: 'Accessibility Issues',
  },
  {
    id: 7,
    name: 'Peer Mentorship Program',
    description: 'Connects new students with senior mentors for guidance',
    status: 'active',
    success_rate: 82,
    target_factor: 'Lack of Social Integration',
  },
  {
    id: 8,
    name: 'Digital Literacy Training',
    description: 'Teaches students essential digital skills for academic success',
    status: 'planned',
    success_rate: 75,
    target_factor: 'Technological Barriers',
  }
];

const StatusBadge = ({ status }) => {
  const styles = {
    active: 'bg-green-100 text-green-800',
    planned: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-blue-100 text-blue-800',
  };

  const icons = {
    active: CheckCircle,
    planned: Clock,
    completed: AlertCircle,
  };

  const Icon = icons[status];

  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${styles[status]}`}>
      <Icon className="w-4 h-4 mr-1" />
      {status.charAt(0).toUpperCase() + status.slice(1)}
    </span>
  );
};

const Interventions = () => {
  const [selectedIntervention, setSelectedIntervention] = useState(null);

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Intervention Recommendations</h1>
        <p className="text-gray-500">View and manage intervention programs</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {mockInterventions.map((intervention) => {
          const isSelected = selectedIntervention?.id === intervention.id;

          return (
            <div
              key={intervention.id}
              className="bg-white p-6 rounded-xl shadow-sm cursor-pointer hover:shadow-md transition-shadow"
              onClick={() =>
                setSelectedIntervention(isSelected ? null : intervention)
              }
            >
              <div className="flex justify-between items-start">
                <h3 className="text-lg font-medium text-gray-900">
                  {intervention.name}
                </h3>
                <StatusBadge status={intervention.status} />
              </div>
              <p className="mt-2 text-gray-500">{intervention.description}</p>
              <div className="mt-4">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-500">Success Rate</span>
                  <span className="font-medium text-gray-900">{intervention.success_rate}%</span>
                </div>
                <div className="mt-1 bg-gray-200 rounded-full h-2">
                  <div
                    className="bg-blue-600 rounded-full h-2"
                    style={{ width: `${intervention.success_rate}%` }}
                  />
                </div>
              </div>
              <div className="mt-4">
                <span className="text-sm text-gray-500">Target Factor:</span>
                <span className="ml-2 text-sm font-medium text-gray-900">
                  {intervention.target_factor}
                </span>
              </div>

              <AnimatePresence>
                {isSelected && (
                  <motion.div
                    className="mt-6 border-t pt-4"
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    exit={{ opacity: 0, height: 0 }}
                    transition={{ duration: 0.3, ease: 'easeInOut' }}
                  >
                    <h4 className="text-md font-medium text-gray-900">Implementation Steps</h4>
                    <ul className="mt-2 space-y-2 list-disc list-inside text-gray-500">
                      <li>Identify target students</li>
                      <li>Assign program coordinators</li>
                      <li>Schedule initial assessments</li>
                      <li>Monitor progress weekly</li>
                    </ul>

                    <h4 className="mt-6 text-md font-medium text-gray-900">Progress Tracking</h4>
                    <div className="mt-4 space-y-4">
                      <div>
                        <div className="flex justify-between text-sm">
                          <span className="text-gray-500">Students Enrolled</span>
                          <span className="font-medium text-gray-900">45/50</span>
                        </div>
                        <div className="mt-1 bg-gray-200 rounded-full h-2">
                          <div
                            className="bg-blue-600 rounded-full h-2"
                            style={{ width: '90%' }}
                          />
                        </div>
                      </div>
                      <div>
                        <div className="flex justify-between text-sm">
                          <span className="text-gray-500">Budget Utilized</span>
                          <span className="font-medium text-gray-900">75%</span>
                        </div>
                        <div className="mt-1 bg-gray-200 rounded-full h-2">
                          <div
                            className="bg-blue-600 rounded-full h-2"
                            style={{ width: '75%' }}
                          />
                        </div>
                      </div>
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default Interventions;
