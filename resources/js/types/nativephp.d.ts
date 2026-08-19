declare module '#nativephp' {
    export function BridgeCall(
        method: string,
        params?: Record<string, unknown>,
    ): Promise<unknown>;
}
