import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

const outlinedButtonSurface =
  "border border-orange-200/90 bg-linear-to-br from-background via-orange-50/55 to-orange-100/80 text-foreground shadow-xs hover:border-orange-400/70 hover:from-orange-50 hover:via-orange-100/80 hover:to-orange-200/80 hover:text-orange-900 hover:shadow-sm focus-visible:ring-orange-500/25"

export const buttonVariants = cva(
  "ui-control inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-xl text-sm font-medium motion-reduce:transition-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:cursor-not-allowed aria-disabled:opacity-50 data-loading:cursor-wait [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:ring-[3px] aria-invalid:ring-destructive/20 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-linear-to-br from-orange-600 via-orange-600 to-orange-700 text-white shadow-sm hover:from-orange-700 hover:via-orange-700 hover:to-orange-800 hover:shadow-md focus-visible:ring-orange-500/30",
        destructive:
          "bg-linear-to-br from-destructive via-destructive to-red-700 text-white shadow-sm hover:from-red-700 hover:to-red-800 hover:shadow-md focus-visible:ring-destructive/20",
        outline: outlinedButtonSurface,
        secondary:
          "bg-linear-to-br from-secondary/70 via-secondary/90 to-secondary text-secondary-foreground shadow-xs hover:from-secondary hover:to-neutral-300 hover:shadow-sm focus-visible:ring-orange-500/20",
        ghost: outlinedButtonSurface,
        link: "text-orange-700 underline-offset-4 hover:underline focus-visible:ring-orange-500/25",
      },
      size: {
        "default": "h-10 px-4 py-2 has-[>svg]:px-3",
        "sm": "h-9 rounded-lg gap-1.5 px-3 has-[>svg]:px-2.5",
        "lg": "h-11 px-6 has-[>svg]:px-4",
        "icon": "size-10",
        "icon-sm": "size-9",
        "icon-lg": "size-11",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
