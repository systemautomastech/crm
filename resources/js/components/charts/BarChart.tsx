import React from 'react';
import { BarChart as RechartsBarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, Cell } from 'recharts';

interface BarChartProps {
  data: any[];
  dataKey: string;
  xAxisKey: string;
  color?: string;
  horizontal?: boolean;
  stacked?: boolean;
  showLegend?: boolean;
  showGrid?: boolean;
  showTooltip?: boolean;
  height?: number;
  bars?: Array<{
    dataKey: string;
    color: string;
    name?: string;
  }>;
  activeIndex?: number;
  negative?: boolean;
  yAxisWidth?: number;
}

export const BarChart: React.FC<BarChartProps> = ({
  data,
  dataKey,
  xAxisKey,
  color = '#3b82f6',
  horizontal = false,
  stacked = false,
  showLegend = false,
  showGrid = true,
  showTooltip = true,
  height = 350,
  bars = [],
  activeIndex,
  negative = false,
  yAxisWidth
}) => {
  const getMaxValue = () => {
    let max = 0;
    data.forEach((item: any) => {
      if (bars.length > 0) {
        bars.forEach((bar: any) => {
          const val = parseFloat(item[bar.dataKey]) || 0;
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
  let dynamicYAxisWidth = 40;
  if (!horizontal && maxValueStr.length > 6) {
    dynamicYAxisWidth = 25 + (maxValueStr.length - 6) * 5;
    dynamicYAxisWidth = Math.min(dynamicYAxisWidth, 85);
  }
  
  const finalYAxisWidth = yAxisWidth || dynamicYAxisWidth;
  
  let chartMargin: any;
  if (horizontal) {
    chartMargin = { left: 80, right: 12, top: 10, bottom: 10 };
  } else {
    chartMargin = { left: finalYAxisWidth, right: 12 };
  }

  const layout = horizontal ? { layout: 'horizontal' as const } : {};

  return (
    <ResponsiveContainer width="100%" height={height}>
      <RechartsBarChart data={data} margin={chartMargin} {...layout}>
        {showGrid && <CartesianGrid vertical={false} />}
        {horizontal ? (
          <>
            <XAxis type="number" domain={negative ? ['dataMin', 'dataMax'] : [0, 'dataMax']} tickLine={false} axisLine={false} />
            <YAxis type="category" dataKey={xAxisKey} tickLine={false} axisLine={false} width={70} />
          </>
        ) : (
          <>
            <XAxis dataKey={xAxisKey} tickLine={false} axisLine={false} tickMargin={8} height={45} />
            <YAxis domain={negative ? ['dataMin', 'dataMax'] : [0, 'dataMax']} tickLine={false} axisLine={false} tickMargin={8} width={finalYAxisWidth} />
          </>
        )}
        {showTooltip && <Tooltip />}
        {showLegend && <Legend />}
        {bars.length > 0 ? bars.map((bar) => (
          <Bar
            key={bar.dataKey}
            dataKey={bar.dataKey}
            stackId={stacked ? "1" : undefined}
            fill={bar.color}
            radius={4}
          />
        )) : (
          <Bar dataKey={dataKey} fill={color} radius={4}>
            {activeIndex !== undefined && data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={index === activeIndex ? '#10b981' : color} />
            ))}
          </Bar>
        )}
      </RechartsBarChart>
    </ResponsiveContainer>
  );
};
