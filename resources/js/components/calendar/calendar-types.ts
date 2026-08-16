import type { Project, Todo } from '@/types/models';

export type CalendarView = 'month' | 'week' | 'agenda';

export type CalendarState = {
    view: CalendarView;
    anchor_date: string;
    today_date: string;
    start_date: string;
    end_date: string;
    week_start: 'sunday' | 'monday';
};

export type CalendarTodo = Pick<
    Todo,
    'id' | 'title' | 'status' | 'priority' | 'due_date' | 'is_completed'
> & {
    status_definition: Todo['status_definition'] | null;
    priority_definition: Todo['priority_definition'] | null;
    project: Pick<Project, 'id' | 'name' | 'color'> | null;
};

export type CalendarDay = {
    date: Date;
    dateKey: string;
    isCurrentMonth: boolean;
    todos: CalendarTodo[];
};
