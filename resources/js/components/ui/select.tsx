"use client"

import * as React from "react"
import * as SelectPrimitive from "@radix-ui/react-select"
import {
  Check,
  ChevronDown,
  ChevronUp,
  Search,
} from "lucide-react"

import { Input } from "./input"
import { cn } from "@/lib/utils"

/* -------------------------------------------------------------------------- */
/*                                    Root                                    */
/* -------------------------------------------------------------------------- */

const Select = SelectPrimitive.Root
const SelectGroup = SelectPrimitive.Group
const SelectValue = SelectPrimitive.Value

/* -------------------------------------------------------------------------- */
/*                                  Trigger                                   */
/* -------------------------------------------------------------------------- */

const SelectTrigger = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.Trigger>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Trigger>
>(({ className, children, ...props }, ref) => (
  <SelectPrimitive.Trigger
    ref={ref}
    className={cn(
      "flex h-10 w-full min-w-0 items-center justify-between gap-2 rounded-md border border-input bg-background px-3 py-2 text-left text-sm ring-offset-background",
      "data-[placeholder]:text-muted-foreground",
      "focus:border-primary focus:outline-none",
      "disabled:cursor-not-allowed disabled:opacity-50",
      "[&>span]:min-w-0 [&>span]:flex-1 [&>span]:truncate [&>span]:text-left",
      className
    )}
    {...props}
  >
    {children}

    <SelectPrimitive.Icon asChild>
      <ChevronDown className="h-4 w-4 shrink-0 opacity-50" />
    </SelectPrimitive.Icon>
  </SelectPrimitive.Trigger>
))

SelectTrigger.displayName = SelectPrimitive.Trigger.displayName

/* -------------------------------------------------------------------------- */
/*                              Scroll Buttons                                */
/* -------------------------------------------------------------------------- */

const SelectScrollUpButton = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.ScrollUpButton>,
  React.ComponentPropsWithoutRef<
    typeof SelectPrimitive.ScrollUpButton
  >
>(({ className, ...props }, ref) => (
  <SelectPrimitive.ScrollUpButton
    ref={ref}
    className={cn(
      "flex cursor-default items-center justify-center py-1",
      className
    )}
    {...props}
  >
    <ChevronUp className="h-4 w-4" />
  </SelectPrimitive.ScrollUpButton>
))

SelectScrollUpButton.displayName =
  SelectPrimitive.ScrollUpButton.displayName

const SelectScrollDownButton = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.ScrollDownButton>,
  React.ComponentPropsWithoutRef<
    typeof SelectPrimitive.ScrollDownButton
  >
>(({ className, ...props }, ref) => (
  <SelectPrimitive.ScrollDownButton
    ref={ref}
    className={cn(
      "flex cursor-default items-center justify-center py-1",
      className
    )}
    {...props}
  >
    <ChevronDown className="h-4 w-4" />
  </SelectPrimitive.ScrollDownButton>
))

SelectScrollDownButton.displayName =
  SelectPrimitive.ScrollDownButton.displayName

/* -------------------------------------------------------------------------- */
/*                             Search Text Helper                             */
/* -------------------------------------------------------------------------- */

/**
 * Recursively extracts text from React children.
 *
 * Supports:
 *
 * <SelectItem value="105">
 *   Mesbah Uddin
 * </SelectItem>
 *
 * and:
 *
 * <SelectItem value="105">
 *   <div>
 *     <span>Mesbah Uddin</span>
 *     <span>Extension 105</span>
 *   </div>
 * </SelectItem>
 */
const getSearchableText = (
  node: React.ReactNode
): string => {
  if (
    node === null ||
    node === undefined ||
    typeof node === "boolean"
  ) {
    return ""
  }

  if (
    typeof node === "string" ||
    typeof node === "number"
  ) {
    return String(node)
  }

  if (Array.isArray(node)) {
    return node
      .map(getSearchableText)
      .join(" ")
  }

  if (React.isValidElement(node)) {
    const props = node.props as {
      children?: React.ReactNode
    }

    return getSearchableText(
      props.children
    )
  }

  return ""
}

/* -------------------------------------------------------------------------- */
/*                                  Content                                   */
/* -------------------------------------------------------------------------- */

type SelectContentProps =
  React.ComponentPropsWithoutRef<
    typeof SelectPrimitive.Content
  > & {
    searchable?: boolean
    maxResults?: number
    searchPlaceholder?: string
    emptyText?: string
  }

const SelectContent = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.Content>,
  SelectContentProps
>(
  (
    {
      className,
      children,
      position = "popper",
      searchable = false,
      maxResults = 50,
      searchPlaceholder = "Search...",
      emptyText = "No results found",
      ...props
    },
    forwardedRef
  ) => {
    const [search, setSearch] =
      React.useState("")

    const inputRef =
      React.useRef<HTMLInputElement>(null)

    const contentRef =
      React.useRef<HTMLDivElement | null>(null)

    /* ---------------------------------------------------------------------- */
    /*                             Merge refs                                 */
    /* ---------------------------------------------------------------------- */

    const setContentRef =
      React.useCallback(
        (
          node: HTMLDivElement | null
        ) => {
          contentRef.current = node

          if (
            typeof forwardedRef ===
            "function"
          ) {
            forwardedRef(node)
          } else if (forwardedRef) {
            (
              forwardedRef as React.MutableRefObject<HTMLDivElement | null>
            ).current = node
          }
        },
        [forwardedRef]
      )

    /* ---------------------------------------------------------------------- */
    /*                       Convert children to array                        */
    /* ---------------------------------------------------------------------- */

    const childrenArray =
      React.useMemo(
        () =>
          React.Children.toArray(
            children
          ),
        [children]
      )

    /* ---------------------------------------------------------------------- */
    /*                              Filtering                                */
    /* ---------------------------------------------------------------------- */

    const filteredChildren =
      React.useMemo(() => {
        if (!searchable) {
          return childrenArray
        }

        const query = search
          .trim()
          .toLowerCase()

        /**
         * IMPORTANT:
         *
         * Do NOT slice the normal item list here.
         *
         * Radix needs the selected item to remain
         * registered in its collection so that
         * SelectValue can display its ItemText.
         *
         * This fixes cases where values such as
         * 202 were selected internally but the
         * trigger became blank.
         */
        if (!query) {
          return childrenArray
        }

        return childrenArray
          .filter((child) => {
            if (
              !React.isValidElement(
                child
              )
            ) {
              return true
            }

            const childProps =
              child.props as {
                children?: React.ReactNode
              }

            const text =
              getSearchableText(
                childProps.children
              )

            return text
              .toLowerCase()
              .includes(query)
          })
          .slice(0, maxResults)
      }, [
        childrenArray,
        search,
        searchable,
        maxResults,
      ])

    /* ---------------------------------------------------------------------- */
    /*                           First result                                 */
    /* ---------------------------------------------------------------------- */

    const getFirstEnabledItem =
      React.useCallback(() => {
        return contentRef.current?.querySelector<HTMLElement>(
          '[role="option"]:not([data-disabled])'
        )
      }, [])

    /* ---------------------------------------------------------------------- */
    /*                     Keep search input focused                         */
    /* ---------------------------------------------------------------------- */

    React.useEffect(() => {
      if (!searchable) {
        return
      }

      /**
       * Filtering causes Radix's item collection
       * to update. In some cases Radix may attempt
       * to move focus from the search field.
       *
       * Restore focus after the filtered results
       * have rendered.
       */
      const frame =
        requestAnimationFrame(() => {
          const content =
            contentRef.current

          if (
            content?.dataset.state !==
            "open"
          ) {
            return
          }

          if (
            document.activeElement !==
            inputRef.current
          ) {
            inputRef.current?.focus()
          }
        })

      return () =>
        cancelAnimationFrame(frame)
    }, [search, searchable])

    /* ---------------------------------------------------------------------- */
    /*                          Search keyboard                               */
    /* ---------------------------------------------------------------------- */

    const handleSearchKeyDown = (
      event: React.KeyboardEvent<HTMLInputElement>
    ) => {
      /**
       * Radix Select has its own typeahead system.
       * Don't allow search-input keystrokes to
       * reach Radix.
       */
      event.stopPropagation()

      if (event.key === "Enter") {
        event.preventDefault()

        getFirstEnabledItem()?.click()

        return
      }

      if (
        event.key === "ArrowDown"
      ) {
        event.preventDefault()

        getFirstEnabledItem()?.focus()

        return
      }
    }

    /* ---------------------------------------------------------------------- */
    /*                                Render                                  */
    /* ---------------------------------------------------------------------- */

    return (
      <SelectPrimitive.Portal>
        <SelectPrimitive.Content
          ref={setContentRef}
          position={position}
          className={cn(
            "relative z-50 overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md",

            "max-h-[var(--radix-select-content-available-height)]",

            "min-w-[var(--radix-select-trigger-width)]",

            "w-max max-w-[min(600px,calc(100vw-2rem))]",

            "data-[state=open]:animate-in",
            "data-[state=closed]:animate-out",

            "data-[state=closed]:fade-out-0",
            "data-[state=open]:fade-in-0",

            "data-[state=closed]:zoom-out-95",
            "data-[state=open]:zoom-in-95",

            "data-[side=bottom]:slide-in-from-top-2",
            "data-[side=left]:slide-in-from-right-2",
            "data-[side=right]:slide-in-from-left-2",
            "data-[side=top]:slide-in-from-bottom-2",

            "origin-[--radix-select-content-transform-origin]",

            position === "popper" && [
              "data-[side=bottom]:translate-y-1",
              "data-[side=left]:-translate-x-1",
              "data-[side=right]:translate-x-1",
              "data-[side=top]:-translate-y-1",
            ],

            className
          )}

          /* -------------------------------------------------------------- */
          /* Clear old search when select closes                             */
          /* -------------------------------------------------------------- */

          onCloseAutoFocus={() => {
            setSearch("")
          }}

          /* -------------------------------------------------------------- */
          /* Prevent Radix typeahead from stealing search keys                */
          /* -------------------------------------------------------------- */

          onKeyDownCapture={(
            event
          ) => {
            if (
              searchable &&
              event.target instanceof
              HTMLInputElement
            ) {
              event.stopPropagation()
            }
          }}

          {...props}
        >
          <SelectScrollUpButton />

          {/* -------------------------------------------------------------- */}
          {/* Search                                                         */}
          {/* -------------------------------------------------------------- */}

          {searchable && (
            <div
              className={cn(
                "sticky top-0 z-10",
                "flex items-center",
                "border-b bg-popover",
                "px-3 py-2"
              )}
              /**
               * Radix reacts to pointer-down
               * events inside Select.Content.
               *
               * Stop search interactions from
               * being interpreted as option
               * interactions.
               */
              onPointerDown={(
                event
              ) => {
                event.stopPropagation()
              }}
            >
              <Search className="mr-2 h-4 w-4 shrink-0 opacity-50" />

              <Input
                ref={inputRef}
                type="text"
                autoFocus
                value={search}
                placeholder={
                  searchPlaceholder
                }
                autoComplete="off"
                autoCorrect="off"
                spellCheck={false}
                onChange={(
                  event
                ) => {
                  setSearch(
                    event.target.value
                  )
                }}
                onKeyDown={
                  handleSearchKeyDown
                }
                className={cn(
                  "h-8 min-w-0 flex-1",
                  "border-0 bg-transparent",
                  "p-0 shadow-none",
                  "focus-visible:ring-0",
                  "focus-visible:ring-offset-0"
                )}
              />
            </div>
          )}

          {/* -------------------------------------------------------------- */}
          {/* Options                                                        */}
          {/* -------------------------------------------------------------- */}

          <SelectPrimitive.Viewport
            className={cn(
              "w-full overflow-y-auto p-1",
              "max-h-[300px]"
            )}
          >
            {filteredChildren}

            {searchable &&
              search.trim() !== "" &&
              filteredChildren.length ===
              0 && (
                <div className="px-3 py-6 text-center text-sm text-muted-foreground">
                  {emptyText}
                </div>
              )}
          </SelectPrimitive.Viewport>

          <SelectScrollDownButton />
        </SelectPrimitive.Content>
      </SelectPrimitive.Portal>
    )
  }
)

SelectContent.displayName =
  SelectPrimitive.Content.displayName

/* -------------------------------------------------------------------------- */
/*                                   Label                                    */
/* -------------------------------------------------------------------------- */

const SelectLabel = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.Label>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Label>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.Label
    ref={ref}
    className={cn(
      "py-1.5 pl-8 pr-2 text-left text-sm font-semibold",
      className
    )}
    {...props}
  />
))

SelectLabel.displayName =
  SelectPrimitive.Label.displayName

/* -------------------------------------------------------------------------- */
/*                                    Item                                    */
/* -------------------------------------------------------------------------- */

const SelectItem = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.Item>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Item>
>(({ className, children, value, ...props }, ref) => (
  <SelectPrimitive.Item
    ref={ref}
    value={String(value)}
    className={cn(
      "relative flex w-full min-w-0 cursor-default select-none items-center rounded-sm",
      "py-1.5 pl-8 pr-2",
      "text-left text-sm outline-none",
      "focus:bg-accent focus:text-accent-foreground",
      "data-[disabled]:pointer-events-none",
      "data-[disabled]:opacity-50",
      className
    )}
    {...props}
  >
    <span className="absolute left-2 flex h-3.5 w-3.5 shrink-0 items-center justify-center">
      <SelectPrimitive.ItemIndicator>
        <Check className="h-4 w-4" />
      </SelectPrimitive.ItemIndicator>
    </span>

    <SelectPrimitive.ItemText>
      {children}
    </SelectPrimitive.ItemText>
  </SelectPrimitive.Item>
))

SelectItem.displayName = SelectPrimitive.Item.displayName

/* -------------------------------------------------------------------------- */
/*                                 Separator                                  */
/* -------------------------------------------------------------------------- */

const SelectSeparator = React.forwardRef<
  React.ElementRef<typeof SelectPrimitive.Separator>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Separator>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.Separator
    ref={ref}
    className={cn(
      "-mx-1 my-1 h-px bg-muted",
      className
    )}
    {...props}
  />
))

SelectSeparator.displayName =
  SelectPrimitive.Separator.displayName

/* -------------------------------------------------------------------------- */
/*                                   Export                                   */
/* -------------------------------------------------------------------------- */

export {
  Select,
  SelectGroup,
  SelectValue,
  SelectTrigger,
  SelectContent,
  SelectLabel,
  SelectItem,
  SelectSeparator,
  SelectScrollUpButton,
  SelectScrollDownButton,
}