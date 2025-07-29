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
