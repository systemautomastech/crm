import React from 'react';
import { AreaChart as RechartsAreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface AreaChartProps {
  data: any[];
  dataKey: string;
  xAxisKey: string;
  title?: string;
  color?: string;
  type?: 'monotone' | 'linear' | 'step' | 'stepBefore' | 'stepAfter';
  stacked?: boolean;
  showLegend?: boolean;
  showGrid?: boolean;
  showTooltip?: boolean;
  gradient?: boolean;
  height?: number;
  areas?: Array<{
    dataKey: string;
    color: string;
    name?: string;
  }>;
  yAxisWidth?: number;
}

export const AreaChart: React.FC<AreaChartProps> = ({
  data,
  dataKey,
  xAxisKey,
  color = '#3b82f6',
  type = 'monotone',
  stacked = false,
  showLegend = false,
  showGrid = true,
  showTooltip = true,
  gradient = false,
  height = 350,
  areas = [],
  yAxisWidth
}) => {
  const getMaxValue = () => {
    let max = 0;
    data.forEach((item: any) => {
      if (areas.length > 0) {
        areas.forEach((area: any) => {
          const val = parseFloat(item[area.dataKey]) || 0;
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
      <RechartsAreaChart data={data} margin={{ left: leftMargin, right: 20, top: 10, bottom: 10 }}>
        {gradient && (
          <defs>
            <linearGradient id="fillDesktop" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor={color} stopOpacity={0.8}/>
              <stop offset="95%" stopColor={color} stopOpacity={0.1}/>
            </linearGradient>
          </defs>
        )}
        {showGrid && <CartesianGrid vertical={false} />}
        <XAxis dataKey={xAxisKey} tickLine={false} axisLine={false} tickMargin={8} height={45} />
        <YAxis tickLine={false} axisLine={false} tickMargin={8} width={finalYAxisWidth} />
        {showTooltip && <Tooltip />}
        {showLegend && <Legend />}
        {areas.length > 0 ? areas.map((area) => (
          <Area
            key={area.dataKey}
            type={type}
            dataKey={area.dataKey}
            stackId={stacked ? "1" : undefined}
            stroke={area.color}
            fill={gradient ? "url(#fillDesktop)" : area.color}
            fillOpacity={0.4}
          />
        )) : (
          <Area
            type={type}
            dataKey={dataKey}
            stackId={stacked ? "1" : undefined}
            stroke={color}
            fill={gradient ? "url(#fillDesktop)" : color}
            fillOpacity={0.4}
          />
        )}
      </RechartsAreaChart>
    </ResponsiveContainer>
  );
};
