import React, { useState } from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';

const mockFactors = [
  { id: 1, factor: 'Economic Issues', impact: 35, correlation: 0.85 },
  { id: 2, factor: 'Academic Performance', impact: 25, correlation: 0.78 },
  { id: 3, factor: 'Family Circumstances', impact: 20, correlation: 0.72 },
  { id: 4, factor: 'Social Factors', impact: 12, correlation: 0.65 },
  { id: 5, factor: 'Health Issues', impact: 8, correlation: 0.58 },
];

const COLORS = ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'];

const DropoutFactors = () => {
  const [selectedFactor, setSelectedFactor] = useState(null);

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dropout Factors Analysis</h1>
        <p className="text-gray-500">Analyze key factors contributing to student dropouts</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div className="bg-white p-6 rounded-xl shadow-sm">
          <h2 className="text-lg font-semibold mb-4">Factor Distribution</h2>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={mockFactors}
                dataKey="impact"
                nameKey="factor"
                cx="50%"
                cy="50%"
                outerRadius={100}
                fill="#8884d8"
                label
              >
                {mockFactors.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white p-6 rounded-xl shadow-sm">
          <h2 className="text-lg font-semibold mb-4">Factors Table</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Factor
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Impact %
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Correlation
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {mockFactors.map((factor) => (
                  <tr
                    key={factor.id}
                    className="hover:bg-gray-50 cursor-pointer"
                    onClick={() => setSelectedFactor(factor)}
                  >
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {factor.factor}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {factor.impact}%
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {factor.correlation}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {selectedFactor && (
        <div className="bg-white p-6 rounded-xl shadow-sm">
          <h2 className="text-lg font-semibold mb-4">Factor Details</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 className="text-md font-medium text-gray-900">{selectedFactor.factor}</h3>
              <p className="text-gray-500 mt-2">
                Detailed analysis and description of the selected factor would go here,
                including specific insights and recommendations for addressing this factor.
              </p>
            </div>
            <div>
              <h3 className="text-md font-medium text-gray-900">Statistical Insights</h3>
              <ul className="mt-2 space-y-2">
                <li className="text-gray-500">Impact Percentage: {selectedFactor.impact}%</li>
                <li className="text-gray-500">Correlation: {selectedFactor.correlation}</li>
                <li className="text-gray-500">Trend: Increasing over past 3 months</li>
              </ul>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default DropoutFactors;