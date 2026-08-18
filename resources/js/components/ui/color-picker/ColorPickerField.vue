<script setup lang="ts">
import * as colorPicker from '@zag-js/color-picker';
import { Check } from '@lucide/vue';
import { normalizeProps, useMachine } from '@zag-js/vue';
import { computed, onMounted, ref } from 'vue';
import { useUi } from '@/composables/useUi';

const props = withDefaults(
    defineProps<{
        id: string;
        disabled?: boolean;
        invalid?: boolean;
        presets?: readonly string[];
    }>(),
    {
        disabled: false,
        invalid: false,
        presets: () => [],
    },
);

const color = defineModel<string>({ required: true });
const { t } = useUi();
const hexPattern = /^#[0-9a-f]{6}$/i;

function normalizedHex(value: string): string {
    return hexPattern.test(value) ? value.toLowerCase() : '#000000';
}

const service = useMachine(colorPicker.machine, {
    id: props.id,
    ids: {
        trigger: props.id,
    },
    get value() {
        return colorPicker.parse(normalizedHex(color.value));
    },
    get disabled() {
        return props.disabled;
    },
    get invalid() {
        return props.invalid;
    },
    format: 'hsba',
    closeOnSelect: true,
    positioning: {
        placement: 'bottom-start',
        strategy: 'fixed',
        gutter: 8,
        overflowPadding: 16,
        flip: true,
        slide: true,
        fitViewport: true,
        hideWhenDetached: true,
    },
    onValueChange(details) {
        color.value = details.value.toString('hex').toLowerCase();
    },
});
const api = computed(() => colorPicker.connect(service, normalizeProps));
const rootElement = ref<HTMLElement | null>(null);
const portalTarget = ref<HTMLElement | string>('body');

onMounted(() => {
    portalTarget.value =
        rootElement.value?.closest<HTMLElement>('[role="dialog"]') ??
        document.body;
});

const displayColor = computed(() => normalizedHex(color.value).toUpperCase());
const triggerLabel = computed(() =>
    t('color_picker.open', { color: displayColor.value }),
);
const dialogLabel = computed(() => t('color_picker.dialog'));
const areaLabel = computed(() => t('color_picker.area'));
const areaRoleDescription = computed(() => t('color_picker.area_role'));
const areaValueText = computed(() =>
    t('color_picker.area_value', {
        saturation: Math.round(
            Number(api.value.getChannelValue('saturation')),
        ),
        brightness: Math.round(
            Number(api.value.getChannelValue('brightness')),
        ),
    }),
);
const hueLabel = computed(() => t('color_picker.hue'));
const hueValueText = computed(() =>
    t('color_picker.hue_value', {
        value: Math.round(Number(api.value.getChannelValue('hue'))),
    }),
);
const hexLabel = computed(() => t('color_picker.hex'));
const presetsLabel = computed(() => t('color_picker.presets'));

function presetLabel(preset: string): string {
    return t('color_picker.preset', { color: preset.toUpperCase() });
}
</script>

<template>
    <div ref="rootElement" v-bind="api.getRootProps()" class="min-w-0">
        <span :id="String(api.getLabelProps().id)" class="sr-only">
            {{ triggerLabel }}
        </span>

        <div v-bind="api.getControlProps()">
            <button
                v-bind="api.getTriggerProps()"
                data-slot="color-picker-trigger"
                :aria-label="triggerLabel"
                :aria-invalid="invalid"
                class="flex min-h-12 w-full min-w-0 cursor-pointer items-center gap-3 rounded-xl border border-input bg-linear-to-br from-background via-orange-50/45 to-orange-100/65 px-3.5 py-2 text-left text-base shadow-xs transition-[color,box-shadow,border-color] outline-none hover:border-orange-300/70 focus-visible:border-orange-500 focus-visible:ring-[3px] focus-visible:ring-orange-500/20 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none pointer-coarse:min-h-13 forced-colors:border-[ButtonBorder]"
            >
                <span
                    v-bind="api.getSwatchProps({
                        value: api.value,
                        respectAlpha: false,
                    })"
                    class="size-7 shrink-0 rounded-lg border border-black/15 shadow-sm forced-colors:border-[ButtonText]"
                    aria-hidden="true"
                />
                <span class="min-w-0 flex-1 font-mono font-semibold">
                    {{ displayColor }}
                </span>
                <span
                    class="size-2.5 shrink-0 rounded-full bg-orange-600 opacity-70"
                    aria-hidden="true"
                />
            </button>
        </div>

        <Teleport :to="portalTarget">
            <div v-bind="api.getPositionerProps()" class="z-70">
                <div
                    v-bind="api.getContentProps()"
                    data-slot="color-picker-content"
                    :aria-label="dialogLabel"
                    class="max-h-[var(--available-height)] w-[min(20rem,calc(100dvw-2rem))] origin-[var(--transform-origin)] space-y-4 overflow-y-auto overscroll-contain rounded-2xl border border-border/80 bg-popover p-4 text-popover-foreground shadow-[0_24px_70px_-30px_rgba(15,23,42,0.55)] data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 motion-reduce:data-[state=open]:animate-none forced-colors:border-[CanvasText]"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold">
                            {{ t('color_picker.custom') }}
                        </p>
                        <span class="font-mono text-sm text-muted-foreground">
                            {{ displayColor }}
                        </span>
                    </div>

                    <div
                        v-bind="api.getAreaProps()"
                        :aria-label="areaLabel"
                        class="h-44 w-full cursor-crosshair rounded-xl border border-black/15 shadow-inner forced-colors:border-[CanvasText]"
                    >
                        <div
                            v-bind="api.getAreaBackgroundProps()"
                            class="h-full w-full rounded-[inherit]"
                        />
                        <div
                            v-bind="api.getAreaThumbProps()"
                            :aria-label="areaLabel"
                            :aria-roledescription="areaRoleDescription"
                            :aria-valuetext="areaValueText"
                            class="size-11 rounded-full border-[3px] border-white shadow-[0_1px_5px_rgba(15,23,42,0.8)] outline-none focus-visible:ring-3 focus-visible:ring-orange-500/45 pointer-coarse:size-12 forced-colors:border-[Highlight] forced-colors:bg-[Canvas]"
                        />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium">
                                {{ hueLabel }}
                            </span>
                            <span class="text-sm text-muted-foreground">
                                {{ hueValueText }}
                            </span>
                        </div>
                        <div
                            v-bind="api.getChannelSliderProps({
                                channel: 'hue',
                            })"
                            class="flex min-h-12 items-center pointer-coarse:min-h-13"
                        >
                            <div
                                v-bind="api.getChannelSliderTrackProps({
                                    channel: 'hue',
                                })"
                                class="h-4 w-full rounded-full border border-black/10 forced-colors:border-[CanvasText]"
                            />
                            <div
                                v-bind="api.getChannelSliderThumbProps({
                                    channel: 'hue',
                                })"
                                :aria-label="hueLabel"
                                :aria-valuetext="hueValueText"
                                class="size-11 -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-white shadow-[0_1px_5px_rgba(15,23,42,0.65)] outline-none focus-visible:ring-3 focus-visible:ring-orange-500/45 pointer-coarse:size-12 forced-colors:border-[Highlight] forced-colors:bg-[Canvas]"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label
                            :for="`${id}-hex-input`"
                            class="text-sm font-medium"
                        >
                            {{ hexLabel }}
                        </label>
                        <input
                            v-bind="api.getChannelInputProps({
                                channel: 'hex',
                            })"
                            :id="`${id}-hex-input`"
                            :aria-label="hexLabel"
                            class="min-h-12 w-full rounded-xl border border-input bg-background px-3.5 py-2 font-mono text-base uppercase shadow-xs outline-none focus-visible:border-orange-500 focus-visible:ring-[3px] focus-visible:ring-orange-500/20 disabled:opacity-50 pointer-coarse:min-h-13 forced-colors:border-[CanvasText]"
                        />
                    </div>

                    <div v-if="presets.length" class="space-y-2">
                        <p class="text-sm font-medium">{{ presetsLabel }}</p>
                        <div
                            v-bind="api.getSwatchGroupProps()"
                            :aria-label="presetsLabel"
                            class="grid grid-cols-4 gap-2"
                        >
                            <button
                                v-for="preset in presets"
                                :key="preset"
                                v-bind="api.getSwatchTriggerProps({
                                    value: preset,
                                })"
                                :aria-label="presetLabel(preset)"
                                :aria-pressed="
                                    api.getSwatchTriggerState({
                                        value: preset,
                                    }).checked
                                "
                                class="flex min-h-12 cursor-pointer items-center justify-center rounded-xl border border-border/80 bg-background outline-none transition-[border-color,box-shadow,transform] hover:-translate-y-0.5 hover:border-orange-500/40 focus-visible:ring-3 focus-visible:ring-orange-500/30 data-[state=checked]:border-orange-600 data-[state=checked]:ring-2 data-[state=checked]:ring-orange-500/25 motion-reduce:transform-none motion-reduce:transition-none pointer-coarse:min-h-13 forced-colors:border-[ButtonBorder]"
                            >
                                <span
                                    v-bind="api.getSwatchProps({
                                        value: preset,
                                        respectAlpha: false,
                                    })"
                                    class="size-7 rounded-lg border border-black/15 shadow-sm forced-colors:border-[ButtonText]"
                                    aria-hidden="true"
                                />
                                <Check
                                    v-if="
                                        api.getSwatchTriggerState({
                                            value: preset,
                                        }).checked
                                    "
                                    class="absolute size-4 text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.9)] forced-colors:text-[Highlight]"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="min-h-12 w-full rounded-xl bg-orange-600 px-4 py-2.5 text-base font-semibold text-white shadow-sm outline-none transition-[background-color,box-shadow] hover:bg-orange-700 focus-visible:ring-3 focus-visible:ring-orange-500/35 focus-visible:ring-offset-2 disabled:opacity-50 motion-reduce:transition-none pointer-coarse:min-h-13 forced-colors:border forced-colors:border-[ButtonText]"
                        @click="api.setOpen(false)"
                    >
                        {{ t('color_picker.done') }}
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>
