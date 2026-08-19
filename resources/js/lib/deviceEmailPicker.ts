export const DEVICE_EMAIL_PICKER_EVENT = 'sutelio-email-picker-result';

export type DeviceEmailPickerErrorCode =
    'BRIDGE_FAILED' | 'BUSY' | 'INVALID_RESULT' | 'NATIVE_FAILED' | 'TIMED_OUT';

export class DeviceEmailPickerError extends Error {
    public readonly code: DeviceEmailPickerErrorCode;

    constructor(code: DeviceEmailPickerErrorCode) {
        super(code);
        this.name = 'DeviceEmailPickerError';
        this.code = code;
    }
}

export interface DeviceEmailPickerEnvironment {
    eventTarget: EventTarget;
    scheduleTimeout: (callback: () => void) => unknown;
    cancelTimeout: (handle: unknown) => void;
}

type BridgeCall = () => Promise<unknown>;

type PickerResultEvent = Event & {
    detail?: {
        email?: unknown;
        status?: unknown;
    };
};

let requestActive = false;

function defaultEnvironment(): DeviceEmailPickerEnvironment {
    return {
        eventTarget: document,
        scheduleTimeout: (callback) => window.setTimeout(callback, 300_000),
        cancelTimeout: (handle) => window.clearTimeout(handle as number),
    };
}

function normalizeEmail(value: unknown): string | null {
    if (typeof value !== 'string') {
        return null;
    }

    const email = value.trim();

    if (
        email.length === 0 ||
        email.length > 254 ||
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    ) {
        return null;
    }

    return email;
}

export function requestDeviceEmail(
    bridgeCall: BridgeCall,
    environment: DeviceEmailPickerEnvironment = defaultEnvironment(),
): Promise<string | null> {
    if (requestActive) {
        return Promise.reject(new DeviceEmailPickerError('BUSY'));
    }

    requestActive = true;

    return new Promise<string | null>((resolve, reject) => {
        let settled = false;

        const cleanup = (): void => {
            environment.eventTarget.removeEventListener(
                DEVICE_EMAIL_PICKER_EVENT,
                handleResult as EventListener,
            );
            environment.cancelTimeout(timeoutHandle);
            requestActive = false;
        };

        const settle = (
            callback: (value: string | null) => void,
            value: string | null,
        ): void => {
            if (settled) {
                return;
            }

            settled = true;
            cleanup();
            callback(value);
        };

        const fail = (code: DeviceEmailPickerErrorCode): void => {
            if (settled) {
                return;
            }

            settled = true;
            cleanup();
            reject(new DeviceEmailPickerError(code));
        };

        const handleResult = (event: Event): void => {
            const detail = (event as PickerResultEvent).detail;

            if (detail?.status === 'cancelled') {
                settle(resolve, null);

                return;
            }

            if (detail?.status !== 'selected') {
                fail('NATIVE_FAILED');

                return;
            }

            const email = normalizeEmail(detail.email);

            if (email === null) {
                fail('INVALID_RESULT');

                return;
            }

            settle(resolve, email);
        };

        environment.eventTarget.addEventListener(
            DEVICE_EMAIL_PICKER_EVENT,
            handleResult as EventListener,
        );
        const timeoutHandle = environment.scheduleTimeout(() =>
            fail('TIMED_OUT'),
        );

        void Promise.resolve()
            .then(bridgeCall)
            .then((result) => {
                if (
                    typeof result !== 'object' ||
                    result === null ||
                    !('status' in result) ||
                    result.status !== 'launched'
                ) {
                    fail('BRIDGE_FAILED');
                }
            })
            .catch(() => fail('BRIDGE_FAILED'));
    });
}
