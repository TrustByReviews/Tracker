export interface Project {
    id: string;
    name: string;
    description: string;
    status: string;
    created_by: string;
    created_at: string;
    updated_at: string;
    users?: User[];
    sprints?: Sprint[];
    creator?: User;
    
    // Phase 1: Essential fields
    objectives?: string;
    priority?: string;
    category?: string;
    development_type?: string;
    planned_start_date?: string;
    planned_end_date?: string;
    actual_start_date?: string;
    actual_end_date?: string;
    methodology?: string;
    
    // Phase 3: Advanced fields
    technologies?: string[];
    programming_languages?: string[];
    frameworks?: string[];
    database_type?: string;
    architecture?: string;
    external_integrations?: string[];
    project_owner?: string;
    product_owner?: string;
    stakeholders?: string[];
    milestones?: string[];
    estimated_velocity?: number;
    current_sprint?: string;
    estimated_budget?: number;
    used_budget?: number;
    assigned_resources?: string[];
    progress_percentage?: number;
    identified_risks?: string[];
    open_issues?: number;
    documentation_url?: string;
    repository_url?: string;
    task_board_url?: string;
}

export interface User {
    id: string;
    name: string;
    email: string;
    avatar?: string;
    status: string;
    work_time: string;
    hour_value: number;
}

export interface Sprint {
    id: string;
    name: string;
    goal: string;
    start_date: string;
    end_date: string;
    project_id: string;
    created_at?: string;
    updated_at?: string;
    tasks?: Task[];
    bugs?: any[];
    project?: {
        id: string;
        name: string;
    };
    
    // Fase 1: Campos esenciales
    description?: string;
    sprint_type?: string;
    planned_start_date?: string;
    planned_end_date?: string;
    actual_start_date?: string;
    actual_end_date?: string;
    duration_days?: number;
    sprint_objective?: string;
    user_stories_included?: string[];
    assigned_tasks?: string[];
    acceptance_criteria?: string;
    
    // Fase 2: Campos de seguimiento avanzado
    planned_velocity?: number;
    actual_velocity?: number;
    velocity_deviation?: number;
    progress_percentage?: number;
    blockers?: string[];
    risks?: string[];
    blocker_resolution_notes?: string;
    detailed_acceptance_criteria?: string[];
    definition_of_done?: string[];
    quality_gates?: string[];
    bugs_found?: number;
    bugs_resolved?: number;
    bug_resolution_rate?: number;
    code_reviews_completed?: number;
    code_reviews_pending?: number;
    daily_scrums_held?: number;
    daily_scrums_missed?: number;
    daily_scrum_attendance_rate?: number;
    
    // Fase 3: Campos de retrospectiva y mejoras
    isCompleted?: boolean;
    hasRetrospective?: boolean;
    achievements?: string[];
    problems?: string[];
    actions_to_take?: string[];
    retrospective_notes?: string;
    lessons_learned?: string[];
    improvement_areas?: string[];
    team_feedback?: string[];
    stakeholder_feedback?: string[];
    team_satisfaction_score?: number;
    stakeholder_satisfaction_score?: number;
    process_improvements?: string[];
    tool_improvements?: string[];
    communication_improvements?: string[];
    technical_debt_added?: string[];
    technical_debt_resolved?: string[];
    knowledge_shared?: string[];
    skills_developed?: string[];
    mentoring_sessions?: string[];
    sprint_goals_achieved?: string[];
    sprint_goals_partially_achieved?: string[];
    sprint_goals_not_achieved?: string[];
    sprint_ceremony_effectiveness?: string[];
}

export interface Task {
    id: string;
    name: string;
    description: string;
    status: string;
    priority: string;
    category: string;
    story_points: number;
    estimated_hours?: number;
    actual_hours?: number;
    estimated_start?: string | null;
    estimated_finish?: string | null;
    actual_start?: string | null;
    actual_finish?: string | null;
    user_id?: string | null;
    sprint_id?: string;
    project_id?: string;
    user?: User;
    sprint?: Sprint;
    project?: {
        id: string;
        name: string;
    };
}

export interface Bug {
    id: string;
    title: string;
    description: string;
    status: string;
    priority: string;
    severity: string;
    category: string;
    reported_by?: string;
    assigned_to?: string;
    sprint_id?: string;
    project_id?: string;
    created_at?: string;
    updated_at?: string;
    resolved_at?: string;
    resolution_notes?: string;
    steps_to_reproduce?: string;
    expected_behavior?: string;
    actual_behavior?: string;
    environment?: string;
    browser?: string;
    os?: string;
    attachments?: string[];
    tags?: string[];
    user?: User;
    sprint?: Sprint;
    project?: {
        id: string;
        name: string;
    };
}
