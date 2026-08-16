(() => {
    const root = document.documentElement;

    if (
        root.dataset.appearance === 'system' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
    ) {
        root.classList.add('dark');
    }
})();
