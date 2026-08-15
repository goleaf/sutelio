export function isTodoOverdue(
    dueDate: string | null,
    isCompleted: boolean,
    now: Date = new Date(),
): boolean {
    if (isCompleted || !dueDate) {
        return false;
    }

    const endOfDueDate = new Date(`${dueDate}T23:59:59.999`);

    return endOfDueDate.getTime() < now.getTime();
}
