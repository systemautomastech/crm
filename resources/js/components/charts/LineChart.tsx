import React from 'react';
import { LineChart as RechartsLineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface LineChartProps {
  data: any[];
  dataKey: string;
  xAxisKey: string;
  color?: string;
  type?: 'monotone' | 'linear' | 'step' | 'stepBefore' | 'stepAfter';
  showLegend?: boolean;
  showGrid?: boolean;
  showTooltip?: boolean;
  showDots?: boolean;
  height?: number;
  lines?: Array<{
    dataKey: string;
    color: string;
    name?: string;
    type?: 'monotone' | 'linear' | 'step' | 'stepBefore' | 'stepAfter';
  }>;
  customDots?: boolean;
  strokeWidth?: number;
  yAxisWidth?: number;
}

export const LineChart: React.FC<LineChartProps> = ({
  data,
  dataKey,
  xAxisKey,
  color = '#3b82f6',
  type = 'monotone',
  showLegend = false,
  showGrid = true,
  showTooltip = true,
  showDots = false,
  height = 350,
  lines = [],
  customDots = false,
  strokeWidth = 2,
  yAxisWidth
}) => {
  const getMaxValue = () => {
    let max = 0;
    data.forEach((item: any) => {
      if (lines.length > 0) {
        lines.forEach((line: any) => {
          const val = parseFloat(item[line.dataKey]) || 0;
          max = Math.max(max, val);
        });
      } else {
        const val = parseFloat(item[dataKey]) || 0;
        max = Math.max(max, val);
      }
    });
    return max;
  };

  const maxValue = getMaxValue();
  const maxValueStr = maxValue.toLocaleString();
  
  // Calculate dynamic width based on actual string length of formatted value
  // If value string is longer than 6 characters (e.g., "100,000"), add extra space
  let dynamicYAxisWidth = 30;
  if (maxValueStr.length > 6) {
    dynamicYAxisWidth = 15 + (maxValueStr.length - 6) * 5;
    dynamicYAxisWidth = Math.min(dynamicYAxisWidth, 85);
  }
  
  const finalYAxisWidth = yAxisWidth || dynamicYAxisWidth;
  const leftMargin = finalYAxisWidth;

  return (
    <ResponsiveContainer width="100%" height={height}>
      <RechartsLineChart data={data} margin={{ left: leftMargin, right: 20, top: 10, bottom: 10 }}>
        {showGrid && <CartesianGrid vertical={false} />}
        <XAxis dataKey={xAxisKey} tickLine={false} axisLine={false} tickMargin={8} height={45} />
        <YAxis tickLine={false} axisLine={false} tickMargin={8} width={finalYAxisWidth} />
        {showTooltip && <Tooltip />}
        {showLegend && <Legend />}
        {lines.length > 0 ? lines.map((line) => (
          <Line
            name ={line.name || line.dataKey}
            key={line.dataKey}
            type={line.type || type}
            dataKey={line.dataKey}
            stroke={line.color}
            strokeWidth={strokeWidth}
            dot={showDots}
          />
        )) : (
          <Line
            type={type}
            dataKey={dataKey}
            stroke={color}
            strokeWidth={strokeWidth}
            dot={showDots}
          />
        )}
      </RechartsLineChart>
    </ResponsiveContainer>
  );
};
