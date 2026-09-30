export interface HealthScore {
    id: number;
    score: number;
    financial_score: number;
    team_score: number;
    sales_score: number;
    project_score: number;
    operations_score: number;
    trend: 'improving' | 'stable' | 'declining';
    analysis_date: string;
}

export interface Insight {
    id: number;
    title: string;
    description: string;
    severity: 'positive' | 'info' | 'warning' | 'critical';
    module: string;
    is_dismissed: boolean;
}

export interface Recommendation {
    id: number;
    recommendation: string;
    reason: string;
    priority: 'high' | 'medium' | 'low';
    related_module: string;
    action_label: string | null;
    action_url: string | null;
    status: 'pending' | 'done' | 'dismissed';
}

export interface Alert {
    id: number;
    title: string;
    message: string;
    severity: 'warning' | 'critical';
    module: string;
    is_resolved: boolean;
}

export interface ScoreHistoryItem {
    score: number;
    analysis_date: string;
    trend: string;
}

export interface DashboardProps {
    [key: string]: any;
    health_score: HealthScore | null;
    insights: Insight[];
    recommendations: Recommendation[];
    alerts: Alert[];
    score_history: ScoreHistoryItem[];
    analysis_date: string | null;
    has_today_data: boolean;
    aiAdvisorEnabled: boolean;
    selected_date?: string;
}
