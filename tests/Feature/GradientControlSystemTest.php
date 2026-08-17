<?php

use Illuminate\Support\Facades\File;

test('boxed button variants use visible diagonal gradient surfaces', function () {
    $source = File::get(resource_path('js/components/ui/button/index.ts'));

    expect($source)
        ->toContain('const outlinedButtonSurface =')
        ->toContain('outline: outlinedButtonSurface')
        ->toContain('ghost: outlinedButtonSurface')
        ->toContain('bg-linear-to-br from-orange-600')
        ->toContain('bg-linear-to-br from-destructive')
        ->toContain('bg-linear-to-br from-secondary/70')
        ->toContain('border-orange-200/90')
        ->toContain('hover:border-orange-400/70')
        ->toContain('focus-visible:ring-orange-500/25')
        ->toContain('motion-reduce:transition-none');
});

test('shared form controls use the same subtle diagonal surface ramp', function () {
    foreach ([
        'input' => 'ui/input/Input.vue',
        'textarea' => 'ui/textarea/Textarea.vue',
        'select trigger' => 'ui/select/SelectTrigger.vue',
        'checkbox' => 'ui/checkbox/Checkbox.vue',
        'otp slot' => 'ui/input-otp/InputOTPSlot.vue',
    ] as $name => $path) {
        expect(File::get(resource_path("js/components/{$path}")), $name)
            ->toContain('bg-linear-to-br')
            ->toContain('from-background')
            ->toContain('to-orange-100/65');
    }

    expect(File::get(resource_path('js/components/ui/checkbox/Checkbox.vue')))
        ->toContain('data-[state=checked]:from-orange-600')
        ->toContain('data-[state=checked]:to-orange-700');
});

test('active native text entry exceptions use the shared diagonal surface ramp', function () {
    foreach ([
        'workspace onboarding' => 'onboarding/WorkspaceStep.vue',
        'project onboarding' => 'onboarding/ProjectStep.vue',
        'task onboarding' => 'onboarding/TaskStep.vue',
        'task comments' => 'task/TaskCommentsPanel.vue',
        'two factor manual key' => 'TwoFactorSetupModal.vue',
    ] as $name => $path) {
        expect(File::get(resource_path("js/components/{$path}")), $name)
            ->toContain('bg-linear-to-br')
            ->toContain('from-background')
            ->toContain('to-orange-100/65');
    }
});

test('onboarding skip is an outlined full width phone action', function () {
    $source = File::get(resource_path('js/components/onboarding/OnboardingShell.vue'));

    expect($source)
        ->not->toContain('variant="ghost"')
        ->toContain('grid-cols-1')
        ->toContain('min-[30rem]:grid-cols-2')
        ->toContain('class="min-h-11 w-full min-[30rem]:col-span-2 sm:col-auto sm:w-auto"')
        ->toContain('variant="outline"');
});
