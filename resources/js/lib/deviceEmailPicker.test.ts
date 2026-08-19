import assert from 'node:assert/strict';
import test from 'node:test';

interface DeviceEmailPickerModule {
    requestDeviceEmail: (
        bridgeCall: () => Promise<unknown>,
        environment: PickerEnvironment,
    ) => Promise<string | null>;
}

interface PickerEnvironment {
    eventTarget: EventTarget;
    scheduleTimeout: (callback: () => void) => unknown;
    cancelTimeout: (handle: unknown) => void;
}

const pickerModule = (await import('./deviceEmailPicker.ts').catch(
    () => null,
)) as DeviceEmailPickerModule | null;

function requirePickerModule(): DeviceEmailPickerModule {
    assert.notEqual(
        pickerModule,
        null,
        'the device email picker adapter must exist',
    );

    return pickerModule as DeviceEmailPickerModule;
}

function environment(target: EventTarget): PickerEnvironment {
    return {
        eventTarget: target,
        scheduleTimeout: () => 1,
        cancelTimeout: () => undefined,
    };
}

function emitResult(
    target: EventTarget,
    detail: Record<string, unknown>,
): void {
    const event = new Event('sutelio-email-picker-result') as Event & {
        detail: Record<string, unknown>;
    };
    event.detail = detail;
    target.dispatchEvent(event);
}

test('device chooser resolves only the selected email', async () => {
    const { requestDeviceEmail } = requirePickerModule();
    const target = new EventTarget();
    const pending = requestDeviceEmail(
        async () => ({ status: 'launched' }),
        environment(target),
    );

    emitResult(target, {
        status: 'selected',
        email: ' selected@example.com ',
    });

    assert.equal(await pending, 'selected@example.com');
});

test('device chooser cancellation is a normal null result', async () => {
    const { requestDeviceEmail } = requirePickerModule();
    const target = new EventTarget();
    const pending = requestDeviceEmail(
        async () => ({ status: 'launched' }),
        environment(target),
    );

    emitResult(target, { status: 'cancelled' });

    assert.equal(await pending, null);
});

test('device chooser rejects malformed native email payloads', async () => {
    const { requestDeviceEmail } = requirePickerModule();
    const target = new EventTarget();
    const pending = requestDeviceEmail(
        async () => ({ status: 'launched' }),
        environment(target),
    );

    emitResult(target, { status: 'selected', email: 'not-an-email' });

    await assert.rejects(pending, { code: 'INVALID_RESULT' });
});

test('bridge failures clean up and allow a later request', async () => {
    const { requestDeviceEmail } = requirePickerModule();
    const target = new EventTarget();

    await assert.rejects(
        requestDeviceEmail(async () => {
            throw new Error('bridge unavailable');
        }, environment(target)),
        { code: 'BRIDGE_FAILED' },
    );

    const retry = requestDeviceEmail(
        async () => ({ status: 'launched' }),
        environment(target),
    );
    emitResult(target, { status: 'cancelled' });

    assert.equal(await retry, null);
});

test('device chooser times out cleanly and prevents concurrent launches', async () => {
    const { requestDeviceEmail } = requirePickerModule();
    const target = new EventTarget();
    const timeoutHolder: { callback?: () => void } = {};
    const pickerEnvironment: PickerEnvironment = {
        eventTarget: target,
        scheduleTimeout: (callback) => {
            timeoutHolder.callback = callback;

            return 7;
        },
        cancelTimeout: () => undefined,
    };
    const pending = requestDeviceEmail(
        async () => ({ status: 'launched' }),
        pickerEnvironment,
    );

    await assert.rejects(
        requestDeviceEmail(
            async () => ({ status: 'launched' }),
            pickerEnvironment,
        ),
        { code: 'BUSY' },
    );

    assert.ok(timeoutHolder.callback);
    timeoutHolder.callback();

    await assert.rejects(pending, { code: 'TIMED_OUT' });
});
