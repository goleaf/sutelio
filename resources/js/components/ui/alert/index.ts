import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Alert } from "./Alert.vue"
export { default as AlertDescription } from "./AlertDescription.vue"
export { default as AlertTitle } from "./AlertTitle.vue"

export const alertVariants = cva(
  "relative grid w-full grid-cols-[0_1fr] items-start gap-y-0.5 rounded-xl border border-border/80 px-4 py-3.5 text-sm shadow-[0_14px_36px_-32px_rgba(15,23,42,0.5)] has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] has-[>svg]:gap-x-3 [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current",
  {
    variants: {
      variant: {
        default: "bg-card text-card-foreground",
        destructive:
          "border-status-destructive-border bg-status-destructive-surface text-status-destructive-text [&>svg]:text-status-destructive-icon *:data-[slot=alert-description]:text-status-destructive-text/90",
        success:
          "border-status-success-border bg-status-success-surface text-status-success-text [&>svg]:text-status-success-icon *:data-[slot=alert-description]:text-status-success-text/90",
        warning:
          "border-status-warning-border bg-status-warning-surface text-status-warning-text [&>svg]:text-status-warning-icon *:data-[slot=alert-description]:text-status-warning-text/85",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)

export type AlertVariants = VariantProps<typeof alertVariants>
