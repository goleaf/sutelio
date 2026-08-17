<template>
    <div
        data-slot="workspace-page-frame"
        class="ui-page-frame min-h-full bg-muted/20"
    >
        <div data-slot="workspace-page-container" class="ui-page-container">
            <slot />
        </div>
    </div>
</template>
