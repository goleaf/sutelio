import type {
    CalendarDay,
    CalendarTodo,
    CalendarView,
} from '@/components/calendar/calendar-types';

export function parseDateKey(dateKey: string): Date {
    return new Date(`${dateKey}T12:00:00`);
}

export function toDateKey(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function addDays(dateKey: string, amount: number): string {
    const date = parseDateKey(dateKey);
    date.setDate(date.getDate() + amount);

    return toDateKey(date);
}

export function addMonths(dateKey: string, amount: number): string {
    const date = parseDateKey(dateKey);
    const preferredDay = date.getDate();

    date.setDate(1);
    date.setMonth(date.getMonth() + amount);

    const lastDay = new Date(
        date.getFullYear(),
        date.getMonth() + 1,
        0,
    ).getDate();
    date.setDate(Math.min(preferredDay, lastDay));

    return toDateKey(date);
}

export function shiftCalendarAnchor(
    view: CalendarView,
    anchorDate: string,
    direction: -1 | 1,
): string {
    if (view === 'week') {
        return addDays(anchorDate, direction * 7);
    }

    if (view === 'agenda') {
        return addDays(anchorDate, direction * 31);
    }

    return addMonths(anchorDate, direction);
}

export function groupTodosByDate(
    todos: CalendarTodo[],
): Map<string, CalendarTodo[]> {
    const grouped = new Map<string, CalendarTodo[]>();

    for (const todo of todos) {
        if (!todo.due_date) {
            continue;
        }

        grouped.set(todo.due_date, [
            ...(grouped.get(todo.due_date) ?? []),
            todo,
        ]);
    }

    return grouped;
}

export function buildCalendarDays(
    startDate: string,
    endDate: string,
    anchorDate: string,
    todos: CalendarTodo[],
): CalendarDay[] {
    const anchor = parseDateKey(anchorDate);
    const groupedTodos = groupTodosByDate(todos);
    const days: CalendarDay[] = [];

    for (
        let dateKey = startDate;
        dateKey <= endDate;
        dateKey = addDays(dateKey, 1)
    ) {
        const date = parseDateKey(dateKey);
        days.push({
            date,
            dateKey,
            isCurrentMonth:
                date.getFullYear() === anchor.getFullYear() &&
                date.getMonth() === anchor.getMonth(),
            todos: groupedTodos.get(dateKey) ?? [],
        });
    }

    return days;
}
